import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:flutter_blue_plus/flutter_blue_plus.dart';
import 'package:permission_handler/permission_handler.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({Key? key}) : super(key: key);

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  List<BluetoothDevice> devicesList = [];
  BluetoothDevice? selectedDevice;
  BluetoothCharacteristic? writeCharacteristic;
  bool isConnected = false;

  List<String> items = [];
  String? selectedItem;

  List<String> locations = [];
  String? selectedLocation;

  List<Map<String, dynamic>> huList = [];
  int currentIndex = 0;

  final huInputController = TextEditingController();
  final huInputFocusNode = FocusNode();

  bool started = false;

  @override
  void initState() {
    super.initState();
    _fullRefresh();
  }

  Future<void> _fullRefresh() async {
    resetSession(clearSelectionOnly: true);
    await fetchAvailableItems();
    await _initBluetooth();
  }

  Future<void> fetchAvailableItems() async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('token');
    final response = await http.get(
      Uri.parse('http://hidramani.swatch.sticker.efl3plofc.com/api/items'),
      headers: {'Authorization': 'Bearer $token'},
    );
    if (response.statusCode == 200) {
      final data = (json.decode(response.body) as List).cast<String>();
      setState(() => items = data);
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Error fetching items')),
      );
    }
  }

  Future<void> fetchLocationsByItem(String item) async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('token');
    final response = await http.post(
      Uri.parse(
          'http://hidramani.swatch.sticker.efl3plofc.com/api/locations-by-item'),
      headers: {
        'Authorization': 'Bearer $token',
        'Content-Type': 'application/json',
      },
      body: json.encode({'new_item_number': item}),
    );

    if (response.statusCode == 200) {
      final data = (json.decode(response.body) as List);

      if (data.isEmpty) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
              content: Text('No pending locations to print for this item')),
        );
      }

      final distinctLocations =
          data.map((e) => e['location_id'] as String).toSet().toList()..sort();

      setState(() {
        locations = distinctLocations;
        selectedLocation = null;
        huList.clear();
        currentIndex = 0;
        started = false;
      });
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error fetching locations')),
      );
    }
  }

  Future<void> fetchHuListByLocation(String item, String location) async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('token');
    final response = await http.post(
      Uri.parse('http://hidramani.swatch.sticker.efl3plofc.com/api/hu-ids'),
      headers: {
        'Authorization': 'Bearer $token',
        'Content-Type': 'application/json',
      },
      body: json.encode({'new_item_number': item, 'location_id': location}),
    );

    if (response.statusCode == 200) {
      final data = (json.decode(response.body) as List);
      setState(() {
        huList = data
            .map<Map<String, dynamic>>((e) => e as Map<String, dynamic>)
            .toList();
        currentIndex = 0;
        started = true;
      });
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error fetching HU list')),
      );
    }
  }

  Future<void> _initBluetooth() async {
    await _requestPermissions();
    bool isAvailable = await FlutterBluePlus.isAvailable;
    bool isOn = await FlutterBluePlus.isOn;
    if (!isAvailable || !isOn) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text("Bluetooth is off or not available")),
      );
      return;
    }
    _startScan();
  }

  Future<void> _requestPermissions() async {
    await [
      Permission.bluetooth,
      Permission.bluetoothScan,
      Permission.bluetoothConnect,
      Permission.locationWhenInUse,
    ].request();
  }

  void _startScan() {
    devicesList.clear();
    FlutterBluePlus.startScan(timeout: const Duration(seconds: 4));
    FlutterBluePlus.scanResults.listen((results) {
      for (var r in results) {
        if (!devicesList.contains(r.device) &&
            (r.device.name.toLowerCase().contains("zebra") ||
                r.device.name.toLowerCase().contains("zd") ||
                r.device.name.isNotEmpty)) {
          setState(() => devicesList.add(r.device));
        }
      }
    });
  }

  Future<void> _connectToDevice(BluetoothDevice device) async {
    try {
      await device.connect(autoConnect: false);
      FlutterBluePlus.stopScan();
      var services = await device.discoverServices();
      for (var svc in services) {
        for (var ch in svc.characteristics) {
          if (ch.properties.write) {
            setState(() {
              selectedDevice = device;
              isConnected = true;
              devicesList = [device];
              writeCharacteristic = ch;
            });
            break;
          }
        }
      }
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text("Connected to ${device.name}")),
      );
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text("Connection failed: $e")),
      );
    }
  }

  Future<void> disconnectPrinter() async {
    if (selectedDevice != null) {
      await selectedDevice!.disconnect();
      setState(() {
        selectedDevice = null;
        isConnected = false;
        writeCharacteristic = null;
        devicesList.clear();
      });
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text("Disconnected printer")),
      );
    }
  }

  Future<void> printLabel(String inputHuId) async {
    if (currentIndex >= huList.length) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text("All labels printed.")),
      );
      return;
    }

    final expectedHu = huList[currentIndex]['pallet'];
    if (inputHuId.trim() != expectedHu) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text("HU ID does not match expected.")),
      );
      return;
    }

    if (!isConnected || writeCharacteristic == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text("No printer connected")),
      );
      return;
    }

    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('token');
    final response = await http.post(
      Uri.parse(
          'http://hidramani.swatch.sticker.efl3plofc.com/api/pallet/print'),
      headers: {
        'Authorization': 'Bearer $token',
        'Content-Type': 'application/json',
      },
      body: json.encode({'pallet': inputHuId}),
    );

    if (response.statusCode != 200) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
            content:
                Text("Error printing: ${json.decode(response.body)['error']}")),
      );
      return;
    }

    final data = json.decode(response.body);

    final zpl = '''
^XA
^MMC
^PW609
^LL406
^LH0,0
^FO350,70^A0N,27,27^FD[${data['printed_time']}]^FS
^FO40,70^A0N,27,27^FDRoll #:     ${data['supplier_hu']}^FS
^FO40,100^A0N,27,27^FDLOT #:     ${data['lot_number']}^FS
^FO40,130^A0N,27,27^FDColor:      ${data['color']}^FS
^FO40,160^A0N,27,27^FDINV #:      ${data['invoice_number']}^FS
^FO40,190^A0N,27,27^FDSO No:     ${data['client_so']}^FS
^FO40,220^A0N,27,27^FDPlant:      ${data['plant']}^FS
^FO40,250^A0N,27,27^FDREF ID:    ${data['new_item_number']}^FS
^FO40,280^A0N,27,27^FDPallet:      ${data['pallet']}^FS
^FO40,310^A0N,27,27^FDQty:         ${data['actual_qty']} | YDS^FS
^FO40,340^A0N,27,27^FD${data['customer_po_number']}^FS
^FO420,250^BQN,6,6^FDLA,${data['pallet']}^FS
^PQ1
^XZ
''';

    final bytes = utf8.encode(zpl);
    const chunkSize = 180;
    for (var i = 0; i < bytes.length; i += chunkSize) {
      final chunk = bytes.sublist(
          i, i + chunkSize > bytes.length ? bytes.length : i + chunkSize);
      await writeCharacteristic!.write(chunk, withoutResponse: false);
      await Future.delayed(const Duration(milliseconds: 150));
    }

    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(content: Text("Label printed")),
    );

    huInputController.clear();
    huInputFocusNode.requestFocus();

    setState(() {
      currentIndex++;
    });
  }

  void resetSession({bool clearSelectionOnly = false}) {
    setState(() {
      locations.clear();
      huList.clear();
      currentIndex = 0;
      started = false;
      huInputController.clear();
      selectedLocation = null;
      if (!clearSelectionOnly) selectedItem = null;
    });
  }

  @override
  void dispose() {
    huInputController.dispose();
    huInputFocusNode.dispose();
    selectedDevice?.disconnect();
    super.dispose();
  }

  Widget _buildDetailBox(String label, String value) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 18),
      margin: const EdgeInsets.only(bottom: 10),
      decoration: BoxDecoration(
        border: Border.all(color: const Color.fromARGB(255, 228, 147, 55)),
        borderRadius: BorderRadius.circular(8),
        color: const Color.fromARGB(255, 228, 147, 55),
      ),
      child: Row(
        children: [
          Text("$label: ",
              style: const TextStyle(
                  fontWeight: FontWeight.bold,
                  color: Colors.white,
                  fontSize: 16)),
          Expanded(
              child: Text(value, style: const TextStyle(color: Colors.white))),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final showHUInput = started && currentIndex < huList.length;

    return Scaffold(
      appBar: AppBar(
        title: const Text("Hidramani Sticker Printer"),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            tooltip: 'Refresh all data',
            onPressed: _fullRefresh,
          ),
          IconButton(
            icon: const Icon(Icons.print),
            tooltip: 'Scan Bluetooth Printers',
            onPressed: _startScan,
          ),
          if (isConnected)
            IconButton(
              icon: const Icon(Icons.cancel),
              tooltip: 'Disconnect Printer',
              onPressed: disconnectPrinter,
            ),
        ],
      ),
      body: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            if (selectedDevice != null && isConnected) ...[
              const Text("Connected Printer:",
                  style: TextStyle(fontWeight: FontWeight.bold)),
              const SizedBox(height: 8),
              ListTile(
                title: Text(selectedDevice!.name),
                subtitle: Text(selectedDevice!.id.id),
                trailing: const Icon(Icons.check_circle, color: Colors.green),
              ),
              const Divider(),
            ] else if (devicesList.isNotEmpty) ...[
              const Text("Available Devices:",
                  style: TextStyle(fontWeight: FontWeight.bold)),
              const SizedBox(height: 8),
              SizedBox(
                height: 100,
                child: ListView.builder(
                  itemCount: devicesList.length,
                  itemBuilder: (context, index) {
                    final d = devicesList[index];
                    return ListTile(
                      title: Text(d.name),
                      subtitle: Text(d.id.id),
                      onTap: () => _connectToDevice(d),
                    );
                  },
                ),
              ),
              const Divider(),
            ],
            if (!started) ...[
              DropdownButtonFormField<String>(
                isExpanded: true,
                decoration: const InputDecoration(
                  labelText: "Select New Item Number",
                  border: OutlineInputBorder(),
                ),
                items: items.map((it) {
                  return DropdownMenuItem(
                    value: it,
                    child: Text(it),
                  );
                }).toList(),
                value: selectedItem,
                onChanged: (v) async {
                  setState(() {
                    selectedItem = v;
                    selectedLocation = null;
                    locations.clear();
                    huList.clear();
                    started = false;
                    currentIndex = 0;
                  });
                  if (v != null) await fetchLocationsByItem(v);
                },
                hint: const Text("Choose or search item..."),
              ),
              const SizedBox(height: 10),
              if (locations.isNotEmpty)
                DropdownButtonFormField<String>(
                  isExpanded: true,
                  decoration: const InputDecoration(
                    labelText: "Select Location",
                    border: OutlineInputBorder(),
                  ),
                  items: locations.map((loc) {
                    return DropdownMenuItem(
                      value: loc,
                      child: Text(loc),
                    );
                  }).toList(),
                  value: selectedLocation,
                  onChanged: (v) async {
                    setState(() {
                      selectedLocation = v;
                      huList.clear();
                      started = false;
                      currentIndex = 0;
                    });
                    if (v != null && selectedItem != null) {
                      await fetchHuListByLocation(selectedItem!, v);
                    }
                  },
                  hint: const Text("Choose location..."),
                ),
              const SizedBox(height: 20),
              ElevatedButton(
                onPressed: (selectedItem != null &&
                        selectedLocation != null &&
                        huList.isNotEmpty)
                    ? () {
                        setState(() {
                          started = true;
                          currentIndex = 0;
                        });
                        huInputFocusNode.requestFocus();
                      }
                    : null,
                child: const Text("Start Printing"),
              ),
            ],
            if (showHUInput) ...[
              const SizedBox(height: 20),
              _buildDetailBox("Location", selectedLocation ?? ""),
              _buildDetailBox(
                  "Expected HU", huList[currentIndex]['pallet'] ?? ""),
              TextField(
                controller: huInputController,
                focusNode: huInputFocusNode,
                decoration: const InputDecoration(
                  labelText: "Scan or Enter HU ID",
                  border: OutlineInputBorder(),
                ),
                onSubmitted: (_) => printLabel(huInputController.text.trim()),
              ),
              const SizedBox(height: 10),
              Row(
                children: [
                  Expanded(
                    child: ElevatedButton(
                      style: ElevatedButton.styleFrom(
                          backgroundColor: Color.fromARGB(255, 228, 147, 55)),
                      onPressed: () =>
                          printLabel(huInputController.text.trim()),
                      child: const Text("Print Label",
                          style: TextStyle(color: Colors.white)),
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: ElevatedButton(
                      style: ElevatedButton.styleFrom(
                          backgroundColor: Color.fromARGB(255, 228, 147, 55)),
                      onPressed: () {
                        setState(() {
                          currentIndex++;
                          huInputController.clear();
                          huInputFocusNode.requestFocus();
                        });
                      },
                      child: const Text("Skip",
                          style: TextStyle(color: Colors.white)),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 10),
              ElevatedButton(
                style: ElevatedButton.styleFrom(
                    backgroundColor: Color.fromARGB(255, 228, 98, 55)),
                onPressed: () {
                  resetSession();
                  _fullRefresh();
                },
                child:
                    const Text("Stop", style: TextStyle(color: Colors.white)),
              ),
            ],
            if (started && currentIndex >= huList.length) ...[
              const SizedBox(height: 20),
              const Text("All labels printed for selected location."),
              ElevatedButton(
                onPressed: () {
                  resetSession();
                  _fullRefresh();
                },
                child: const Text("Restart / Refresh"),
              )
            ],
          ],
        ),
      ),
    );
  }
}

<?php



namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\ExcelUpload;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;






class ExcelUploadController extends Controller
{
    public function showUploadForm()
    {
        return view('ExcelUpload', ['uploadedData' => []]);
    }

   public function import(Request $request)
{
    $request->validate([
        'file' => 'required|mimes:xlsx,xls,csv'
    ]);

    $action = $request->input('action');

    try {
        $file = $request->file('file')->getRealPath();
        $spreadsheet = IOFactory::load($file);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        if (empty($rows) || !is_array($rows[0])) {
            return redirect()->back()->withErrors(['error' => 'The uploaded file is empty or invalid.']);
        }

        $expectedHeaders = [
            'wh id' => 'wh_id',
            'client code' => 'client_code',
            'pallet' => 'pallet',
            'invoice number' => 'invoice_number',
            'location id' => 'location_id',
            'item number' => 'item_number',
            'description' => 'description',
            'lot number' => 'lot_number',
            'actual qty' => 'actual_qty',
            'unavailable qty' => 'unavailable_qty',
            'uom' => 'uom',
            'status' => 'status',
            'mlp' => 'mlp',
            'stored attribute id' => 'stored_attribute_id',
            'fifo date' => 'fifo_date',
            'expiration date' => 'expiration_date',
            'grn number' => 'grn_number',
            'gate pass id' => 'gatepass_id',
            'cust dec no' => 'cust_dec_number',
            'color' => 'color',
            'size' => 'size',
            'style' => 'style',
            'supplier' => 'supplier',
            'plant' => 'plant',
            'client so' => 'client_so',
            'client so line' => 'client_so_line',
            'po cust dec' => 'po_cust_dec',
            'customer ref number' => 'customer_ref_number',
            'item id' => 'item_id',
            'invoice number1' => 'invoice_number_1',
            'transaction' => 'transaction',
            'order type' => 'order_type',
            'order number' => 'order_number',
            'store order number' => 'store_order_number',
            'customer po number' => 'customer_po_number',
            'partial order flag' => 'partial_order_flag',
            'order date' => 'order_date',
            'load id' => 'load_id',
            'asn number' => 'asn_number',
            'po number' => 'po_number',
            'supplier hu' => 'supplier_hu',
            'new item number' => 'new_item_number',
        ];

        $actualHeaders = array_map(fn($h) => strtolower(trim($h)), $rows[0]);
        $missingHeaders = array_diff(array_keys($expectedHeaders), $actualHeaders);

        if (!empty($missingHeaders)) {
            return redirect()->back()->withErrors([
                'error' => 'Invalid format. Missing headers: ' . implode(', ', $missingHeaders)
            ]);
        }

        $headerMap = [];
        foreach ($actualHeaders as $index => $header) {
            if (isset($expectedHeaders[$header])) {
                $headerMap[$index] = $expectedHeaders[$header];
            }
        }

        $existingRecords = ExcelUpload::select('pallet')->get()
            ->map(fn($record) => $record->pallet)->toArray();

        $newRecords = [];
        $updatedRecords = [];
        $deletedRecords = [];
        $duplicates = [];
        $notFoundRecords = [];

        DB::beginTransaction();

        foreach ($rows as $index => $row) {
            if ($index === 0 || !is_array($row) || empty(array_filter($row))) continue;

            $recordData = [];

            foreach ($headerMap as $colIndex => $dbColumn) {
                $value = $row[$colIndex] ?? null;
                if (!is_null($value) && $value !== '') {
                    $recordData[$dbColumn] = $value;
                }
            }

            foreach (array_values($expectedHeaders) as $expectedKey) {
                if (!array_key_exists($expectedKey, $recordData)) {
                    $recordData[$expectedKey] = null;
                }
            }

            // Unique Key
            $uniqueKey = $recordData['pallet'] ?? '';

            if ($action === 'insert') {
                if (in_array($uniqueKey, $existingRecords)) {
                    $duplicates[] = $uniqueKey;
                } else {
                    $recordData['load_user_id'] = auth()->id(); // ✅ Add user ID
                    $newRecords[] = $recordData;
                }

            } elseif ($action === 'update') {
                $existingRecord = ExcelUpload::where('pallet', $uniqueKey)->first();

                if ($existingRecord) {
                    $changed = false;

                    foreach ($recordData as $column => $value) {
                        if ($value !== null && $value !== '') {
                            if ($existingRecord->{$column} !== $value) {
                                $existingRecord->{$column} = $value;
                                $changed = true;
                            }
                        }
                    }

                    if ($changed) {
                        $existingRecord->load_user_id = auth()->id(); // ✅ Optional: log updater
                        $existingRecord->save();
                        $updatedRecords[] = $recordData;
                    }

                } else {
                    $notFoundRecords[] = $uniqueKey;
                }

            } elseif ($action === 'delete') {
                $existingRecord = ExcelUpload::where('pallet', $uniqueKey);
                if ($existingRecord->exists()) {
                    $existingRecord->delete();
                    $deletedRecords[] = $recordData;
                }
            }
        }

        if ($action === 'insert' && !empty($newRecords)) {
            foreach ($newRecords as $record) {
                ExcelUpload::create($record);
            }
        }

        if ($action === 'update' && !empty($notFoundRecords)) {
            DB::rollBack();
            return redirect()->back()->withErrors([
                'error' => 'Update failed. These POs were not found: ' . implode(', ', $notFoundRecords)
            ]);
        }

        DB::commit();

        $uploadedData = match ($action) {
            'insert' => $newRecords,
            'update' => $updatedRecords,
            'delete' => $deletedRecords,
            default => [],
        };

        if ($action === 'insert' && !empty($duplicates)) {
            return redirect()->back()->withErrors([
                'error' => 'Duplicate entries found: ' . implode(', ', $duplicates)
            ]);
        }

        return redirect()->route('import.excel')->with('success', ucfirst($action) . ' operation completed successfully.')
            ->with('uploadedData', $uploadedData);

    } catch (\Illuminate\Database\QueryException $e) {
        DB::rollBack();

        if ($e->errorInfo[1] == 1062) {
            preg_match("/Duplicate entry '(.*?)' for key/", $e->getMessage(), $matches);
            $duplicateEntry = $matches[1] ?? 'unknown';

            return redirect()->back()->withErrors([
                'error' => "Duplicate entry found: $duplicateEntry."
            ]);
        }

        return redirect()->back()->withErrors([
            'error' => 'Database Error: ' . $e->getMessage()
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()->withErrors([
            'error' => 'Unexpected Error: ' . $e->getMessage()
        ]);
    }
}


    public function show($number)
{
    $pallet = ExcelUpload::where('pallet', $number)->first();

    if (!$pallet) {
        return response()->json(['error' => 'Pallet not found'], 404);
    }

    $pallet->printed_status = 'Printed';
    $pallet->printed_time = Carbon::now();

    // Make sure the user is authenticated
    if (Auth::check()) {
        $pallet->printed_user = Auth::id();
    }

    $pallet->save();

    return response()->json([
        'supplier_hu' => $pallet->supplier_hu,
        'lot_number' => $pallet->lot_number,
        'color' => $pallet->color,
        'asn_number' => $pallet->asn_number,
        'client_so' => $pallet->client_so,
        'plant' => $pallet->plant,
        'new_item_number' => $pallet->new_item_number,
        'pallet' => $pallet->pallet,
        'actual_qty' => $pallet->actual_qty,
        'partial_order_flag' => $pallet->partial_order_flag,
    ]);
}


public function printLabel(Request $request)
{
    $request->validate([
        'pallet' => 'required|string',
    ]);

   $pallet = ExcelUpload::where('pallet', $request->pallet)
    ->where('printed_status', 'Not Printed')
    ->first();


    if (!$pallet) {
        return response()->json(['error' => 'Pallet not found'], 404);
    }

    $pallet->printed_status = 'Printed';
    $pallet->printed_time = Carbon::now();
    $pallet->printed_user = Auth::id(); // user ID from Sanctum-authenticated user
    $pallet->save();

    return response()->json([
        'supplier_hu' => $pallet->supplier_hu,
        'lot_number' => $pallet->lot_number,
        'color' => $pallet->color,
        'invoice_number' => $pallet->invoice_number,
        'client_so' => $pallet->client_so,
        'plant' => $pallet->plant,
        'new_item_number' => $pallet->new_item_number,
        'pallet' => $pallet->pallet,
        'actual_qty' => $pallet->actual_qty,
        'customer_po_number' => $pallet->customer_po_number,
        'printed_time' => $pallet->printed_time ? $pallet->printed_time->format('Y-m-d H:i:s') : 'Not Printed',
    ]);
}

public function showdata(Request $request)
{
    $query = ExcelUpload::with('printedUser'); // eager load relationship

    if ($request->has('printed_status') && !empty($request->printed_status)) {
        $query->where('printed_status', $request->printed_status);
    }
    $completedCount = ExcelUpload::where('printed_status', 'Not Printed')->count();
    $pendingCount = ExcelUpload::where('printed_status', 'Printed')->count();


    if ($request->has('search') && !empty($request->search)) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('pallet', 'like', '%' . $search . '%')
              ->orWhere('supplier_hu', 'like', '%' . $search . '%')
              ->orWhere('lot_number', 'like', '%' . $search . '%');
        });
    }

    $data = $query->get();

    return view('SwatchFullDetails', compact('data','completedCount', 'pendingCount'));
}


public function exportExcel(Request $request)
{

    $request->validate([
        'start_date' => 'required|date',
        'end_date' => 'required|date|after_or_equal:start_date',
    ]);


    $query = ExcelUpload::query();


    if ($request->has('start_date') && $request->has('end_date')) {
    $startDate = $request->start_date . ' 00:00:00';
    $endDate = $request->end_date . ' 23:59:59';

    $query->whereBetween('created_at', [$startDate, $endDate]);
}






    if ($request->has('search') && !empty($request->search)) {
        $query->where('pallet', 'like', '%' . $request->search . '%');
    }

    $data = $query->get();

    // Create a new spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Set headers
    $headers = [
        'A1' => 'WH ID', 'B1' => 'CLIENT CODE', 'C1' => 'PALLET', 'D1' => 'INVOICE NUMBER', 'E1' => 'LOCATION ID',
        'F1' => 'ITEM NUMBER', 'G1' => 'DESCRIPTION', 'H1' => 'LOT NUMBER', 'I1' => 'ACTUAL QTY', 'J1' => 'UNAVAILABLE QTY',
        'K1' => 'UOM', 'L1' => 'STATUS', 'M1' => 'MLP', 'N1' => 'STORED ATTRIBUTE ID', 'O1' => 'FIFO DATE',
        'P1' => 'EXPIRATION DATE', 'Q1' => 'GRN NUMBER', 'R1' => 'GATE PASS ID', 'S1' => 'CUST DEC NO', 'T1' => 'COLOR',
        'U1' => 'SIZE', 'V1' => 'STYLE', 'W1' => 'SUPPLIER', 'X1' => 'PLANT', 'Y1' => 'CLIENT SO',
        'Z1' => 'CLIENT SO LINE', 'AA1' => 'PO CUST DEC', 'AB1' => 'CUSTOMER REF NUMBER', 'AC1' => 'ITEM ID',
        'AD1' => 'INVOICE NUMBER1', 'AE1' => 'TRANSACTION', 'AF1' => 'ORDER TYPE', 'AG1' => 'ORDER NUMBER',
        'AH1' => 'STORE ORDER NUMBER', 'AI1' => 'CUSTOMER PO NUMBER', 'AJ1' => 'PARTIAL ORDER FLAG', 'AK1' => 'ORDER DATE',
        'AL1' => 'LOAD ID', 'AM1' => 'ASN NUMBER', 'AN1' => 'PO NUMBER', 'AO1' => 'SUPPLIER HU', 'AP1' => 'NEW ITEM NUMBER',
        'AQ1' => 'PRINTED STATUS', 'AR1' => 'PRINTED USER', 'AS1' => 'PRINTED TIME', 'AT1' => 'UPLOADED USER', 'AU1' => 'UPLOADED TIME',
    ];

    // Style the header row
$headerStyle = [
    'font' => [
        'bold' => true,
        'color' => ['rgb' => 'FFFFFF'], // White font
    ],
    'fill' => [
        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
        'startColor' => ['rgb' => '4F81BD'], // Light blue background
    ],
];

// Apply the style to the header row (A1 to AU1)
$sheet->getStyle('A1:AU1')->applyFromArray($headerStyle);

    foreach ($headers as $cell => $header) {
        $sheet->setCellValue($cell, $header);
    }

    // Add data to rows
    $row = 2;
    foreach ($data as $item) {
        $sheet->setCellValue('A' . $row, $item->wh_id);
        $sheet->setCellValue('B' . $row, $item->client_code);
        $sheet->setCellValue('C' . $row, $item->pallet);
        $sheet->setCellValue('D' . $row, $item->invoice_number);
        $sheet->setCellValue('E' . $row, $item->location_id);
        $sheet->setCellValue('F' . $row, $item->item_number);
        $sheet->setCellValue('G' . $row, $item->description);
        $sheet->setCellValue('H' . $row, $item->lot_number);
        $sheet->setCellValue('I' . $row, $item->actual_qty);
        $sheet->setCellValue('J' . $row, $item->unavailable_qty);
        $sheet->setCellValue('K' . $row, $item->uom);
        $sheet->setCellValue('L' . $row, $item->status);
        $sheet->setCellValue('M' . $row, $item->mlp);
        $sheet->setCellValue('N' . $row, $item->stored_attribute_id);
        $sheet->setCellValue('O' . $row, $item->fifo_date);
        $sheet->setCellValue('P' . $row, $item->expiration_date);
        $sheet->setCellValue('Q' . $row, $item->grn_number);
        $sheet->setCellValue('R' . $row, $item->gatepass_id);
        $sheet->setCellValue('S' . $row, $item->cust_dec_number);
        $sheet->setCellValue('T' . $row, $item->color);
        $sheet->setCellValue('U' . $row, $item->size);
        $sheet->setCellValue('V' . $row, $item->style);
        $sheet->setCellValue('W' . $row, $item->supplier);
        $sheet->setCellValue('X' . $row, $item->plant);
        $sheet->setCellValue('Y' . $row, $item->client_so);
        $sheet->setCellValue('Z' . $row, $item->client_so_line);
        $sheet->setCellValue('AA' . $row, $item->po_cust_dec);
        $sheet->setCellValue('AB' . $row, $item->customer_ref_number);
        $sheet->setCellValue('AC' . $row, $item->item_id);
        $sheet->setCellValue('AD' . $row, $item->invoice_number_1);
        $sheet->setCellValue('AE' . $row, $item->transaction);
        $sheet->setCellValue('AF' . $row, $item->order_type);
        $sheet->setCellValue('AG' . $row, $item->order_number);
        $sheet->setCellValue('AH' . $row, $item->store_order_number);
        $sheet->setCellValue('AI' . $row, $item->customer_po_number);
        $sheet->setCellValue('AJ' . $row, $item->partial_order_flag);
        $sheet->setCellValue('AK' . $row, $item->order_date);
        $sheet->setCellValue('AL' . $row, $item->load_id);
        $sheet->setCellValue('AM' . $row, $item->asn_number);
        $sheet->setCellValue('AN' . $row, $item->po_number);
        $sheet->setCellValue('AO' . $row, $item->supplier_hu);
        $sheet->setCellValue('AP' . $row, $item->new_item_number);
        $sheet->setCellValue('AQ' . $row, $item->printed_status);
        $sheet->setCellValue('AR' . $row, $item->printedUser ? $item->printedUser->name : '');
        $sheet->setCellValue('AS' . $row, $item->printed_time ? \Carbon\Carbon::parse($item->printed_time)->format('Y-m-d H:i:s') : 'Not Printed');
        $sheet->setCellValue('AT' . $row, $item->uploadedUser ? $item->uploadedUser->name : 'Unknown User');
        $sheet->setCellValue('AU' . $row, $item->created_at ? $item->created_at->format('Y-m-d H:i:s') : 'N/A');

        $row++;
    }

    // Set filename with date range
    $fileName = 'Hidramani_swatch_details_' . $request->start_date . '_to_' . $request->end_date . '.xlsx';

    // Create a writer and stream the file to the browser
    $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
    $response = new StreamedResponse(function () use ($writer) {
        $writer->save('php://output');
    });

    $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    $response->headers->set('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    $response->headers->set('Cache-Control', 'max-age=0');

    return $response;
}





   public function getAllItemNumbers()
{
    $items = DB::table('excel_uploads')
        ->where('printed_status', 'Not Printed')
        ->select('new_item_number')
        ->distinct()
        ->orderBy('new_item_number')
        ->pluck('new_item_number');

    return response()->json($items);
}


 public function getHusByLocation(Request $request)
{
    try {
        $request->validate([
            'new_item_number' => 'required|string',
            'location_id' => 'required|string',
        ]);

        $hus = DB::table('excel_uploads')
            ->where('new_item_number', $request->new_item_number)
            ->where('location_id', $request->location_id)
            ->where('printed_status', 'Not Printed')
            ->select([
                'pallet',
                'supplier_hu',
                'lot_number',
                'color',
                'invoice_number',
                'client_so',
                'plant',
                'new_item_number',
                'actual_qty',
                'customer_po_number',
            ])
            ->orderBy('pallet')
            ->get();

        return response()->json($hus);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Server error: ' . $e->getMessage(),
        ], 500);
    }
}




public function showprinteddata(Request $request)
{
    $query = ExcelUpload::with('printedUser')
                ->where('printed_status', 'Printed'); // always filter Printed


    if ($request->has('search') && !empty($request->search)) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('pallet', 'like', '%' . $search . '%')
              ->orWhere('supplier_hu', 'like', '%' . $search . '%')
              ->orWhere('lot_number', 'like', '%' . $search . '%');
        });
    }

    $data = $query->get();

    return view('printeddata', compact('data'));
}



public function togglePrintedStatus(Request $request)
{
    $request->validate([
        'id' => 'required|integer',
        'printed_status' => 'required|string'
    ]);

    $item = ExcelUpload::findOrFail($request->id);
    $item->printed_status = $request->printed_status;
    $item->save();

    return response()->json(['success' => true]);
}

public function showUnprintedPage()
{
    return view('deleterecords');
}

public function fetchUnprintedRecords(Request $request)
{
    $request->validate([
        'new_item_number' => 'required|string'
    ]);

    $records = ExcelUpload::where('new_item_number', $request->new_item_number)
                ->where('printed_status', 'Not Printed')
                ->get();

    return response()->json($records);
}

public function deleteUnprintedRecords(Request $request)
{
    $request->validate([
        'ids' => 'required|array'
    ]);

    ExcelUpload::whereIn('id', $request->ids)->delete();

    return response()->json(['success' => true]);
}



}

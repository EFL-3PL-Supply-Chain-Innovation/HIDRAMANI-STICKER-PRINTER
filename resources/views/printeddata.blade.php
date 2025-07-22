<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Loading Team Dashboard</title>
  <meta http-equiv="refresh" content="150">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
  <style>
    .navbar { z-index: 1000; }
    .main-header img { width: 50px; height: 50px; }
    .badge { font-size: 0.8rem; }
    .bg-lightblue { background-color: rgb(156, 190, 142); }
    .topic {
      color: rgb(232, 134, 42);
      font-family: Georgia, serif;
      font-weight: bold;
      font-size: 22px;
      text-align: center;
      flex: 1;
      margin-left: 230px;
    }
    .table-responsive { overflow-x: auto; }
    .pagination { color: black; }
    .pagination a { color:black; }
    .pagination a:hover {
      background-color: rgb(232, 134, 42);
      color:white;
    }
    .pagination .page-item.active .page-link {
      background-color: rgb(232, 134, 42) !important;
      color: white !important;
      border-color: rgb(232, 134, 42) !important;
    }
    @media (max-width: 768px) {
      .topic { font-size: 18px; text-align: center; margin: 10px 0; }
      .main-header img { width: 150px; height: auto; }
    }
    .table th, .table td { font-size: 0.75rem; }
    .alert { margin-top: 1rem; }
    .dataTables_filter { display: none !important; }
  </style>
</head>
<body>

<!-- Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white mb-4">
  <div class="container-fluid">
    <a class="navbar-brand main-header" href="#">
      <img src="{{ asset('images/logohi.jpg') }}" alt="Logo" style="height:70px; width:260px;">
    </a>
    <div class="topic">Hidramani Swatch Sticker Print</div>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
      <ul class="navbar-nav">
        @if(auth()->check() && auth()->user()->role === 'Admin')
        <li class="nav-item"><a class="nav-link" href="{{ route('operate.excel') }}">Upload</a></li>
        <li class="nav-item"><a class="nav-link" href="{{ route('operate.showdata') }}">Show Data</a></li>
        <li class="nav-item"><a class="nav-link" href="{{ route('create') }}">Create User</a></li>
        <li class="nav-item"><a class="nav-link" href="{{ route('showprinteddata') }}">Show Printed Data</a></li>
        @endif
      </ul>
    </div>
  </div>
</nav>

<div class="container-fluid">
  @if(session('success'))
    <div class="alert alert-success" id="success-alert">{{ session('success') }}</div>
  @endif
  @if($errors->any())
    <div class="alert alert-danger" id="error-alert">
      <ul class="mb-0">@foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach</ul>
    </div>
  @endif

  <button type="button" onclick="toggleColumns()" title="Toggle Attributes" class="btn btn-outline-secondary me-2">
    <i class="fas fa-th"></i> View Attributes
  </button>
</div>

<div class="table-responsive mt-2">
  <table id="styledTable" class="table table-bordered table-hover table-striped align-middle w-100 text-nowrap" style="font-size: 12px;">
    <thead class="table-dark text-white" style="font-size: 11px;">
      <tr>
        <th>WH ID</th>
        <th>CLIENT CODE</th>
        <th>PALLET</th>
        <th>INVOICE NUMBER</th>
        <th>LOT NUMBER</th>
        <th>ACTUAL QTY</th>
        <th>UOM</th>
        <th>STYLE</th>
        <th>PLANT</th>
        <th>CLIENT SO</th>
        <th>CUSTOMER PO NUMBER</th>
        <th>SUPPLIER HU</th>
        <th>NEW ITEM NUMBER</th>
        <th>PRINTED STATUS</th>
        <th>PRINTED USER</th>
        <th>PRINTED TIME</th>
        <th>UPLOADED USER</th>
        <th>UPLOADED TIME</th>
      </tr>
    </thead>
    <tbody style="font-size:9px;">
      @foreach($data as $row)
      <tr>
        <td>{{ $row->wh_id ?? 'N/A' }}</td>
        <td>{{ $row->client_code ?? 'N/A' }}</td>
        <td>{{ $row->pallet ?? 'N/A' }}</td>
        <td>{{ $row->invoice_number ?? 'N/A' }}</td>
        <td>{{ $row->lot_number ?? 'N/A' }}</td>
        <td>{{ $row->actual_qty ?? 'N/A' }}</td>
        <td>{{ $row->uom ?? 'N/A' }}</td>
        <td>{{ $row->style ?? 'N/A' }}</td>
        <td>{{ $row->plant ?? 'N/A' }}</td>
        <td>{{ $row->client_so ?? 'N/A' }}</td>
        <td>{{ $row->customer_po_number ?? 'N/A' }}</td>
        <td>{{ $row->supplier_hu ?? 'N/A' }}</td>
        <td>{{ $row->new_item_number ?? 'N/A' }}</td>
        <td class="text-center">
          <button class="btn btn-sm toggle-status-btn {{ $row->printed_status === 'Printed' ? 'btn-success' : 'btn-danger' }}"
            data-id="{{ $row->id }}"
            data-status="{{ $row->printed_status }}">
            {{ $row->printed_status === 'Printed' ? 'Printed' : 'Not Printed' }}
          </button>
        </td>
        <td>{{ $row->printedUser->name ?? 'N/A' }}</td>
        <td>{{ $row->printed_time ?? 'N/A' }}</td>
        <td>{{ $row->uploadedUser->name ?? 'N/A' }}</td>
        <td>{{ $row->created_at ?? 'N/A' }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
  let table;
  $(document).ready(function () {
    table = $('#styledTable').DataTable({
      responsive: true,
      paging: true,
      pageLength: 10,
      lengthChange: false,
      searching: true,
      ordering: false,
      info: false,
      columnDefs: [{ targets: [3,4,5,6,7,8,9,10], visible: false }]
    });

    // Toggle printed status on click
    $('.table').on('click', '.toggle-status-btn', function() {
      var btn = $(this);
      var rowId = btn.data('id');
      var currentStatus = btn.data('status');
      var newStatus = (currentStatus === 'Printed') ? 'Not Printed' : 'Printed';

      $.ajax({
        url: '{{ route("toggle.printed.status") }}',
        method: 'POST',
        data: {
          _token: '{{ csrf_token() }}',
          id: rowId,
          printed_status: newStatus
        },
        success: function() {
          btn.data('status', newStatus);
          btn.text(newStatus);
          btn.toggleClass('btn-success btn-danger');
        },
        error: function(xhr) {
          console.log(xhr.responseText);
          alert('Failed to update status.');
        }
      });
    });
  });

  function toggleColumns() {
    const toggleIndices = [3,4,5,6,7,8,9,10];
    toggleIndices.forEach(i => {
      const vis = table.column(i).visible();
      table.column(i).visible(!vis);
    });
  }
</script>
</body>
</html>

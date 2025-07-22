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
    .navbar {
      z-index: 1000;
    }


    .main-header img {
      width: 50px;
      height: 50px;
    }

    .badge {
      font-size: 0.8rem;
    }

    .bg-lightblue {
      background-color: rgb(156, 190, 142);
    }

    .topic {
      color: rgb(232, 134, 42);
      font-family: Georgia, serif;
      font-weight: bold;
      font-size: 22px;
      text-align: center;
      flex: 1;
      margin-left: 230px;
    }

      .table-responsive {
                overflow-x: auto;
            }

             .pagination {
            color: black;
        }
        .pagination a {
            color:black;
        }
        .pagination a:hover {
            background-color: rgb(232, 134, 42);
            color:white; /* Change hover color */

        }

        .pagination .page-item.active .page-link {
            background-color: rgb(232, 134, 42) !important; /* Active page color */
            color: white !important;
            border-color: rgb(232, 134, 42) !important;
        }

    @media (max-width: 768px) {
      .topic {
        font-size: 18px;
        text-align: center;
        margin: 10px 0;
      }

      .main-header img {
        width: 150px;
        height: auto;
      }
    }

    .table th, .table td {
      font-size: 0.75rem;
    }

    .alert {
      margin-top: 1rem;
    }
     .dataTables_filter {
    display: none !important;
  }
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
        <li class="nav-item">
          <a class="nav-link" href="{{ route('operate.excel') }}">Upload</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="{{ route('operate.showdata') }}">Show Data</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="{{ route('create') }}">Create User</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="{{ route('showprinteddata') }}">Show Printed Data</a>
        </li>
        @endif
      </ul>
    </div>
  </div>
</nav>

<div class="container-fluid">
  @if(session('success'))
  <div class="alert alert-success" id="success-alert">
    {{ session('success') }}
  </div>
  @endif

  @if($errors->any())
  <div class="alert alert-danger" id="error-alert">
    <ul class="mb-0">
      @foreach ($errors->all() as $error)
      <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
  @endif


        <div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exportModalLabel">Select Date Range</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('export.excel') }}" method="GET" id="exportForm">
    <label for="start_date">Start Date:</label>
    <input type="date" name="start_date" id="start_date" class="form-control" required>

    <label for="end_date" class="mt-2">End Date:</label>
    <input type="date" name="end_date" id="end_date" class="form-control" required>

    <input type="hidden" name="search" value="{{ request('search') }}">


</form>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn text-white" form="exportForm" style="background-color: rgb(232, 134, 42);">Export</button>
            </div>
        </div>
    </div>
</div>









   <div class="d-flex align-items-center g-2 m-3" style="margin-top: 5px;">
  <div class="me-2" style="min-width: 150px; cursor: pointer;" onclick="filterByStatus('Not Printed')">
    <div class="card text-white bg-danger mb-0" style="text-align: center;">
      <div class="card-body p-2">
        <h5 class="card-title mb-1" style="font-size: 15px;">Not Printed</h5>
        <p class="card-text mb-0">{{ $completedCount }}</p>
      </div>
    </div>
  </div>

  <div class="me-2" style="min-width: 150px; cursor: pointer;" onclick="filterByStatus('Printed')">
    <div class="card text-white bg-success mb-0" style="text-align: center;">
      <div class="card-body p-2">
        <h5 class="card-title mb-1" style="font-size: 15px;">Printed</h5>
        <p class="card-text mb-0">{{ $pendingCount }}</p>
      </div>
    </div>
  </div>

  <button type="button" onclick="clearFilter()" class="btn btn-outline-secondary me-2">
    Clear Filter
  </button>

  <button type="button" onclick="toggleColumns()" title="Toggle Attributes" class="btn btn-outline-secondary me-2">
    <i class="fas fa-th"></i> View Attributes
  </button>

  <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#exportModal" style="background-color: seagreen; color: white;">
    <i class="fas fa-share-square"></i> Export
  </button>
</div>

<div class="table-responsive">
  <table id="styledTable" class="table table-bordered table-hover table-striped align-middle w-100 text-nowrap" style="font-size: 12px;">
    <thead class="table-dark text-white" style="font-size: 11px;">
      <tr>
        <th>WH ID</th>                  <!-- 0 -->
        <th>CLIENT CODE</th>            <!-- 1 -->
        <th>PALLET</th>                 <!-- 2 -->
        <th>INVOICE NUMBER</th>         <!-- 3 -->
        <th>LOT NUMBER</th>             <!-- 4 -->
        <th>ACTUAL QTY</th>             <!-- 5 -->
        <th>UOM</th>                    <!-- 6 -->
        <th>STYLE</th>                  <!-- 7 -->
        <th>PLANT</th>                  <!-- 8 -->
        <th>CLIENT SO</th>              <!-- 9 -->
        <th>CUSTOMER PO NUMBER</th>     <!-- 10 -->
        <th>SUPPLIER HU</th>            <!-- 11 -->
        <th>NEW ITEM NUMBER</th>        <!-- 12 -->
        <th>PRINTED STATUS</th>         <!-- 13 -->
        <th>PRINTED USER</th>           <!-- 14 -->
        <th>PRINTED TIME</th>           <!-- 15 -->
        <th>UPLOADED USER</th>          <!-- 16 -->
        <th>UPLOADED TIME</th>          <!-- 17 -->
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
          @if($row->printed_status === 'Printed')
            <span class="badge bg-success">Printed</span>
          @elseif($row->printed_status === 'Not Printed')
            <span class="badge bg-danger">Not Printed</span>
          @else
            <span class="badge bg-secondary">{{ $row->printed_status ?? 'N/A' }}</span>
          @endif
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>


<script>
  let table;

  $(document).ready(function () {
    table = $('#styledTable').DataTable({
      responsive: true,
      paging: true,
      pageLength: 10,
      lengthChange: false,
      searching: true,  // Keep searching enabled internally for filtering
      ordering: false,
      info: false,
      columnDefs: [
        { targets: [3,4,5,6,7,8,9,10], visible: false },
      ]
    });
  });

  function filterByStatus(status) {
    // Use regex to exactly match the status string in column 13 (Printed Status)
    table.column(13).search('^' + status + '$', true, false).draw();
  }

  function clearFilter() {
    table.column(13).search('').draw();
  }

  function toggleColumns() {
    const toggleIndices = [3,4,5,6,7,8,9,10];
    toggleIndices.forEach(i => {
      const vis = table.column(i).visible();
      table.column(i).visible(!vis);
    });
  }
</script>

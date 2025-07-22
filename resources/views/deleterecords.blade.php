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

   <div class="row mb-3">
  <div class="col-3">
    <input type="text" id="newItemNumber" class="form-control" placeholder="Enter New Item Number">
  </div>
  <div class="col-auto">
    <button class="btn" id="searchBtn" style="background-color: rgb(232, 134, 42); color: white;">
      <i class="fas fa-search"></i> Search
    </button>
  </div>
</div>



    <form id="recordsForm">
        <table class="table table-bordered table-striped" id="recordsTable" style="display:none;">
            <thead class="table-dark">
                <tr>
                    <th><input type="checkbox" id="selectAll"></th>
                    <th>ID</th>
                    <th>WH ID</th>
                    <th>PALLET</th>
                    <th>LOCATION ID</th>
                    <th>INVOICE NUMBER</th>
                    <th>LOT NUMBER</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>

        <button type="button" class="btn btn-danger mt-2" id="deleteSelected" style="display:none;">
            Delete Selected
        </button>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$('#searchBtn').click(function() {
    let itemNumber = $('#newItemNumber').val();
    if(!itemNumber) {
        alert('Please enter New Item Number.');
        return;
    }

    $.ajax({
        url: '{{ route("fetch.unprinted.records") }}',
        method: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            new_item_number: itemNumber
        },
        success: function(records) {
            let tbody = $('#recordsTable tbody');
            tbody.empty();

            if(records.length === 0) {
                alert('No unprinted records found.');
                $('#recordsTable').hide();
                $('#deleteSelected').hide();
                return;
            }

            records.forEach(record => {
                tbody.append(`
                    <tr>
                        <td><input type="checkbox" name="ids[]" value="${record.id}"></td>
                        <td>${record.id}</td>
                        <td>${record.wh_id ?? 'N/A'}</td>
                        <td>${record.pallet ?? 'N/A'}</td>
                        <td>${record.location_id ?? 'N/A'}</td>
                        <td>${record.invoice_number ?? 'N/A'}</td>
                        <td>${record.lot_number ?? 'N/A'}</td>
                    </tr>
                `);
            });

            $('#recordsTable').show();
            $('#deleteSelected').show();
        },
        error: function(xhr) {
            console.log(xhr.responseText);
            alert('Error fetching records.');
        }
    });
});

$('#selectAll').click(function() {
    $('input[name="ids[]"]').prop('checked', this.checked);
});

$('#deleteSelected').click(function() {
    let selected = $('input[name="ids[]"]:checked').map(function(){ return this.value; }).get();

    if(selected.length === 0) {
        alert('Please select records to delete.');
        return;
    }

    if(!confirm('Are you sure you want to delete selected records?')) return;

    $.ajax({
        url: '{{ route("delete.unprinted.records") }}',
        method: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            ids: selected
        },
        success: function() {
            alert('Selected records deleted.');
            $('#searchBtn').click(); // reload
        },
        error: function(xhr) {
            console.log(xhr.responseText);
            alert('Failed to delete records.');
        }
    });
});
</script>


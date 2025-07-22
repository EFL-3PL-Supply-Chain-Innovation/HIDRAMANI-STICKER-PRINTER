<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Loading Team Dashboard</title>
  <meta http-equiv="refresh" content="100">
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
        <li class="nav-item">
          <a class="nav-link" href="{{ route('unprinted.records') }}">Show Unprinted Data</a>
        </li>
        @endif
      </ul>
    </div>
  </div>
</nav>

<div class="container">
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

  <form action="{{ route('import.excel') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="mb-4">
      <h2 class="mb-3">Upload the Excel</h2>
      <div class="row">
        <div class="col-md-6 mb-3">
          <label for="file" class="form-label">Load the Excel Sheet</label>
          <input type="file" name="file" class="form-control" required>
        </div>
      </div>

      <div class="d-flex flex-wrap gap-2 mt-3">
        <button type="submit" name="action" value="insert" class="btn" style="background-color:rgb(232, 134, 42); color:white;">Insert</button>
        <button type="submit" name="action" value="update" class="btn" style="background-color:rgb(232, 134, 42); color:white;">Update</button>
        <button type="submit" name="action" value="delete" class="btn" style="background-color:rgb(232, 134, 42); color:white;">Delete</button>
      </div>
    </div>
  </form>

  @if(session('uploadedData') && count(session('uploadedData')) > 0)
  <h3>Uploaded Data</h3>
  <div class="table-responsive">
    <table class="table table-bordered table-hover table-striped align-middle">
      <thead class="table-dark text-white">
        <tr>
          <th>WH ID</th>
          <th>Client Code</th>
          <th>Pallet</th>
          <th>PO</th>
          <th>Location ID</th>
          <th>Description</th>
          <th>LOT Number</th>
          <th>Actual Qty</th>
          <th>Unavailable Qty</th>
          <th>UOM</th>
          <th>Status</th>
          <th>MLP</th>
          <th>Stored Attribute ID</th>
          <th>FIFO Date</th>
          <th>Expiration Date</th>
          <th>GRN Number</th>
          <th>Gatepass ID</th>
          <th>Cust Dec Number</th>
          <th>Color</th>
          <th>Size</th>
          <th>Style</th>
          <th>Supplier</th>
          <th>Plant</th>
        </tr>
      </thead>
      <tbody>
        @foreach(session('uploadedData') as $data)
        <tr>
          <td>{{ $data['wh_id'] ?? 'N/A' }}</td>
          <td>{{ $data['client_code'] ?? 'N/A' }}</td>
          <td>{{ $data['pallet'] ?? 'N/A' }}</td>
          <td>{{ $data['po'] ?? 'N/A' }}</td>
          <td>{{ $data['location_id'] ?? 'N/A' }}</td>
          <td>{{ $data['description'] ?? 'N/A' }}</td>
          <td>{{ $data['lot_number'] ?? 'N/A' }}</td>
          <td>{{ $data['actual_qty'] ?? 'N/A' }}</td>
          <td>{{ $data['unavailable_qty'] ?? 'N/A' }}</td>
          <td>{{ $data['uom'] ?? 'N/A' }}</td>
          <td>{{ $data['status'] ?? 'N/A' }}</td>
          <td>{{ $data['mlp'] ?? 'N/A' }}</td>
          <td>{{ $data['stored_attribute_id'] ?? 'N/A' }}</td>
          <td>{{ $data['fifo_date'] ?? 'N/A' }}</td>
          <td>{{ $data['expiration_date'] ?? 'N/A' }}</td>
          <td>{{ $data['grn_number'] ?? 'N/A' }}</td>
          <td>{{ $data['gatepass_id'] ?? 'N/A' }}</td>
          <td>{{ $data['cust_dec_number'] ?? 'N/A' }}</td>
          <td>{{ $data['color'] ?? 'N/A' }}</td>
          <td>{{ $data['size'] ?? 'N/A' }}</td>
          <td>{{ $data['style'] ?? 'N/A' }}</td>
          <td>{{ $data['supplier'] ?? 'N/A' }}</td>
          <td>{{ $data['plant'] ?? 'N/A' }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @endif
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
  setTimeout(function () {
    const successAlert = document.getElementById('success-alert');
    const errorAlert = document.getElementById('error-alert');
    if (successAlert) {
      successAlert.style.opacity = '0';
      setTimeout(() => successAlert.remove(), 500);
    }
    if (errorAlert) {
      errorAlert.style.opacity = '0';
      setTimeout(() => errorAlert.remove(), 500);
    }
  }, 4000);
</script>
</body>
</html>

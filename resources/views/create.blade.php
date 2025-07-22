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
      margin-left: 230px;
      flex: 1;
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
        @endif
      </ul>
    </div>
  </div>
</nav>



<div class="content">
    <div class="container">
        @if(session('success'))
        <div class="alert alert-success" id="success-alert">
            {{ session('success') }}
        </div>
    @endif

    <!-- Error Alerts -->
    @if($errors->any())
        <div class="alert alert-danger" id="error-alert">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif


   <form action="{{ route('create.store') }}" method="POST">
        @csrf
        <div class="row">

            <div class="col-md-6 mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
        </div>

        <div class="col-md-6 mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required>
        </div>
        </div>

        <div class="row">

            <div class="col-md-6 mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>

        <div class="col-md-6 mb-3">
            <label for="password_confirmation" class="form-label">Confirm Password</label>
            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
        </div>
        </div>

        <div class="col-md-6 mb-3">
            <label for="role" class="form-label">Role</label>
            <select name="role" id="role" class="form-select" required>
                <option value="">Select Role</option>
                <option value="Admin">Admin</option>
                <option value="User">User</option>
            </select>
        </div>


        <button type="submit" class="btn" style="background-color: rgb(232, 134, 42); color:white">Create User</button>
    </form>
</div>

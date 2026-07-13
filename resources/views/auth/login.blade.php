<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Employee Records Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9; 
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #334155; 
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            padding: 2.5rem;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            border-top: 4px solid #FDE047; 
            margin: 15px;
        }
        
        .btn-custom {
            background-color: #FDE047;
            color: #334155;
            padding: 0.6rem;
            font-weight: 600;
            border: none;
            transition: all 0.2s ease;
        }
        .btn-custom:hover {
            background-color: #EAB308; 
            color: #ffffff;
        }
        
        .form-control:focus {
            border-color: #FDE047;
            box-shadow: 0 0 0 0.25rem rgba(253, 224, 71, 0.25);
        }
        
        .text-accent {
            color: #EAB308; 
        }
    </style>
</head>
<body>

    <div class="login-card">
        
        <div class="text-center mb-4">
            <img src="{{ asset('build/assets/logo.png') }}" alt="CNHS Logo" class="rounded-circle shadow-sm" style="width: 100px; height: 100px; object-fit: cover;">
        </div>
        
        <h4 class="text-center mb-4 text-accent fw-bold">Employee Records Management System</h4>

        @if(session('error'))
            <div class="alert alert-danger p-2 text-center small fw-bold" role="alert">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('login.post') }}" method="POST">
            @csrf
            
            <div class="mb-3">
                <label for="id_number" class="form-label text-muted small fw-bold">ID Number</label>
                <input type="text" class="form-control" id="id_number" name="id_number" placeholder="Enter your ID number" required autofocus>
            </div>
            
            <div class="mb-4">
                <label for="password" class="form-label text-muted small fw-bold">Password</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
            </div>
            
            <div class="d-grid">
                <button type="submit" class="btn btn-custom">Login</button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
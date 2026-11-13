<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="@yield('description', 'SubShield — Privacy-first digital subscription tracker.')">
    <title>@yield('title', 'SubShield')</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" integrity="sha384-tViUnnbYAV00FLIhhi3v/dWt3Jxw4gZQcNoSCxCIFNJVCx7/D55/wXsrNIRANwdD" crossorigin="anonymous">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .auth-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, .3);
            max-width: 440px;
            width: 100%;
        }
        .btn-indigo {
            background: #6366f1;
            border-color: #6366f1;
            color: #fff;
        }
        .btn-indigo:hover {
            background: #4f46e5;
            border-color: #4f46e5;
            color: #fff;
        }
    </style>
</head>
<body>
<main class="container px-3 py-4">
    <div class="auth-card card mx-auto">
        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <img src="/favicon.svg" width="56" height="56" alt="SubShield" class="mb-2">
                <h4 class="fw-bold mb-1">@yield('heading', 'SubShield')</h4>
                <p class="text-muted small mb-0">@yield('subheading')</p>
            </div>

            @if(session('status'))
                <div class="alert alert-success py-2 small mb-3">{{ session('status') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger py-2 small mb-3">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            @yield('content')
        </div>
    </div>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>

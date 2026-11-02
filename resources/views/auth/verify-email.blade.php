<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email — SubShield</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
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
            box-shadow: 0 20px 60px rgba(0,0,0,.3);
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
        .otp-input {
            font-size: 2rem;
            letter-spacing: 0.6rem;
            text-align: center;
            font-weight: 700;
            border-radius: 10px;
        }
    </style>
</head>
<body>
<div class="container px-3 py-4">
    <div class="auth-card card mx-auto">
        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <img src="/favicon.svg" width="56" height="56" alt="SubShield" class="mb-2">
                <h4 class="fw-bold mb-1">Verify Your Email</h4>
                <p class="text-muted small mb-0">
                    We sent a 6-digit verification code to<br>
                    <span class="text-dark fw-semibold">{{ auth()->user()->email }}</span>
                </p>
            </div>

            @if(session('status') == 'verification-link-sent')
                <div class="alert alert-success py-2 small mb-3 text-center">
                    <i class="bi bi-check-circle me-1"></i>A fresh 6-digit code has been sent to your email address.
                </div>
            @endif

            @if($errors->has('otp'))
                <div class="alert alert-danger py-2 small mb-3 text-center">
                    <i class="bi bi-exclamation-triangle me-1"></i>{{ $errors->first('otp') }}
                </div>
            @endif

            <form method="POST" action="{{ route('verification.otp') }}" class="mb-4">
                @csrf

                <div class="mb-3">
                    <label for="otp" class="form-label small fw-semibold text-muted text-uppercase text-center d-block">
                        Enter 6-Digit Code
                    </label>
                    <input type="text"
                           name="otp"
                           id="otp"
                           maxlength="6"
                           pattern="[0-9]{6}"
                           inputmode="numeric"
                           class="form-control form-control-lg otp-input @error('otp') is-invalid @enderror"
                           placeholder="······"
                           autocomplete="one-time-code"
                           required>
                    <div class="form-text text-center small text-muted mt-2">
                        <i class="bi bi-clock-history me-1"></i>Code expires in 10 minutes
                    </div>
                </div>

                <button type="submit" class="btn btn-indigo btn-lg w-100 py-2 fw-semibold">
                    <i class="bi bi-shield-check me-2"></i>Verify Account
                </button>
            </form>

            <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" class="btn btn-link text-decoration-none p-0 small text-primary">
                        <i class="bi bi-arrow-clockwise me-1"></i>Resend Code
                    </button>
                </form>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-link text-decoration-none p-0 small text-danger">
                        <i class="bi bi-box-arrow-right me-1"></i>Log Out
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

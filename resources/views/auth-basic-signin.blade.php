<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="{{ asset('assets/images/favicon-32x32.png') }}" type="image/png" />
    <link href="{{ asset('assets/plugins/simplebar/css/simplebar.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/plugins/perfect-scrollbar/css/perfect-scrollbar.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/plugins/metismenu/css/metisMenu.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/pace.min.css') }}" rel="stylesheet" />
    <script src="{{ asset('assets/js/pace.min.js') }}"></script>
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/bootstrap-extended.css') }}" rel="stylesheet">

    <link href="{{ asset('assets/css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/icons.css') }}" rel="stylesheet">
    <title>Technofra Renewal Master</title>
    <style>
        :root {
            --auth-orange-top: #ea7d26;
            --auth-orange-bottom: #ff9f17;
            --auth-bg: #eaf4ff;
            --auth-panel: rgba(233, 245, 255, 0.96);
            --auth-panel-border: rgba(81, 137, 194, 0.18);
            --auth-field: #f8fbff;
            --auth-field-border: rgba(97, 149, 203, 0.28);
            --auth-field-focus: #4f8fd6;
            --auth-text: #15324d;
            --auth-muted: #67819d;
        }

        * {
            font-family: system-ui;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background:
                radial-gradient(circle at 20% 20%, rgba(240, 138, 43, 0.08), transparent 28%),
                radial-gradient(circle at 80% 0%, rgba(255, 255, 255, 0.04), transparent 20%),
                linear-gradient(90deg, #eef6ff 0%, var(--auth-bg) 100%);
            color: var(--auth-text);
        }

        .auth-shell {
            min-height: 100vh;
            display: grid;
            grid-template-columns: minmax(320px, 42%) 1fr;
        }
.auth-story {
    position: relative;
    overflow: hidden;
    background: linear-gradient(
34deg, #00d2ff 0%, #003366 100%);
    padding: 42px 48px 54px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    color: #fff8ef;
}
        .auth-story::before,
        .auth-story::after {
            content: '';
            position: absolute;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.08);
            filter: blur(2px);
        }

        .auth-story::before {
            width: 320px;
            height: 320px;
            top: -130px;
            right: -120px;
        }

        .auth-story::after {
            width: 220px;
            height: 220px;
            bottom: -80px;
            left: -70px;
        }

        .brand-lockup {
            position: relative;
            z-index: 1;
            display: inline-flex;
            align-items: center;
            gap: 14px;
        }

        .brand-mark {
            width: 60px;
            height: 38px;
            border: 4px solid #fff;
            border-radius: 9px;
            position: relative;
        }

        .brand-mark::before,
        .brand-mark::after {
            content: '';
            position: absolute;
            background: #fff;
            border-radius: 999px;
        }

        .brand-mark::before {
            width: 20px;
            height: 4px;
            top: 8px;
            left: 8px;
            box-shadow: 0 10px 0 #fff, 24px 5px 0 #fff;
        }

        .brand-mark::after {
            width: 4px;
            height: 22px;
            right: 10px;
            top: 6px;
        }

        .brand-title {
            font-size: 1.95rem;
            font-weight: 700;
            letter-spacing: -0.03em;
            margin: 0;
        }

        .brand-subtitle {
            font-size: 1.95rem;
            font-weight: 300;
            margin: 0 0 0 -4px;
        }

        .story-copy {
            position: relative;
            z-index: 1;
            max-width: 520px;
            margin-top: auto;
            padding-top: 40px;
        }

        .story-kicker {
            font-size: 3rem;
            line-height: 1;
            font-weight: 700;
            margin-bottom: 26px;
            letter-spacing: -0.04em;
        }

        .story-date {
            display: inline-block;
            font-size: 0.95rem;
            opacity: 0.85;
            margin-bottom: 8px;
        }

        .story-headline {
            font-size: clamp(2.2rem, 3vw, 3.35rem);
            line-height: 1.04;
            font-weight: 700;
            letter-spacing: -0.05em;
            margin-bottom: 18px;
        }

        .story-text {
            font-size: 1.22rem;
            line-height: 1.7;
            max-width: 490px;
            color: rgba(255, 248, 239, 0.9);
        }

        .story-dots {
            display: flex;
            gap: 12px;
            margin-top: 34px;
        }

        .story-dots span {
            width: 13px;
            height: 13px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.34);
        }

        .story-dots span:first-child {
            background: #fff;
        }

        .auth-main {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 42px 24px;
            position: relative;
        }

       .auth-card {
    width: min(100%, 430px);
    background: #000000;
    border: 1px solid var(--auth-panel-border);
    border-radius: 18px;
    box-shadow: 0 30px 80px rgba(0, 0, 0, 0.45);
    backdrop-filter: blur(14px);
    overflow: hidden;
}
.btn:hover{
    background-color: #036;
    color: #fff;
}

        .auth-card-inner {
            padding: 34px 40px 28px;
        }

        .welcome-text {
            text-align: center;
            color: #eef6ff;
            font-size: 1rem;
            margin-bottom: 10px;
        }

        .auth-title {
            text-align: center;
            font-size: 3rem;
            line-height: 1.05;
            font-weight: 700;
            letter-spacing: -0.05em;
            margin-bottom: 30px;
            color: #12314a;
        }

        .form-label,
        .form-check-label {
             color: #949ea7;
            font-weight: 400;
        }
        

        .form-control,
        .input-group-text {
            background: var(--auth-field);
            border-color: var(--auth-field-border);
            color: #173a57;
            min-height: 56px;
            border-radius: 10px;
        }

        .form-control::placeholder {
            color: #7b91a8;
        }

        .form-control:focus {
            background: var(--auth-field);
            color: #17324a;
            border-color: var(--auth-field-focus);
            box-shadow: 0 0 0 0.2rem rgba(240, 138, 43, 0.14);
        }

        .input-group .form-control {
            border-right: 0;
        }

        .input-group-text {
            border-left: 0;
            color: #6c88a6;
        }

        .form-check-input {
            background-color: transparent;
            border-color: rgba(255, 255, 255, 0.25);
        }

        .form-check-input:checked {
            background-color: var(--auth-field-focus);
            border-color: var(--auth-field-focus);
        }

        .auth-link {
            color: var(--auth-field-focus);
            text-decoration: none;
            font-weight: 500;
        }

        .auth-link:hover {
            color: #FFFF;
        }

        .auth-submit {
            min-height: 56px;
            border: 0;
            border-radius: 10px;
            background-color: #036;
            color: #fff;
            font-weight: 600;
            font-size: 1.1rem;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .auth-footer {
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            padding: 16px 22px 18px;
            text-align: center;
            color: var(--auth-muted);
            font-size: 0.95rem;
        }

        .alert {
            border-radius: 12px;
        }

        .invalid-feedback,
        .text-danger {
            color: #cf4d4d !important;
        }

        @media (max-width: 991.98px) {
            .auth-shell {
                grid-template-columns: 1fr;
            }

            .auth-story {
                min-height: 360px;
                padding: 30px 24px 34px;
            }

            .story-copy {
                display: none;
            }

            .auth-story {
                min-height: auto;
                justify-content: center;
                display: none;
            }

            .story-kicker {
                font-size: 2.2rem;
            }

            .story-headline {
                font-size: 2.3rem;
            }

            .auth-main {
                padding: 24px 16px 36px;
                background: linear-gradient(34deg, #00d2ff 0%, #003366 100%);
            }

            .auth-card-inner {
                padding: 28px 22px 22px;
            }

            .auth-title {
                font-size: 2.35rem;
            }
        }
    </style>
</head>
<body>
    <div class="auth-shell">
        <aside class="auth-story">
            <div class="brand-lockup">
                 <div>
                    @if(!empty($globalSettings['crm_logo']) && Storage::exists('public/settings/' . $globalSettings['crm_logo']))
                        <img src="{{ Storage::url('public/settings/' . $globalSettings['crm_logo']) }}" class="logo-icon" alt="logo">
                    @else
                        <img src="{{ asset('assets/images/logo-icon.png') }}" class="logo-icon" alt="logo icon">
                    @endif
                </div>
                <div>
                    <p class="brand-title mb-0">{{ $globalSettings['company_name'] ?? 'Technofra' }}</p>
                    {{-- <p class="brand-subtitle">Portal</p> --}}
                </div>
            </div>

            <div class="story-copy">
                <div class="story-headline">Work smarter with the new CRM experience.</div>
                <div class="story-text">
                    Manage renewals, client records, tasks, and project communication from one focused workspace. Your existing login flow stays the same, only the experience is cleaner and sharper.
                </div>
                <div class="story-dots">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        </aside>

        <main class="auth-main">
            <div class="auth-card">
                <div class="auth-card-inner">
                    <div class="text-center mb-3">
                        @if(!empty($globalSettings['crm_logo']) && Storage::exists('public/settings/' . $globalSettings['crm_logo']))
                            <img src="{{ Storage::url('public/settings/' . $globalSettings['crm_logo']) }}" class="mb-3" alt="logo" style="max-height: 54px; width: auto;">
                        @else
                            <img src="{{ asset('assets/images/logo-icon.png') }}" class="mb-3" alt="logo icon" style="max-height: 54px; width: auto;">
                        @endif
                    </div>

                    <div class="welcome-text">Welcome Back</div>

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form class="row g-3" method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="col-12">
                            <label for="inputEmailAddress" class="form-label">Email Address</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="inputEmailAddress" name="email" value="{{ old('email') }}" placeholder="Enter email" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <label for="inputChoosePassword" class="form-label mb-0">Password</label>
                                <a class="auth-link small" href="{{ route('password.request') }}">Forgot?</a>
                            </div>
                            <div class="input-group" id="show_hide_password">
                                <input type="password" class="form-control @error('password') is-invalid @enderror" id="inputChoosePassword" name="password" placeholder="Password" required>
                                <a href="javascript:;" class="input-group-text bg-transparent">
                                    <i class='bx bx-hide'></i>
                                </a>
                            </div>
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="flexSwitchCheckChecked" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label" for="flexSwitchCheckChecked">Remember Me</label>
                            </div>
                        </div>

                        <div class="col-12 pt-1">
                            <div class="d-grid">
                                <button type="submit" class="btn auth-submit">Login</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="auth-footer">Language: <span class="auth-link">English</span></div>
            </div>
        </main>
    </div>

    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/simplebar/js/simplebar.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/metismenu/js/metisMenu.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js') }}"></script>
    <script>
        $(document).ready(function () {
            $("#show_hide_password a").on('click', function (event) {
                event.preventDefault();
                if ($('#show_hide_password input').attr("type") == "text") {
                    $('#show_hide_password input').attr('type', 'password');
                    $('#show_hide_password i').addClass("bx-hide");
                    $('#show_hide_password i').removeClass("bx-show");
                } else if ($('#show_hide_password input').attr("type") == "password") {
                    $('#show_hide_password input').attr('type', 'text');
                    $('#show_hide_password i').removeClass("bx-hide");
                    $('#show_hide_password i').addClass("bx-show");
                }
            });
        });
    </script>
    <script src="{{ asset('assets/js/app.js') }}"></script>
</body>
</html>






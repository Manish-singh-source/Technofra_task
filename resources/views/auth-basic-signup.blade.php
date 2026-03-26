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
            --auth-green: #20c997;
            --auth-green-dark: #16b886;
            --auth-bg: #f5f7fb;
            --auth-text: #233142;
            --auth-muted: #7f8a99;
            --auth-border: #d7e3dc;
            --auth-shadow: 0 24px 55px rgba(31, 56, 76, 0.14);
        }

        * {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background:
                linear-gradient(135deg, rgba(32, 201, 151, 0.05), transparent 30%),
                linear-gradient(315deg, rgba(32, 201, 151, 0.08), transparent 26%),
                var(--auth-bg);
            color: var(--auth-text);
        }

        .auth-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 36px 20px;
        }

        .auth-card {
            width: min(1120px, 100%);
            min-height: 700px;
            display: grid;
            grid-template-columns: 1.1fr 1.55fr;
            background: #fff;
            border-radius: 28px;
            overflow: hidden;
            box-shadow: var(--auth-shadow);
        }

        .auth-side {
            position: relative;
            overflow: hidden;
            background: linear-gradient(180deg, #22cfa0 0%, #1ac897 100%);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 48px 34px;
            text-align: center;
        }

        .auth-side::before,
        .auth-side::after {
            content: "";
            position: absolute;
            background: rgba(255, 255, 255, 0.07);
            pointer-events: none;
        }

        .auth-side::before {
            width: 220px;
            height: 220px;
            border-radius: 50%;
            left: -70px;
            bottom: -34px;
        }

        .auth-side::after {
            width: 0;
            height: 0;
            border-left: 34px solid transparent;
            border-right: 34px solid transparent;
            border-bottom: 60px solid rgba(255, 255, 255, 0.06);
            top: 72px;
            right: 52px;
            transform: rotate(-18deg);
            background: transparent;
        }

        .auth-side-shape {
            position: absolute;
            width: 0;
            height: 0;
            border-left: 42px solid transparent;
            border-right: 42px solid transparent;
            border-bottom: 74px solid rgba(255, 255, 255, 0.05);
            top: 64px;
            left: 34px;
            transform: rotate(-26deg);
        }

        .auth-side-content {
            position: relative;
            z-index: 1;
            max-width: 320px;
        }

        .auth-side-title {
            margin: 0 0 14px;
            font-size: clamp(2.1rem, 4vw, 3rem);
            line-height: 1.08;
            font-weight: 700;
            letter-spacing: 0.03em;
        }

        .auth-side-divider {
            width: 52px;
            height: 4px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.92);
            margin: 0 auto 20px;
        }

        .auth-side-text {
            font-size: 1.12rem;
            line-height: 1.7;
            color: rgba(255, 255, 255, 0.88);
            margin-bottom: 30px;
        }

        .auth-side-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 180px;
            min-height: 52px;
            padding: 0 28px;
            border-radius: 999px;
            border: 2px solid rgba(255, 255, 255, 0.82);
            color: #fff;
            font-weight: 700;
            text-decoration: none;
            transition: background-color 0.2s ease, color 0.2s ease;
        }

        .auth-side-button:hover {
            background: #fff;
            color: var(--auth-green-dark);
        }

        .auth-form-panel {
            padding: 42px 56px 28px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .auth-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.35rem;
            font-weight: 700;
            color: #1b2a39;
        }

        .auth-brand-accent {
            color: var(--auth-green);
        }

        .auth-brand img {
            max-height: 42px;
            width: auto;
        }

        .auth-form-wrap {
            width: min(100%, 430px);
            margin: 0 auto;
            text-align: center;
        }

        .auth-title {
            margin: 18px 0 10px;
            font-size: clamp(2.1rem, 4vw, 2.8rem);
            font-weight: 700;
            letter-spacing: 0.04em;
            color: var(--auth-green);
        }

        .auth-divider {
            width: 48px;
            height: 4px;
            border-radius: 999px;
            background: var(--auth-green);
            margin: 0 auto 20px;
        }

        .auth-socials {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 14px;
            margin-bottom: 18px;
        }

        .auth-social {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            border: 1px solid #ebeff3;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #22303f;
            text-decoration: none;
            background: #fff;
            box-shadow: 0 6px 18px rgba(28, 42, 56, 0.06);
        }

        .auth-subtitle {
            color: var(--auth-muted);
            font-size: 0.98rem;
            margin-bottom: 24px;
        }

        .auth-alert {
            text-align: left;
            border-radius: 14px;
            margin-bottom: 18px;
        }

        .form-group {
            text-align: left;
            margin-bottom: 16px;
        }

        .form-label {
            font-size: 0.93rem;
            font-weight: 600;
            color: #4f6577;
            margin-bottom: 8px;
        }

        .form-control,
        .input-group-text {
            min-height: 54px;
            border-radius: 12px;
            border: 1px solid var(--auth-border);
            background: #fff;
        }

        .form-control {
            padding-inline: 16px;
            color: var(--auth-text);
        }

        .form-control:focus {
            border-color: var(--auth-green);
            box-shadow: 0 0 0 0.18rem rgba(32, 201, 151, 0.15);
        }

        .input-group .form-control {
            border-right: 0;
        }

        .input-group-text {
            background: #fff;
            border-left: 0;
            color: #7b8794;
            padding-inline: 16px;
        }

        .auth-check {
            text-align: left;
            margin: 8px 0 24px;
        }

        .form-check {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin: 0;
        }

        .form-check-input {
            margin: 3px 0 0;
            width: 18px;
            height: 18px;
            border-color: #d8dfe6;
            flex: 0 0 auto;
        }

        .form-check-input:checked {
            background-color: var(--auth-green);
            border-color: var(--auth-green);
        }

        .form-check-label,
        .auth-link {
            color: #384b5d;
            font-size: 0.96rem;
        }

        .auth-link {
            text-decoration: none;
        }

        .auth-link:hover {
            color: var(--auth-green-dark);
        }

        .auth-submit {
            min-height: 54px;
            min-width: 170px;
            border: 0;
            border-radius: 999px;
            padding: 0 28px;
            font-weight: 700;
            font-size: 1rem;
            color: #fff;
            background: linear-gradient(180deg, #24d6a1 0%, var(--auth-green-dark) 100%);
            box-shadow: 0 14px 26px rgba(32, 201, 151, 0.28);
        }

        .auth-submit:hover,
        .auth-submit:focus {
            color: #fff;
            background: linear-gradient(180deg, #22c596 0%, #11a676 100%);
        }

        .auth-login-copy {
            color: var(--auth-muted);
            font-size: 0.95rem;
            margin-top: 18px;
        }

        .auth-footer {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 16px;
            color: var(--auth-muted);
            font-size: 0.87rem;
            margin-top: 28px;
        }

        .auth-footer span {
            color: #c8d1db;
        }

        .invalid-feedback,
        .text-danger {
            color: #d04b4b !important;
        }

        @media (max-width: 991.98px) {
            .auth-card {
                grid-template-columns: 1fr;
            }

            .auth-side {
                min-height: 280px;
                padding: 34px 22px;
            }

            .auth-form-panel {
                padding: 28px 22px 24px;
            }

            .auth-form-wrap {
                width: 100%;
            }
        }

        @media (max-width: 575.98px) {
            .auth-page {
                padding: 14px;
            }

            .auth-card {
                border-radius: 22px;
            }

            .auth-title {
                font-size: 1.85rem;
            }

            .auth-footer {
                flex-wrap: wrap;
                gap: 8px;
            }
        }
    </style>
</head>
<body>
    @php($crmLogoUrl = \App\Models\Setting::resolveGeneralAssetUrl($globalSettings['crm_logo'] ?? ''))
    @php($companyName = trim($globalSettings['company_name'] ?? 'Technofra Name'))
    @php($companyParts = preg_split('/\s+/', $companyName, 2))
    @php($companyFirst = $companyParts[0] ?? 'Technofra')
    @php($companyRest = $companyParts[1] ?? 'Name')

    <div class="auth-page">
        <div class="auth-card">
            <aside class="auth-side">
                <span class="auth-side-shape" aria-hidden="true"></span>
                <div class="auth-side-content">
                    <h2 class="auth-side-title">Welcome Back!</h2>
                    <div class="auth-side-divider"></div>
                    <p class="auth-side-text">To keep connected with us please login with your personal info.</p>
                    <a href="{{ route('login') }}" class="auth-side-button">Sign In</a>
                </div>
            </aside>

            <section class="auth-form-panel">
                <div>
                    <div class="auth-brand">
                        @if($crmLogoUrl)
                            <img src="{{ $crmLogoUrl }}" alt="logo">
                        @endif
                        <div>
                            <span class="auth-brand-accent">{{ $companyFirst }}</span>
                            <span>{{ $companyRest }}</span>
                        </div>
                    </div>

                    <div class="auth-form-wrap">
                        <h1 class="auth-title">Create Account</h1>
                        <div class="auth-divider"></div>

                        <div class="auth-socials" aria-hidden="true">
                            <a href="javascript:;" class="auth-social"><i class='bx bxl-facebook'></i></a>
                            <a href="javascript:;" class="auth-social"><i class='bx bxl-linkedin'></i></a>
                            <a href="javascript:;" class="auth-social"><i class='bx bxl-google'></i></a>
                        </div>

                        <p class="auth-subtitle">or use your email for registration</p>

                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show auth-alert" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show auth-alert" role="alert">
                                <ul class="mb-0 ps-3">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('register') }}">
                            @csrf

                            <div class="form-group">
                                <label for="inputUsername" class="form-label">Name</label>
                                <input
                                    type="text"
                                    class="form-control @error('name') is-invalid @enderror"
                                    id="inputUsername"
                                    name="name"
                                    value="{{ old('name') }}"
                                    placeholder="Enter your full name"
                                    required
                                >
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="inputEmailAddress" class="form-label">Email</label>
                                <input
                                    type="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    id="inputEmailAddress"
                                    name="email"
                                    value="{{ old('email') }}"
                                    placeholder="Enter your email"
                                    required
                                >
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="inputChoosePassword" class="form-label">Password</label>
                                <div class="input-group" id="show_hide_password">
                                    <input
                                        type="password"
                                        class="form-control @error('password') is-invalid @enderror"
                                        id="inputChoosePassword"
                                        name="password"
                                        placeholder="Create a password"
                                        required
                                    >
                                    <a href="javascript:;" class="input-group-text">
                                        <i class='bx bx-hide'></i>
                                    </a>
                                </div>
                                @error('password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="inputConfirmPassword" class="form-label">Confirm Password</label>
                                <input
                                    type="password"
                                    class="form-control @error('password_confirmation') is-invalid @enderror"
                                    id="inputConfirmPassword"
                                    name="password_confirmation"
                                    placeholder="Confirm your password"
                                    required
                                >
                                @error('password_confirmation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="auth-check">
                                <div class="form-check">
                                    <input
                                        class="form-check-input @error('terms') is-invalid @enderror"
                                        type="checkbox"
                                        id="termsCheck"
                                        name="terms"
                                        value="1"
                                        {{ old('terms') ? 'checked' : '' }}
                                        required
                                    >
                                    <label class="form-check-label" for="termsCheck">
                                        I read and agree to <a href="javascript:;" class="auth-link">Terms &amp; Conditions</a>
                                    </label>
                                </div>
                                @error('terms')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" class="btn auth-submit">Sign Up</button>
                        </form>

                        <p class="auth-login-copy">Already have an account? <a href="{{ route('login') }}" class="auth-link">Sign in here</a></p>
                    </div>
                </div>

                <div class="auth-footer">
                    <a href="javascript:;" class="auth-link">Privacy Policy</a>
                    <span>&bull;</span>
                    <a href="javascript:;" class="auth-link">Terms &amp; Conditions</a>
                </div>
            </section>
        </div>
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

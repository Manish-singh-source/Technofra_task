<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Page not found · {{ config('app.name', 'MYCRM') }}</title>
    <style>
        :root{--ink:#172b4d;--muted:#65758b;--blue:#176ee2;--line:#e5edf8}
        *{box-sizing:border-box} body{margin:0;min-height:100vh;font-family:Inter,Segoe UI,Arial,sans-serif;color:var(--ink);background:linear-gradient(135deg,#f5f9ff 0%,#eef4ff 50%,#f9fbff 100%)}
        .error-shell{position:relative;display:grid;min-height:100vh;place-items:center;overflow:hidden;padding:28px}.orb{position:absolute;border-radius:50%;filter:blur(2px);opacity:.58}.orb-one{width:360px;height:360px;top:-125px;left:-115px;background:#cfe2ff}.orb-two{width:440px;height:440px;right:-170px;bottom:-185px;background:#dcd0ff}
        .error-card{position:relative;width:min(700px,100%);padding:clamp(30px,6vw,62px);border:1px solid rgba(255,255,255,.85);border-radius:28px;text-align:center;background:rgba(255,255,255,.88);box-shadow:0 22px 70px rgba(37,76,132,.16);backdrop-filter:blur(10px)}
        .code{margin:0;color:#176ee2;font-size:clamp(4.5rem,15vw,8.5rem);font-weight:800;letter-spacing:-.08em;line-height:.86}.title{margin:20px 0 10px;font-size:clamp(1.55rem,4vw,2.2rem);letter-spacing:-.03em}.copy{max-width:470px;margin:0 auto;color:var(--muted);font-size:1rem;line-height:1.7}.path{display:inline-block;max-width:100%;overflow:hidden;margin:22px auto 0;padding:9px 13px;border:1px solid var(--line);border-radius:9px;color:#708198;background:#f8faff;font-family:Consolas,monospace;font-size:.76rem;text-overflow:ellipsis;white-space:nowrap}
        .actions{display:flex;flex-wrap:wrap;justify-content:center;gap:11px;margin-top:30px}.btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:44px;padding:0 18px;border:1px solid transparent;border-radius:10px;font:700 .9rem inherit;text-decoration:none;cursor:pointer;transition:transform .18s ease,box-shadow .18s ease}.btn:hover{transform:translateY(-2px)}.btn-primary{color:#fff;background:var(--blue);box-shadow:0 8px 18px rgba(23,110,226,.25)}.btn-light{border-color:#dce7f5;color:#435671;background:#fff}.help{margin:28px 0 0;color:#8190a5;font-size:.82rem}.help a{color:#176ee2;text-decoration:none;font-weight:700}@media(max-width:480px){.error-shell{padding:16px}.error-card{border-radius:20px}.actions{align-items:stretch;flex-direction:column}.btn{width:100%}}
    </style>
</head>
<body>
<main class="error-shell">
    <span class="orb orb-one"></span><span class="orb orb-two"></span>
    <section class="error-card" aria-labelledby="error-title">
        <p class="code">404</p>
        <h1 class="title" id="error-title">This page is not available</h1>
        <p class="copy">The link may be incorrect, expired, or the page may have moved. You can safely return to your workspace.</p>
        <div class="path" id="requestedPath"></div>
        <div class="actions">
            @auth
                <a class="btn btn-primary" href="{{ route('dashboard') }}">Go to dashboard <span>→</span></a>
            @else
                <a class="btn btn-primary" href="{{ route('login') }}">Go to sign in <span>→</span></a>
            @endauth
            <button class="btn btn-light" type="button" onclick="window.history.length > 1 ? window.history.back() : window.location.assign('/')">← Go back</button>
        </div>
        <p class="help">Need help? Please contact your CRM administrator.</p>
    </section>
</main>
<script>document.getElementById('requestedPath').textContent = window.location.pathname;</script>
</body>
</html>

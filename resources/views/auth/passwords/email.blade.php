<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="/css/master.css">
    <link rel="icon" href="{{ asset('images/' . ($app_settings->favicon ?? 'favicon.ico')) }}">
    <title>{{ $app_settings->app_name ?? 'InFlow | Ultimate Inventory With POS' }}</title>

    <style>
      :root {
        color-scheme: light;
        font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        --surface: #ffffff;
        --primary: #2d8cff;
        --primary-dark: #312fab;
        --primary-soft: rgba(76,68,236,0.12);
        --text: #1f2937;
        --text-muted: #6b7280;
        --border: #e5e7eb;
        --danger: #ef4444;
        --danger-soft: rgba(239,68,68,0.10);
        --danger-border: rgba(239,68,68,0.35);
        --success: #16a34a;
        --success-soft: rgba(22,163,74,0.10);
        --success-border: rgba(22,163,74,0.35);
      }

      *, *::before, *::after { box-sizing: border-box; }
      body {
        margin: 0;
        background: rgb(45, 140, 255, 0.08);
        color: var(--text);
        overflow-x: hidden;
      }

      /* MAIN GRID */
      .auth-page {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        min-height: 100dvh;
      }

      /* HERO SIDE */
      .auth-hero {
        background: #2d8cff;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        padding: 80px clamp(40px, 8vw, 100px);
      }

      .hero-content {
        max-width: 440px;
        display: grid;
        gap: 1rem;
        text-align: left;
      }

      .hero-title {
        font-size: clamp(1.8rem, 4vw, 2.8rem);
        font-weight: 700;
        margin: 0;
      }

      .hero-subtitle {
        font-size: clamp(0.9rem, 2vw, 1rem);
        color: rgba(255,255,255,0.85);
        line-height: 1.6;
      }

      /* PANEL SIDE */
      .auth-panel {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: clamp(2rem, 6vw, 5rem);
      }

      .auth-panel-inner {
        background: var(--surface);
        border-radius: 24px;
        width: 100%;
        max-width: 480px;
        padding: clamp(1.5rem, 4vw, 3rem);
        box-shadow: 0 18px 36px -12px rgba(0,0,0,0.1);
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
      }

      .panel-title {
        font-size: clamp(1.5rem, 5vw, 1.9rem);
        margin: 0;
      }

      .panel-subtitle {
        font-size: clamp(0.85rem, 3vw, 0.95rem);
        color: var(--text-muted);
        line-height: 1.6;
        margin: 0;
      }

      /* FORM FIELDS */
      form { display: grid; gap: 1rem; }
      .field { display: grid; gap: 0.5rem; }

      .input-shell {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 0 1rem;
        background: #f9fafb;
      }

      .input-shell input {
        flex: 1;
        border: none;
        background: transparent;
        padding: 0.8rem 0;
        font-size: 1rem;
      }

      .input-shell input:focus {
        outline: none;
      }

      .auth-btn {
        padding: 0.9rem;
        border-radius: 12px;
        border: none;
        background: #2d8cff;
        color: #fff;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
      }

      .auth-btn:hover { filter: brightness(1.05); }

      .auth-link {
        color: var(--primary);
        text-decoration: none;
        font-weight: 600;
        font-size: 0.85rem;
        text-align: center;
      }

      .auth-link:hover { text-decoration: underline; }

      /* RESPONSIVE */
      @media (max-width: 1024px) {
        .auth-page {
          grid-template-columns: 1fr;
        }
        .auth-hero { display: none; }
      }

      @media (max-width: 768px) {
        .auth-panel { padding: 2rem; }
        .auth-panel-inner { padding: 1.5rem; border-radius: 20px; }
      }

      @media (max-width: 480px) {
        body { background: #f9f9ff; }
        .auth-panel { padding: 1.25rem; }
        .auth-panel-inner {
          max-width: 100%;
          border-radius: 18px;
          padding: 1.25rem;
          gap: 1.2rem;
        }
        .panel-title { font-size: 1.4rem; }
        .panel-subtitle { font-size: 0.8rem; }
        .input-shell input { font-size: 0.9rem; padding: 0.75rem 0; }
        .auth-btn { font-size: 0.95rem; padding: 0.8rem; }
      }

      /* Alerts */
      .auth-alert{
        padding: 0.875rem 1rem;
        border-radius: 12px;
        border: 1px solid var(--border);
        font-size: 0.95rem;
        line-height: 1.5;
        background: #fff;
        margin: 0.75rem 0;
      }
      .auth-alert ul{ margin: 0; padding-left: 1.1rem; }
      .auth-alert.error{
        background: var(--danger-soft);
        border-color: var(--danger-border);
        color: #991b1b;
      }
      .auth-alert.success{
        background: var(--success-soft);
        border-color: var(--success-border);
        color: #065f46;
      }
    </style>
  </head>
  <body>
    <div class="auth-page">
      <section class="auth-hero">
        <div class="hero-content">
          <h1 class="hero-title">{{ $app_settings->login_hero_title ?? 'Welcome back!' }}</h1>
          <p class="hero-subtitle">
            {{ $app_settings->login_hero_subtitle ?? 'Sign in to access your account and keep your operations in sync.' }}
          </p>
        </div>
      </section>

      <section class="auth-panel">
        <div class="auth-panel-inner">
          <header>
            <h2 class="panel-title">{{ __('Forgot your password?') }}</h2>
            <p class="panel-subtitle">
              {{ __('Enter your email to receive a reset link.') }}
            </p>
          </header>

          @if (session('status'))
          <div class="auth-alert success">{{ session('status') }}</div>
          @endif

          @if ($errors->any())
          <div class="auth-alert error">
            <ul>
              @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
          @endif

          <form method="POST" action="{{ route('password.email') }}" novalidate>
            @csrf
            <div class="field">
              <label for="email">{{ __('E-Mail Address') }}</label>
              <div class="input-shell">
                <span class="input-addon">@</span>
                <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="you@company.com" required autofocus />
              </div>
            </div>

            <button type="submit" class="auth-btn">
              {{ __('Send Password Reset Link') }}
            </button>
          </form>

          <a class="auth-link" href="{{ route('login') }}">{{ __('Back to login') }}</a>
        </div>
      </section>
    </div>
  </body>
</html>
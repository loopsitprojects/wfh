<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="icon" type="image/png" href="{{ asset('loops-icon.png') }}">
<title>Login — WFH Pulse Tracker</title>
@vite(['resources/css/app.css'])
</head>
<body>
<div class="login-page">
  <div class="login-card">
    <div class="login-logo">
      <img src="{{ asset('LoopsWhite.png') }}" alt="Loops Logo" style="height:60px;width:auto;margin-bottom:20px">
      <div class="login-title">WFH Pulse Tracker</div>
      <div class="login-sub">Sign in to your workspace</div>
    </div>

    @if($errors->any())
      <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}">
      @csrf
      <div class="form-group">
        <label>Username</label>
        <input type="text" name="username" class="form-control"
          placeholder="your_username" value="{{ old('username') }}" required autofocus>
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
      </div>
      <div class="form-group" style="display:flex;align-items:center;gap:8px;margin-bottom:28px">
        <input type="checkbox" name="remember" id="remember" style="accent-color:var(--primary)">
        <label for="remember" style="text-transform:none;font-size:13px;color:var(--muted);margin:0">Remember me</label>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:13px">
        Sign In
      </button>
    </form>
  </div>
</div>
</body>
</html>

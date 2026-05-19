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
        <div style="position:relative">
          <input type="password" id="password_input" name="password" class="form-control" placeholder="••••••••" required style="padding-right:40px">
          <button type="button" onclick="togglePassword()" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--muted);cursor:pointer;padding:4px;display:flex;align-items:center" tabindex="-1">
            <svg id="eye-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
            <svg id="eye-off-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
          </button>
        </div>
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
<script>
function togglePassword() {
  const input = document.getElementById('password_input');
  const eye = document.getElementById('eye-icon');
  const eyeOff = document.getElementById('eye-off-icon');
  
  if (input.type === 'password') {
    input.type = 'text';
    eye.style.display = 'none';
    eyeOff.style.display = 'block';
  } else {
    input.type = 'password';
    eye.style.display = 'block';
    eyeOff.style.display = 'none';
  }
}
</script>
</body>
</html>

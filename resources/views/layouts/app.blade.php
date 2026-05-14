<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="theme-color" content="#4F7EFF">
<link rel="manifest" href="/manifest.json">
<link rel="icon" type="image/png" href="/loops-icon.png">
<link rel="apple-touch-icon" href="/icon-512.png">
<title>@yield('title','WFH Pulse') — WFH Tracker</title>
@vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body>
<div class="app-layout">

  <div class="sidebar-overlay" id="sidebar-overlay" onclick="toggleSidebar()"></div>

  {{-- ── Sidebar ──────────────────────────────── --}}
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
      <img src="/LoopsWhite.png" alt="Loops Logo" style="height:36px;width:auto">
    </div>

    <nav class="sidebar-nav">
      @php $role = auth()->user()->role; @endphp

      @if($role === 'employee')
        <div class="nav-section-label">Workspace</div>
        <a href="{{ route('employee.dashboard') }}" class="nav-item {{ request()->routeIs('employee.dashboard') ? 'active' : '' }}">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
          Dashboard
        </a>
        <a href="{{ route('employee.history') }}" class="nav-item {{ request()->routeIs('employee.history') ? 'active' : '' }}">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
          My History
        </a>
      @endif

      @if($role === 'manager')
        <div class="nav-section-label">Management</div>
        <a href="{{ route('manager.dashboard') }}" class="nav-item {{ request()->routeIs('manager.dashboard') ? 'active' : '' }}">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
          Dashboard
        </a>
        <a href="{{ route('manager.pulses') }}" class="nav-item {{ request()->routeIs('manager.pulses') ? 'active' : '' }}">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
          Pulse Requests
          @php $pending = auth()->user()->managedPulses()->where('status','pending')->count(); @endphp
          @if($pending > 0)<span class="badge badge-danger" style="margin-left:auto">{{ $pending }}</span>@endif
        </a>
        <a href="{{ route('manager.team') }}" class="nav-item {{ request()->routeIs('manager.team') ? 'active' : '' }}">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          My Team
        </a>
        <a href="{{ route('manager.reports') }}" class="nav-item {{ request()->routeIs('manager.reports') ? 'active' : '' }}">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
          Reports
        </a>
      @endif

      @if($role === 'admin')
        <div class="nav-section-label">Admin</div>
        <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
          Admin Dashboard
        </a>
        <a href="{{ route('admin.users') }}" class="nav-item {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          User Management
        </a>
        <a href="{{ route('admin.activity') }}" class="nav-item {{ request()->routeIs('admin.activity') ? 'active' : '' }}">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20V10M18 20V4M6 20v-4"/></svg>
          Activity History
        </a>
      @endif
    </nav>

    <div class="sidebar-footer">
      <div class="sidebar-avatar">{{ strtoupper(substr(auth()->user()->name,0,1)) }}</div>
      <div class="sidebar-user-info">
        <div class="sidebar-user-name">{{ auth()->user()->name }}</div>
        <div class="sidebar-user-role">{{ auth()->user()->role }}</div>
      </div>
      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" title="Logout" style="background:none;border:none;color:var(--muted);cursor:pointer;padding:4px">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        </button>
      </form>
    </div>
  </aside>

  {{-- ── Main ─────────────────────────────────── --}}
  <div class="main-content">
    <header class="topnav">
      <div style="display:flex;align-items:center;gap:12px">
        <button class="mobile-menu-toggle" onclick="toggleSidebar()">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
        </button>
        <span class="topnav-title">@yield('page-title','Dashboard')</span>
      </div>
      <div class="topnav-right">
        <a href="{{ route('notifications.index') }}" class="notif-btn" id="notif-btn" title="Notifications">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
          <span class="notif-badge" id="notif-count" style="display:none">0</span>
        </a>
      </div>
    </header>

    <main class="page-body">
      @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
      @endif
      @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
      @endif
      @if($errors->any())
        <div class="alert alert-danger">
          @foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach
        </div>
      @endif

      @yield('content')
    </main>
  </div>
</div>

<script>
// CSRF for AJAX
window.csrfToken = '{{ csrf_token() }}';

// Notification polling
function pollNotifications() {
  fetch('/notifications/count', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    .then(r => r.json())
    .then(data => {
      const badge = document.getElementById('notif-count');
      if (data.count > 0) { badge.textContent = data.count; badge.style.display = 'flex'; }
      else { badge.style.display = 'none'; }
    }).catch(() => {});
}
pollNotifications();
setInterval(pollNotifications, 30000);

// Mobile Sidebar Toggle
function toggleSidebar() {
  document.getElementById('sidebar').classList.toggle('open');
  document.getElementById('sidebar-overlay').classList.toggle('active');
}

// Alert auto-dismiss
setTimeout(() => { document.querySelectorAll('.alert').forEach(a => a.style.opacity = '0'); }, 5000);

// Register Service Worker for PWA
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/sw.js').catch(() => {});
  });
}
</script>
@stack('scripts')
</body>
</html>

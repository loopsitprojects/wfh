@extends('layouts.app')
@section('title','My Team')
@section('page-title','My Team')

@section('content')
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
  <div class="card-title" style="margin:0">System Users Overview</div>
  <button class="btn btn-primary" onclick="document.getElementById('add-emp-card').style.display='block';this.style.display='none'">+ Add New User</button>
</div>

<div class="card" id="add-emp-card" style="display:{{ $errors->any() ? 'block' : 'none' }};margin-bottom:24px;border:1px solid var(--primary)">
  <div class="card-header">
    <div class="card-title">Quick Add User</div>
    <button class="modal-close" onclick="document.getElementById('add-emp-card').style.display='none';document.querySelector('.btn-primary').style.display='flex'">×</button>
  </div>
  <form method="POST" action="{{ route('manager.employees.store') }}" style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:16px;align-items:flex-end">
    @csrf
    <div class="form-group" style="margin:0">
      <label>Username</label>
      <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" required placeholder="jdoe" value="{{ old('username') }}">
      @error('username')<div style="color:var(--danger);font-size:11px;margin-top:4px">{{ $message }}</div>@enderror
    </div>
    <div class="form-group" style="margin:0">
      <label>Email</label>
      <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" required placeholder="john@company.com" value="{{ old('email') }}">
      @error('email')<div style="color:var(--danger);font-size:11px;margin-top:4px">{{ $message }}</div>@enderror
    </div>
    <div class="form-group" style="margin:0">
      <label>Password</label>
      <div style="position:relative">
        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required placeholder="Min 8 chars" style="padding-right:40px">
        <button type="button" onclick="togglePasswordVisibility(this)" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--muted);cursor:pointer;padding:4px;display:flex;align-items:center" tabindex="-1">
          <svg class="eye-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
          <svg class="eye-off-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
        </button>
      </div>
      @error('password')<div style="color:var(--danger);font-size:11px;margin-top:4px">{{ $message }}</div>@enderror
    </div>
    <div class="form-group" style="margin:0">
      <label>Role</label>
      <select name="role" class="form-control">
        <option value="employee">Employee</option>
        <option value="manager">Manager</option>
      </select>
    </div>

    <div style="display:flex;gap:8px">
      <button type="submit" class="btn btn-primary">Create</button>
      <button type="button" class="btn btn-outline" onclick="document.getElementById('add-emp-card').style.display='none';document.querySelector('.btn-primary').style.display='flex'">Cancel</button>
    </div>

  </form>
</div>

<div class="card" style="margin-bottom:20px">
  <form method="GET" style="display:flex;gap:12px;align-items:flex-end">
    <div class="form-group" style="margin:0;flex:1">
      <label>Search</label>
      <input type="text" name="search" class="form-control" placeholder="Name or email…" value="{{ request('search') }}">
    </div>
    <button type="submit" class="btn btn-primary">Search</button>
    <a href="{{ route('manager.team') }}" class="btn btn-outline">Reset</a>
  </form>
</div>

<div class="card">
  <div class="table-wrap">
    <table>
      <thead><tr><th>User</th><th>Role</th><th>Total Hours</th><th>Sessions</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        @forelse($employees as $emp)
        @php
          $totalSec = $emp->timeLogs->sum('duration_seconds');
          $lastLog  = $emp->timeLogs->sortByDesc('started_at')->first();
        @endphp
        <tr>
          <td>
            <div style="font-weight:600">{{ $emp->name }}</div>
            <div style="font-size:12px;color:var(--muted)">{{ $emp->email }}</div>
          </td>
          <td><span class="badge {{ $emp->role === 'manager' ? 'badge-warning' : 'badge-primary' }}">{{ ucfirst($emp->role) }}</span></td>
          <td>{{ gmdate('H\h i\m', $totalSec) }}</td>
          <td>{{ $emp->timeLogs->count() }}</td>
          <td>
            @if($emp->getActiveTimer())
              <span class="pulse-indicator running" style="margin:0">● Active</span>
            @elseif($emp->is_active)
              <span class="badge badge-muted">Idle</span>
            @else
              <span class="badge badge-danger">Deactivated</span>
            @endif
          </td>
          <td>
            <a href="{{ route('manager.users.edit', $emp->id) }}" class="btn btn-outline btn-sm" style="padding:4px 8px;margin-right:4px" title="Edit User">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            </a>
            <form method="POST" action="{{ route('manager.users.destroy', $emp->id) }}" onsubmit="return confirm('Delete this user from the system?')" style="display:inline">
              @csrf
              @method('DELETE')
              <button class="btn btn-danger btn-sm" style="padding:4px 8px" title="Delete User">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
              </button>
            </form>
          </td>


        </tr>
        @empty
          <tr><td colspan="6"><div class="empty-state"><div class="empty-state-icon">👥</div><h3>No team members yet</h3></div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  {{ $employees->links('vendor.pagination.custom') }}
</div>
<script>
function togglePasswordVisibility(btn) {
  const container = btn.parentElement;
  const input = container.querySelector('input');
  const eye = btn.querySelector('.eye-icon');
  const eyeOff = btn.querySelector('.eye-off-icon');
  
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
@endsection

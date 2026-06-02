@extends('layouts.app')
@section('title', isset($user) ? 'Edit User' : 'Add User')
@section('page-title', isset($user) ? 'Edit User' : 'Add New User')

@section('content')
<div style="max-width:480px;margin:0 auto">
  <div class="card">
    <div class="card-header">
      <div><div class="card-title">User Details</div><div class="card-subtitle">Fill in the information below</div></div>
    </div>
    <form method="POST" action="{{ isset($user) ? route('admin.users.update',$user->id) : (auth()->user()->isManager() ? route('manager.employees.store') : route('admin.users.store')) }}">
      @csrf
      @if(isset($user)) @method('PUT') @endif

      <div class="form-group">
        <label>Username <span style="color:var(--danger)">*</span></label>
        <input type="text" name="username" class="form-control" value="{{ old('username',$user->username??'') }}" required placeholder="e.g. jdoe">
        @error('username')<div class="form-error">{{ $message }}</div>@enderror
      </div>

      <div class="form-group">
        <label>Email <span style="color:var(--danger)">*</span></label>
        <input type="email" name="email" class="form-control" value="{{ old('email',$user->email??'') }}" required placeholder="e.g. john@company.com">
        @error('email')<div class="form-error">{{ $message }}</div>@enderror
      </div>

      <div class="form-group">
        <label>Password {{ isset($user) ? '(leave blank to keep)' : '*' }}</label>
        <div style="position:relative">
          <input type="password" name="password" class="form-control" {{ isset($user) ? '' : 'required' }} placeholder="Minimum 8 characters" style="padding-right:40px">
          <button type="button" onclick="togglePasswordVisibility(this)" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--muted);cursor:pointer;padding:4px;display:flex;align-items:center" tabindex="-1">
            <svg class="eye-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
            <svg class="eye-off-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
          </button>
        </div>
        @error('password')<div class="form-error">{{ $message }}</div>@enderror
      </div>

      {{-- Role Selection --}}
      @if(auth()->user()->isAdmin())
        <div class="form-group">
          <label>Role <span style="color:var(--danger)">*</span></label>
          <select name="role" class="form-control">
            @php $currentRole = old('role', request('role', $user->role ?? 'employee')); @endphp
            <option value="employee" {{ $currentRole === 'employee' ? 'selected' : '' }}>Employee</option>
            <option value="manager"  {{ $currentRole === 'manager' ? 'selected' : '' }}>Manager</option>
            <option value="admin"    {{ $currentRole === 'admin'   ? 'selected' : '' }}>Admin</option>
          </select>
        </div>
      @else
        {{-- Manager adding user --}}
        <div class="form-group">
          <label>Role <span style="color:var(--danger)">*</span></label>
          <select name="role" class="form-control">
            <option value="employee" selected>Employee</option>
            <option value="manager">Manager</option>
          </select>
        </div>
        <input type="hidden" name="manager_id" value="{{ auth()->id() }}">
      @endif


      <div style="display:flex;gap:12px;margin-top:20px">
        <button type="submit" class="btn btn-primary" style="flex:1">{{ isset($user) ? 'Update User' : 'Create User' }}</button>
        <a href="{{ auth()->user()->isManager() ? route('manager.team') : route('admin.users') }}" class="btn btn-outline">Cancel</a>
      </div>
    </form>
  </div>
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

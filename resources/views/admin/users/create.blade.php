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
        <input type="password" name="password" class="form-control" {{ isset($user) ? '' : 'required' }} placeholder="Minimum 8 characters">
        @error('password')<div class="form-error">{{ $message }}</div>@enderror
      </div>

      {{-- Role & Manager Selection --}}
      @if(auth()->user()->isAdmin())
        <div class="form-group">
          <label>Role <span style="color:var(--danger)">*</span></label>
          <select name="role" class="form-control" onchange="toggleManagerSelect(this.value)">
            @php $currentRole = old('role', request('role', $user->role ?? 'employee')); @endphp
            <option value="employee" {{ $currentRole === 'employee' ? 'selected' : '' }}>Employee</option>
            <option value="manager"  {{ $currentRole === 'manager' ? 'selected' : '' }}>Manager</option>
            <option value="admin"    {{ $currentRole === 'admin'   ? 'selected' : '' }}>Admin</option>
          </select>
        </div>

        <div class="form-group" id="manager-select-group" style="display: {{ $currentRole === 'employee' ? 'block' : 'none' }}">
          <label>Assigned Manager</label>
          <select name="manager_id" class="form-control">
            <option value="">None</option>
            @foreach($managers as $mgr)
              <option value="{{ $mgr->id }}" {{ old('manager_id', $user->manager_id ?? '') == $mgr->id ? 'selected' : '' }}>{{ $mgr->name }}</option>
            @endforeach
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
function toggleManagerSelect(role) {
  const group = document.getElementById('manager-select-group');
  if (group) group.style.display = (role === 'employee') ? 'block' : 'none';
}
</script>
@endsection

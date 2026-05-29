@extends('layouts.app')
@section('title','User Management')
@section('page-title','User Management')

@section('content')
<div class="card" style="margin-bottom:20px">
  <div style="display:flex; justify-content:space-between; flex-wrap:wrap; gap:16px; align-items:flex-end;">
    <form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end">
      <div class="form-group" style="margin:0;min-width:200px">
        <label>Search</label>
        <input type="text" name="search" class="form-control" placeholder="Name, email, or ID…" value="{{ request('search') }}">
      </div>
      <div class="form-group" style="margin:0;min-width:140px">
        <label>Role</label>
        <select name="role" class="form-control">
          <option value="">All Roles</option>
          <option value="admin"    {{ request('role')=='admin'    ?'selected':'' }}>Admin</option>
          <option value="manager"  {{ request('role')=='manager'  ?'selected':'' }}>Manager</option>
          <option value="employee" {{ request('role')=='employee' ?'selected':'' }}>Employee</option>
        </select>
      </div>
      <button type="submit" class="btn btn-primary">Search</button>
      <a href="{{ route('admin.users') }}" class="btn btn-outline">Reset</a>
    </form>
    
    <div style="display:flex;gap:8px">
      <a href="{{ route('admin.users.create', ['role' => 'manager']) }}" class="btn btn-warning">+ Add Manager</a>
      <a href="{{ route('admin.users.create', ['role' => 'employee']) }}" class="btn btn-primary">+ Add Employee</a>
    </div>
  </div>
</div>

<div class="card">
  <div class="table-wrap">
    <table>
      <thead><tr><th>User</th><th>Role</th><th>Department</th><th>Manager</th><th>Status</th><th>Joined</th><th>Actions</th></tr></thead>
      <tbody>
        @forelse($users as $user)
        <tr>
          <td>
            <div style="font-weight:600">{{ $user->name }}</div>
            <div style="font-size:12px;color:var(--primary)">@ {{ $user->username }}</div>
            <div style="font-size:11px;color:var(--muted)">{{ $user->email }}</div>
          </td>
          <td><span class="badge {{ $user->role==='admin' ? 'badge-danger' : ($user->role==='manager' ? 'badge-warning' : 'badge-primary') }}">{{ ucfirst($user->role) }}</span></td>
          <td style="color:var(--muted)">{{ $user->department ?? '—' }}</td>
          <td style="color:var(--muted)">{{ $user->manager?->name ?? '—' }}</td>
          <td>
            @if($user->is_active)
              <span class="badge badge-success">Active</span>
            @else
              <span class="badge badge-danger">Inactive</span>
            @endif
          </td>
          <td style="color:var(--muted)">{{ $user->created_at->format('M d, Y') }}</td>
          <td>
            <div style="display:flex;gap:6px;align-items:center;">
              <a href="{{ route('admin.users.edit',$user->id) }}" class="btn btn-outline btn-sm">Edit</a>
              <form method="POST" action="{{ route('admin.users.destroy',$user->id) }}" onsubmit="return confirm('Are you sure? This cannot be undone.')" style="margin:0;">
                @csrf @method('DELETE')
                <button class="btn btn-danger btn-sm" {{ $user->id===auth()->id() ? 'disabled' : '' }}>Delete</button>
              </form>
            </div>
          </td>
        </tr>
        @empty
          <tr><td colspan="7"><div class="empty-state"><div class="empty-state-icon">👤</div><h3>No users found</h3></div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  {{ $users->links('vendor.pagination.custom') }}
</div>
@endsection

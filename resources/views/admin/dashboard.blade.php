@extends('layouts.app')
@section('title','Admin Dashboard')
@section('page-title','Admin Dashboard')

@section('content')
<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(79,126,255,.12)">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
    </div>
    <div class="stat-value">{{ $totalUsers }}</div>
    <div class="stat-label">Total Users</div>
  </div>
  <div class="stat-card info">
    <div class="stat-icon" style="background:rgba(56,189,248,.12)">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#38BDF8" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
    </div>
    <div class="stat-value">{{ $totalEmployees }}</div>
    <div class="stat-label">Employees</div>
  </div>
  <div class="stat-card success">
    <div class="stat-icon" style="background:rgba(16,217,122,.12)">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10D97A" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
    </div>
    <div class="stat-value">{{ $activeToday }}</div>
    <div class="stat-label">Active Today</div>
  </div>
  <div class="stat-card warning">
    <div class="stat-icon" style="background:rgba(255,181,71,.12)">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#FFB547" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
    </div>
    <div class="stat-value">{{ $pendingPulses }}</div>
    <div class="stat-label">Pending Pulses</div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(79,126,255,.12)">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
    </div>
    <div class="stat-value">{{ $totalManagers }}</div>
    <div class="stat-label">Managers</div>
  </div>
  <div class="stat-card info">
    <div class="stat-icon" style="background:rgba(56,189,248,.12)">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#38BDF8" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
    </div>
    <div class="stat-value">{{ $totalProjects }}</div>
    <div class="stat-label">Projects</div>
  </div>
</div>

<div class="grid-2">
  <div class="card">
    <div class="card-header">
      <div class="card-title">Quick Actions</div>
    </div>
    <div style="display:flex;flex-direction:column;gap:10px">
      <a href="{{ route('admin.users.create') }}" class="btn btn-primary">+ Add New User</a>
      <a href="{{ route('admin.users') }}" class="btn btn-outline">Manage All Users</a>
      <a href="{{ route('manager.reports') }}" class="btn btn-outline">View Reports</a>
    </div>
  </div>
  <div class="card">
    <div class="card-header"><div class="card-title">Recently Added Users</div></div>
    @foreach($recentUsers as $u)
    <div style="display:flex;align-items:center;gap:12px;padding:8px 0;border-bottom:1px solid var(--border)">
      <div class="sidebar-avatar" style="width:32px;height:32px;font-size:12px">{{ strtoupper(substr($u->name,0,1)) }}</div>
      <div style="flex:1">
        <div style="font-weight:500;font-size:13px">{{ $u->name }}</div>
        <div style="font-size:11px;color:var(--muted)">{{ $u->email }}</div>
      </div>
      <span class="badge {{ $u->role === 'admin' ? 'badge-danger' : ($u->role === 'manager' ? 'badge-warning' : 'badge-primary') }}">{{ ucfirst($u->role) }}</span>
    </div>
    @endforeach
  </div>
</div>
@endsection

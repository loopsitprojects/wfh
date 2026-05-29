@extends('layouts.app')
@section('title','Notifications')
@section('page-title','Notifications')

@section('content')
<div style="max-width:720px;margin:0 auto">
  <div class="card">
    <div class="card-header">
      <div class="card-title">All Notifications</div>
      <div style="display:flex; gap:8px;">
        <form method="POST" action="{{ route('notifications.readAll') }}">@csrf<button class="btn btn-outline btn-sm">Mark all read</button></form>
        <form method="POST" action="{{ route('notifications.clearAll') }}" onsubmit="return confirm('Are you sure you want to clear all notifications?')">@csrf @method('DELETE')<button class="btn btn-danger btn-sm">Clear All</button></form>
      </div>
    </div>
    @forelse($notifications as $n)
    <a href="{{ route('notifications.read',$n->id) }}"
       style="display:flex;gap:14px;padding:14px;border-radius:var(--radius-sm);margin-bottom:6px;text-decoration:none;color:var(--text);background:{{ $n->read_at ? 'transparent' : 'var(--card-hover)' }};border:1px solid {{ $n->read_at ? 'transparent' : 'rgba(79,126,255,.15)' }};transition:all .2s">
      <div style="width:10px;height:10px;border-radius:50%;margin-top:4px;flex-shrink:0;background:{{ $n->read_at ? 'var(--border)' : 'var(--primary)' }}"></div>
      <div style="flex:1">
        <div style="font-weight:{{ $n->read_at ? '400' : '600' }}">{{ $n->data['message'] }}</div>
        <div style="font-size:11px;color:var(--muted);margin-top:4px">{{ $n->created_at->format('M d, Y h:i A') }} · {{ $n->created_at->diffForHumans() }}</div>
      </div>
    </a>
    @empty
      <div class="empty-state"><div class="empty-state-icon">🔔</div><h3>No notifications</h3><p>You're all caught up!</p></div>
    @endforelse
    {{ $notifications->links('vendor.pagination.custom') }}
  </div>
</div>
@endsection

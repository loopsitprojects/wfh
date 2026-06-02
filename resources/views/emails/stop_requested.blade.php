@extends('emails.layout')
@section('title', 'Stop Session Request - ' . $pulse->employee->name)
@section('content')
<h1 class="greeting">Hello {{ $notifiable->name }},</h1>
<p class="intro-text">An employee has requested to stop their active WFH session.</p>

<div class="details-card">
    <div class="detail-row">
        <div class="detail-label">Employee</div>
        <div class="detail-value">
            <span class="employee-badge">{{ $pulse->employee->name }}</span>
            <span style="font-size:12px; color:#64748B; margin-left: 6px;">({{ $pulse->employee->email }})</span>
        </div>
    </div>
    <div class="detail-row">
        <div class="detail-label">Requested At</div>
        <div class="detail-value">{{ now()->setTimezone('Asia/Colombo')->format('M d, Y - h:i A') }} (SL Time)</div>
    </div>
    <div class="detail-row">
        <div class="detail-label">Active Session Description</div>
        <div class="detail-value">
            <div class="description-box">
                "{{ $pulse->description ?: 'No description provided' }}"
            </div>
        </div>
    </div>
</div>

<div class="btn-container">
    <a href="{{ route('manager.dashboard') }}" class="btn-primary">Go to Dashboard</a>
</div>
@endsection

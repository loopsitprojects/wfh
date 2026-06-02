@extends('emails.layout')
@section('title', 'New Pulse Request - ' . $pulse->employee->name)
@section('content')
<h1 class="greeting">Hello {{ $notifiable->name }},</h1>
<p class="intro-text">A new Work From Home check-in request has been submitted and is awaiting your review.</p>

<div class="details-card">
    <div class="detail-row">
        <div class="detail-label">Employee</div>
        <div class="detail-value">
            <span class="employee-badge">{{ $pulse->employee->name }}</span>
            <span style="font-size:12px; color:#64748B; margin-left: 6px;">({{ $pulse->employee->email }})</span>
        </div>
    </div>
    <div class="detail-row">
        <div class="detail-label">Submitted At</div>
        <div class="detail-value">{{ $pulse->created_at->setTimezone('Asia/Colombo')->format('M d, Y - h:i A') }} (SL Time)</div>
    </div>
    <div class="detail-row">
        <div class="detail-label">Task Focus / Description</div>
        <div class="detail-value">
            <div class="description-box">
                "{{ $pulse->description ?: 'No description provided' }}"
            </div>
        </div>
    </div>
</div>

<div class="btn-container">
    <a href="{{ route('manager.pulses') }}" class="btn-primary">Review Pulse Request</a>
</div>
@endsection

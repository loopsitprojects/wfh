@extends('emails.layout')
@section('title', $pulse->isApproved() ? 'WFH Pulse Approved!' : 'WFH Pulse Rejected')
@section('content')
<h1 class="greeting">Hello {{ $notifiable->name }},</h1>

@if($pulse->isApproved())
    <p class="intro-text">Great news! Your Work From Home pulse request has been approved.</p>
    
    <div class="details-card">
        <div class="detail-row">
            <div class="detail-label">Status</div>
            <div class="detail-value">
                <span class="status-badge status-approved">Approved</span>
            </div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Allocated Duration</div>
            <div class="detail-value" style="font-size: 18px; font-weight: 700; color: #10D97A;">
                @php
                    $hours = floor($pulse->duration_hours);
                    $minutes = round(($pulse->duration_hours - $hours) * 60);
                    $timeString = '';
                    if ($hours > 0) $timeString .= $hours . ' hr ';
                    if ($minutes > 0 || $hours == 0) $timeString .= $minutes . ' min';
                @endphp
                {{ trim($timeString) }}
            </div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Request Description</div>
            <div class="detail-value">
                <div class="description-box">
                    "{{ $pulse->description }}"
                </div>
            </div>
        </div>
    </div>

    <div class="btn-container">
        <a href="{{ route('employee.dashboard') }}" class="btn-primary" style="background-color: #10D97A; box-shadow: 0 4px 12px rgba(16, 217, 122, 0.3);">Start Timer</a>
    </div>
@else
    <p class="intro-text">Unfortunately, your Work From Home pulse request was rejected.</p>
    
    <div class="details-card">
        <div class="detail-row">
            <div class="detail-label">Status</div>
            <div class="detail-value">
                <span class="status-badge status-rejected">Rejected</span>
            </div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Rejection Reason</div>
            <div class="detail-value" style="color: #FF4F6A; font-weight: 600;">
                "{{ $pulse->rejection_reason ?: 'No reason provided.' }}"
            </div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Request Description</div>
            <div class="detail-value">
                <div class="description-box">
                    "{{ $pulse->description }}"
                </div>
            </div>
        </div>
    </div>

    <div class="btn-container">
        <a href="{{ route('employee.dashboard') }}" class="btn-primary" style="background-color: #FF4F6A; box-shadow: 0 4px 12px rgba(255, 79, 106, 0.3);">Submit New Request</a>
    </div>
@endif
@endsection

@extends('layouts.app')
@section('title','My Dashboard')
@section('page-title','My Dashboard')

@section('content')
@php
  function fmtSec($s){ $s=abs($s); $h=intdiv($s,3600);$m=intdiv($s%3600,60);return sprintf('%dh %02dm',$h,$m); }
@endphp

{{-- Stats --}}
<div class="stats-grid">
  <div class="stat-card success">
    <div class="stat-icon" style="background:rgba(16,217,122,.12)">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10D97A" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
    </div>
    <div class="stat-value">{{ fmtSec($todaySec) }}</div>
    <div class="stat-label">Today's Work</div>
  </div>
  <div class="stat-card info">
    <div class="stat-icon" style="background:rgba(56,189,248,.12)">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#38BDF8" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
    </div>
    <div class="stat-value">{{ fmtSec($weekSec) }}</div>
    <div class="stat-label">This Week</div>
  </div>
  <div class="stat-card {{ $activePulse ? 'success' : ($pendingPulse ? 'warning' : 'danger') }}">
    <div class="stat-icon" style="background:rgba(79,126,255,.12)">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
    </div>
    <div class="stat-value" style="font-size:18px">
      @if($activeTimer) Running
      @elseif($activePulse) Approved
      @elseif($pendingPulse) Pending
      @else No Pulse
      @endif
    </div>
    <div class="stat-label">Pulse Status</div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(79,126,255,.12)">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
    </div>
    <div class="stat-value">{{ auth()->user()->timeLogs()->whereNotNull('ended_at')->count() }}</div>
    <div class="stat-label">Total Sessions</div>
  </div>
</div>

{{-- Timer + Pulse Request --}}
<div class="grid-2" style="margin-bottom:24px">

  {{-- Timer Widget --}}
  <div class="timer-widget">
    <div id="timer-badge">
      @if($activeTimer)
        <div class="pulse-indicator running">Timer Running</div>
      @endif
    </div>
    <div class="timer-display {{ $activeTimer ? 'running' : '' }}" id="timer-display">
      00:00:00
    </div>
    <div class="timer-status" id="timer-status">
      @if($activeTimer) 
        Started at {{ $activeTimer->started_at->format('h:i A') }} 
        @if($activeTimer->allocated_hours) (Allocated: {{ $activeTimer->allocated_hours }}h) @endif
      @elseif($activePulse) Pulse approved!
      @elseif($pendingPulse) Awaiting manager approval…
      @else Request a pulse to start timer
      @endif
    </div>
    <div class="timer-actions" style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
      @if($activeTimer)
        <button class="btn btn-warning btn-lg" id="btn-pause" onclick="pauseTimer()" style="background:#f59e0b;border-color:#f59e0b">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><rect x="6" y="4" width="4" height="16" rx="1"/><rect x="14" y="4" width="4" height="16" rx="1"/></svg>
          Pause Timer
        </button>
        @if($activePulse && !$activePulse->stop_requested)
          <button class="btn btn-outline btn-lg" id="btn-request-stop" onclick="requestStop()" style="border-color:var(--danger);color:var(--danger)">
            Request to Stop
          </button>
        @elseif($activePulse && $activePulse->stop_requested)
          <span class="badge badge-muted" style="padding:12px 20px;font-size:14px">Stop Requested...</span>
        @endif
      @elseif($activePulse && $activePulse->is_paused)
        <button class="btn btn-success btn-lg" id="btn-resume" onclick="resumeTimer()">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg>
          Resume Timer
        </button>
      @elseif($activePulse)
        <button class="btn btn-success btn-lg" id="btn-start" onclick="startTimer()">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg>
          Start Timer
        </button>
      @else
        <span class="btn btn-outline btn-lg" style="opacity:0.5;cursor:default">
          Waiting for Pulse
        </span>
      @endif
    </div>

  </div>

  {{-- Pulse Request --}}
  <div class="card">
    <div class="card-header">
      <div>
        <div class="card-title">⚡ Request Pulse</div>
        <div class="card-subtitle">Upload work proof to start timer</div>
      </div>
    </div>

    @if($pendingPulse)
      <div class="alert alert-warning" style="margin-bottom:0">
        ⏳ Awaiting manager approval for your pulse request.
      </div>
    @else
      <form method="POST" action="{{ route('employee.pulse.store') }}" enctype="multipart/form-data" onsubmit="document.getElementById('btn-request-pulse').classList.add('loading')">
        @csrf
        <div class="form-group" style="margin-bottom:12px">
          <div class="file-upload" id="drop-zone" onclick="document.getElementById('image-input').click()" style="padding:20px 10px">
            <div class="file-upload-icon" style="font-size:24px;margin-bottom:4px">📸</div>
            <div style="font-weight:600;font-size:12px">Click to upload screenshot</div>
            <div class="file-preview" id="preview" style="display:none;margin-top:10px">
              <img id="preview-img" src="" alt="Preview" style="height:100px;width:auto;margin:0 auto">
            </div>
          </div>
          <input type="file" id="image-input" name="image" accept="image/*" style="display:none" required onchange="previewImage(this)">
        </div>

        <div class="form-group" style="margin-bottom:12px">
          <label>Work Description</label>
          <textarea name="description" class="form-control" placeholder="What are you working on?…" required style="font-size:13px;padding:8px 12px"></textarea>
        </div>

        <button type="submit" id="btn-request-pulse" class="btn btn-primary" style="width:100%;justify-content:center">
          Send Request
        </button>
      </form>
    @endif
  </div>
</div>

{{-- Recent Sessions --}}
<div class="card">
  <div class="card-header">
    <div><div class="card-title">Recent Sessions</div></div>
    <a href="{{ route('employee.history') }}" class="btn btn-outline btn-sm">View All</a>
  </div>
  @if($recentLogs->count())
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Time</th>
          <th>Duration</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        @foreach($recentLogs as $log)
        <tr>
          <td>
            <div style="font-weight:600;font-size:13px">{{ $log->started_at->format('h:i A') }}</div>
            <div style="font-size:11px;color:var(--muted)">{{ $log->started_at->format('d M') }}</div>
          </td>
          <td style="font-family:'JetBrains Mono',monospace;font-size:13px">
            @if($log->duration_seconds)
              {{ floor($log->duration_seconds/3600) }}h {{ floor(($log->duration_seconds%3600)/60) }}m
            @else
              <span style="color:var(--success)">Running...</span>
            @endif
          </td>
          <td>
            @if($log->ended_at)
              <span class="badge badge-muted">Completed</span>
            @else
              <span class="badge badge-success">Active</span>
            @endif
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @else
    <div class="empty-state"><div class="empty-state-icon">⏱</div><h3>No sessions yet</h3><p>Start your first timer after getting a pulse approved.</p></div>
  @endif
</div>
@endsection

@push('scripts')
<script>
let isRunning = false;
let hasPulse  = {{ $activePulse ? 'true' : 'false' }};
let timeOffset = 0;

function previewImage(input) {
  const preview = document.getElementById('preview');
  const previewImg = document.getElementById('preview-img');
  const file = input.files[0];
  if (file) {
    previewImg.src = URL.createObjectURL(file);
    preview.style.display = 'block';
    document.querySelector('#drop-zone .file-upload-icon').style.display = 'none';
  }
}

function pollTimer() {
  fetch('{{ route("employee.timer.status") }}')
    .then(r => r.json())
    .then(data => {
      // Sync time offset
      const serverNow = Math.floor(new Date(data.server_time).getTime() / 1000);
      timeOffset = Math.floor(Date.now() / 1000) - serverNow;

      if (data.has_active_timer !== isRunning || data.has_active_pulse !== hasPulse) {
        location.reload();
      }
    }).catch(() => {});
}
setInterval(pollTimer, 10000); 


function pauseTimer(){
  const btn = document.getElementById('btn-pause');
  if(btn) btn.classList.add('loading');
  fetch('{{ route("employee.timer.pause") }}', {
    method:'POST', headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content}
  }).then(r=>r.json()).then(d=>{
    if(d.success){ 
      Toast.fire({ icon: 'success', title: 'Timer paused' }).then(() => location.reload());
    } else { if(btn) btn.classList.remove('loading'); }
  }).catch(e=>{ console.error(e); if(btn) btn.classList.remove('loading'); });
}

function resumeTimer(){
  const btn = document.getElementById('btn-resume');
  if(btn) btn.classList.add('loading');
  fetch('{{ route("employee.timer.resume") }}', {
    method:'POST', headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content}
  }).then(r=>r.json()).then(d=>{
    if(d.success){ 
      Toast.fire({ icon: 'success', title: 'Timer resumed' }).then(() => location.reload());
    } else { if(btn) btn.classList.remove('loading'); }
  }).catch(e=>{ console.error(e); if(btn) btn.classList.remove('loading'); });
}

function requestStop(){
  Swal.fire({
    title: 'Request to Stop?',
    text: 'This will notify your manager that you want to end your shift.',
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#FF4F6A',
    cancelButtonColor: '#64748B',
    confirmButtonText: 'Yes, Request Stop',
    background: '#1A2235', color: '#E2E8F0'
  }).then((result) => {
    if (result.isConfirmed) {
      const btn = document.getElementById('btn-request-stop');
      if(btn) btn.classList.add('loading');
      fetch('{{ route("employee.timer.request-stop") }}', {
        method:'POST', headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content}
      }).then(r=>r.json()).then(d=>{
        if(d.success){ 
          Toast.fire({ icon: 'success', title: 'Request sent' }).then(() => location.reload());
        } else { if(btn) btn.classList.remove('loading'); }
      }).catch(e=>{ console.error(e); if(btn) btn.classList.remove('loading'); });
    }
  });
}

function startTimer(){
  const btn = document.getElementById('btn-start');
  if(btn) btn.classList.add('loading');
  fetch('{{ route("employee.timer.start") }}', {
    method:'POST', headers:{'X-CSRF-TOKEN':window.csrfToken,'Content-Type':'application/json'}
  }).then(r=>r.json()).then(d=>{
    if(d.success){ Toast.fire({ icon: 'success', title: 'Timer started' }).then(() => location.reload()); }
    else { 
      if(btn) btn.classList.remove('loading');
      Swal.fire({ icon:'error', title:'Error', text:d.error, background: '#1A2235', color: '#E2E8F0' }); 
    }
  }).catch(e=>{ if(btn) btn.classList.remove('loading'); });
}

function stopTimer(){
  fetch('{{ route("employee.timer.stop") }}', {
    method:'POST', headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content}
  }).then(r=>r.json()).then(d=>{
    if(d.success){ location.reload(); }
  }).catch(e=>console.error(e));
}





@if($activeTimer)
  let startTime = {{ $activeTimer->started_at->timestamp }};
  let allocatedHours = {{ $activeTimer->allocated_hours ?? 0 }};
  let spentSec = {{ $spentSec ?? 0 }};
  let totalDuration = Math.round(allocatedHours * 3600);
  let remainingOnStart = totalDuration - spentSec;
  let endTime = startTime + remainingOnStart;
  isRunning = true;


  // Initial sync
  timeOffset = Math.floor(Date.now() / 1000) - {{ now()->timestamp }};

  function tick() {
    if (!isRunning) return;
    let now = Math.floor(Date.now() / 1000) - timeOffset;
    let remaining = endTime - now;
    
    if (remaining <= 0) {
      remaining = 0;
      isRunning = false;
      document.getElementById('timer-badge').innerHTML = '<span class="pulse-indicator" style="background:var(--danger)">Time Up</span>';
      document.getElementById('timer-display').classList.remove('running');
      document.getElementById('timer-display').textContent = '00:00:00';
      
      // Auto-stop!
      stopTimer();
    } else {
      let h = Math.floor(remaining / 3600);
      let m = Math.floor((remaining % 3600) / 60);
      let s = remaining % 60;
      
      document.getElementById('timer-display').textContent = 
        String(h).padStart(2, '0') + ':' + 
        String(m).padStart(2, '0') + ':' + 
        String(s).padStart(2, '0');
    }
  }
  
  setInterval(tick, 1000);
  tick();
@endif

</script>
@endpush

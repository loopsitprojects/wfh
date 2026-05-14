@extends('layouts.app')
@section('title','Request Pulse')
@section('page-title','Request a Pulse')

@section('content')
<div style="max-width:600px;margin:0 auto">
  @if($hasPending)
  <div class="alert alert-warning">
    ⏳ You already have a pending pulse request. Wait for your manager to respond before submitting another.
  </div>
  @endif

  <div class="card">
    <div class="card-header">
      <div>
        <div class="card-title">New Pulse Request</div>
        <div class="card-subtitle">Upload a screenshot of your current work</div>
      </div>
    </div>

    @if(!auth()->user()->manager_id)
      <div class="alert alert-danger">You don't have a manager assigned. Contact your admin.</div>
    @else
    <form method="POST" action="{{ route('employee.pulse.store') }}" enctype="multipart/form-data" id="pulse-form">
      @csrf

      <div class="form-group">
        <label>Work Screenshot <span style="color:var(--danger)">*</span></label>
        <div class="file-upload" id="drop-zone" onclick="document.getElementById('image-input').click()">
          <div class="file-upload-icon">📸</div>
          <div style="font-weight:600;margin-bottom:6px">Drop image here or click to browse</div>
          <div class="file-upload-text">PNG, JPG, WEBP — max 5 MB</div>
          <div class="file-preview" id="preview" style="display:none">
            <img id="preview-img" src="" alt="Preview">
          </div>
        </div>
        <input type="file" id="image-input" name="image" accept="image/*" style="display:none" required>
        @error('image')<div class="form-error">{{ $message }}</div>@enderror
      </div>

      <div class="form-group">
        <label>Description <span style="color:var(--muted)">(optional)</span></label>
        <textarea name="description" class="form-control" placeholder="What are you working on? Any notes for your manager…">{{ old('description') }}</textarea>
      </div>

      <div style="display:flex;gap:12px;margin-top:8px">
        <button type="submit" class="btn btn-primary" {{ $hasPending ? 'disabled' : '' }}>
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
          Send Request
        </button>
        <a href="{{ route('employee.dashboard') }}" class="btn btn-outline">Cancel</a>
      </div>
    </form>
    @endif
  </div>
</div>
@endsection

@push('scripts')
<script>
const dropZone  = document.getElementById('drop-zone');
const imgInput  = document.getElementById('image-input');
const preview   = document.getElementById('preview');
const previewImg = document.getElementById('preview-img');

imgInput.addEventListener('change', () => {
  const file = imgInput.files[0];
  if (!file) return;
  previewImg.src = URL.createObjectURL(file);
  preview.style.display = 'block';
  dropZone.querySelector('.file-upload-icon').style.display = 'none';
});

dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.classList.add('drag-over'); });
dropZone.addEventListener('dragleave', () => dropZone.classList.remove('drag-over'));
dropZone.addEventListener('drop', e => {
  e.preventDefault(); dropZone.classList.remove('drag-over');
  const file = e.dataTransfer.files[0];
  if (file && file.type.startsWith('image/')) {
    const dt = new DataTransfer(); dt.items.add(file); imgInput.files = dt.files;
    previewImg.src = URL.createObjectURL(file);
    preview.style.display = 'block';
  }
});
</script>
@endpush

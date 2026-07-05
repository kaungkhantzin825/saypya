@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Contact Messages</h2>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <!-- Stats -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h3 class="mb-0">{{ $stats['new'] }}</h3>
                    <small>New Messages</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h3 class="mb-0">{{ $stats['read'] }}</h3>
                    <small>Read Messages</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h3 class="mb-0">{{ $stats['replied'] }}</h3>
                    <small>Replied Messages</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control">
                        <option value="">All Messages</option>
                        <option value="new"     {{ request('status') == 'new'     ? 'selected' : '' }}>New</option>
                        <option value="read"    {{ request('status') == 'read'    ? 'selected' : '' }}>Read</option>
                        <option value="replied" {{ request('status') == 'replied' ? 'selected' : '' }}>Replied</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">Filter</button>
                    <a href="{{ route('admin.contact-messages.index') }}" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Messages Table -->
    <div class="card">
        <div class="card-body">

            {{-- Bulk-delete toolbar --}}
            <form id="bulkDeleteForm"
                  action="{{ route('admin.contact-messages.bulk-delete') }}"
                  method="POST"
                  onsubmit="return confirmBulkDelete()">
                @csrf
                @method('DELETE')

                <div class="d-flex align-items-center mb-3 gap-2">
                    <button type="submit" id="bulkDeleteBtn"
                            class="btn btn-danger btn-sm" disabled>
                        <i class="fas fa-trash mr-1"></i>
                        Delete Selected (<span id="selectedCount">0</span>)
                    </button>
                    <small class="text-muted ml-2" id="selectHint">Select rows below to enable bulk delete</small>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th style="width:40px;">
                                    <input type="checkbox" id="selectAll" title="Select all">
                                </th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Subject</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($messages as $message)
                                <tr class="{{ $message->status === 'new' ? 'font-weight-bold' : '' }}">
                                    <td>
                                        <input type="checkbox" name="ids[]"
                                               value="{{ $message->id }}"
                                               class="row-checkbox">
                                    </td>
                                    <td>
                                        {{ $message->name }}
                                        @if($message->status === 'new')
                                            <span class="badge badge-primary badge-sm">New</span>
                                        @endif
                                    </td>
                                    <td>{{ $message->email }}</td>
                                    <td>{{ Str::limit($message->subject, 50) }}</td>
                                    <td>
                                        @if($message->status === 'new')
                                            <span class="badge badge-primary">New</span>
                                        @elseif($message->status === 'read')
                                            <span class="badge badge-info">Read</span>
                                        @else
                                            <span class="badge badge-success">Replied</span>
                                        @endif
                                    </td>
                                    <td>{{ $message->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <a href="{{ route('admin.contact-messages.show', $message) }}"
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button type="button"
                                                class="btn btn-sm btn-danger"
                                                onclick="deleteSingle({{ $message->id }})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">No messages found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </form>

            {{-- Hidden single-delete forms --}}
            @foreach($messages as $message)
            <form id="delete-form-{{ $message->id }}"
                  action="{{ route('admin.contact-messages.destroy', $message) }}"
                  method="POST" class="d-none">
                @csrf
                @method('DELETE')
            </form>
            @endforeach

            <div class="mt-3">
                {{ $messages->links() }}
            </div>
        </div>
    </div>
</div>

<script>
// ── Select All toggle ─────────────────────────────────────────────────────────
const selectAll   = document.getElementById('selectAll');
const checkboxes  = document.querySelectorAll('.row-checkbox');
const bulkBtn     = document.getElementById('bulkDeleteBtn');
const countSpan   = document.getElementById('selectedCount');
const selectHint  = document.getElementById('selectHint');

function updateBulkBtn() {
    const checked = document.querySelectorAll('.row-checkbox:checked').length;
    countSpan.textContent = checked;
    bulkBtn.disabled = checked === 0;
    selectHint.style.display = checked > 0 ? 'none' : '';
}

selectAll.addEventListener('change', function () {
    checkboxes.forEach(cb => cb.checked = this.checked);
    updateBulkBtn();
});

checkboxes.forEach(cb => cb.addEventListener('change', function () {
    selectAll.checked = [...checkboxes].every(c => c.checked);
    updateBulkBtn();
}));

// ── Bulk delete confirm ───────────────────────────────────────────────────────
function confirmBulkDelete() {
    const n = document.querySelectorAll('.row-checkbox:checked').length;
    if (n === 0) return false;
    return confirm(`Delete ${n} selected message(s)? This cannot be undone.`);
}

// ── Single delete via hidden form ─────────────────────────────────────────────
function deleteSingle(id) {
    if (!confirm('Delete this message?')) return;
    document.getElementById('delete-form-' + id).submit();
}
</script>
@endsection

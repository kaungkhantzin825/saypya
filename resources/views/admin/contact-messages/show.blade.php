@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <div class="mb-4">
        <a href="{{ route('admin.contact-messages.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Messages
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <!-- Message Details -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Message Details</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>From:</strong> {{ $message->name }}
                    </div>
                    <div class="mb-3">
                        <strong>Email:</strong> <a href="mailto:{{ $message->email }}">{{ $message->email }}</a>
                    </div>
                    @if($message->phone)
                    <div class="mb-3">
                        <strong>Phone:</strong> {{ $message->phone }}
                    </div>
                    @endif
                    <div class="mb-3">
                        <strong>Subject:</strong> {{ $message->subject }}
                    </div>
                    <div class="mb-3">
                        <strong>Date:</strong> {{ $message->created_at->format('F d, Y h:i A') }}
                    </div>
                    <div class="mb-3">
                        <strong>Status:</strong>
                        @if($message->status === 'new')
                            <span class="badge badge-primary">New</span>
                        @elseif($message->status === 'read')
                            <span class="badge badge-info">Read</span>
                        @else
                            <span class="badge badge-success">Replied</span>
                        @endif
                    </div>
                    <hr>
                    <div class="mb-3">
                        <strong>Message:</strong>
                        <div class="mt-2 p-3 bg-light rounded">
                            {{ $message->message }}
                        </div>
                    </div>

                    @if($message->admin_reply)
                        <hr>
                        <div class="mb-3">
                            <strong>Your Reply:</strong>
                            <div class="mt-2 p-3 bg-success text-white rounded">
                                {{ $message->admin_reply }}
                            </div>
                            <small class="text-muted">Replied on: {{ $message->replied_at->format('F d, Y h:i A') }}</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Reply Form -->
            @if(!$message->admin_reply)
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Send Reply</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.contact-messages.reply', $message) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Reply Message</label>
                            <textarea name="reply" class="form-control" rows="6" required placeholder="Type your reply here..."></textarea>
                            @error('reply')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-success btn-block">
                            <i class="fas fa-paper-plane"></i> Send Reply
                        </button>
                    </form>
                    <hr>
                    <small class="text-muted">
                        <i class="fas fa-info-circle"></i> The reply will be saved in the system. You may need to manually email the user.
                    </small>
                </div>
            </div>
            @endif

            <!-- Actions -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">Actions</h5>
                </div>
                <div class="card-body">
                    <a href="mailto:{{ $message->email }}?subject=Re: {{ $message->subject }}" class="btn btn-primary btn-block mb-2">
                        <i class="fas fa-envelope"></i> Email User
                    </a>
                    <form action="{{ route('admin.contact-messages.destroy', $message) }}" method="POST" 
                          onsubmit="return confirm('Delete this message?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-block">
                            <i class="fas fa-trash"></i> Delete Message
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

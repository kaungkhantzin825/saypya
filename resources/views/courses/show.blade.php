@extends('layouts.app')

@section('title', $course->title)

@section('content')
<div class="bg-gray-900 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Course Info -->
            <div class="lg:col-span-2">
                <div class="mb-4">
                    <span class="bg-blue-600 text-white px-3 py-1 rounded-full text-sm font-semibold">
                        {{ $course->category->name }}
                    </span>
                    <span class="ml-2 text-gray-300">{{ ucfirst($course->level) }}</span>
                </div>
                
                <h1 class="text-4xl font-bold mb-4">{{ $course->title }}</h1>
                
                <p class="text-xl text-gray-300 mb-6">{{ $course->short_description }}</p>
                
                <div class="flex items-center space-x-6 mb-6">
                    <div class="flex items-center">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="fas fa-star text-yellow-400 {{ $i <= $course->average_rating ? '' : 'text-gray-600' }}"></i>
                        @endfor
                        <span class="ml-2 text-gray-300">{{ number_format($course->average_rating, 1) }} ({{ $course->total_reviews }} reviews)</span>
                    </div>
                    <div class="text-gray-300">
                        <i class="fas fa-users mr-1"></i>
                        {{ number_format($course->total_students) }} students
                    </div>
                    <div class="text-gray-300">
                        <i class="fas fa-clock mr-1"></i>
                        {{ $course->duration_hours }} hours
                    </div>
                </div>
                
                <div class="flex items-center mb-8">
                    <img src="{{ $course->instructor->avatar_url }}" alt="{{ $course->instructor->name }}" class="w-12 h-12 rounded-full mr-4">
                    <div>
                        <div class="font-semibold">{{ $course->instructor->name }}</div>
                        <div class="text-gray-400 text-sm">Instructor</div>
                    </div>
                </div>
            </div>
            
            <!-- Course Card -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-lg overflow-hidden sticky top-8">
                    <div class="relative">
                        <img src="{{ $course->thumbnail_url }}" alt="{{ $course->title }}" class="w-full h-48 object-cover">
                        @if($course->preview_video)
                        <div class="absolute inset-0 flex items-center justify-center">
                            <button class="bg-white bg-opacity-90 rounded-full p-4 hover:bg-opacity-100 transition-all">
                                <i class="fas fa-play text-blue-600 text-xl"></i>
                            </button>
                        </div>
                        @endif
                    </div>
                    
                    <div class="p-6">
                        <div class="text-center mb-6">
                            @if($course->hasDiscount())
                                <div class="text-3xl font-bold text-gray-900">{{ \App\Helpers\CurrencyHelper::formatMMK($course->discount_price) }}</div>
                                <div class="text-lg text-gray-500 line-through">{{ \App\Helpers\CurrencyHelper::formatMMK($course->price) }}</div>
                                <div class="text-red-600 font-semibold">{{ $course->discount_percentage }}% လျှော့စျေး</div>
                            @else
                                <div class="text-3xl font-bold text-gray-900">
                                    {{ \App\Helpers\CurrencyHelper::formatMMK($course->price) }}
                                </div>
                            @endif
                        </div>
                        
                        @if($isEnrolled)
                            <a href="{{ route('courses.learn', $course) }}" class="w-full bg-green-600 text-white py-3 px-6 rounded-lg font-semibold hover:bg-green-700 transition-colors text-center block mb-4 myanmar-text">
                                <i class="fas fa-play mr-2"></i>ဆက်လက်သင်ယူရန်
                            </a>
                        @elseif($enrollment && $enrollment->payment_status === 'pending')
                            <div class="w-full bg-yellow-100 border-2 border-yellow-500 text-yellow-800 py-3 px-6 rounded-lg font-semibold text-center mb-4">
                                <i class="fas fa-clock mr-2"></i>Pending Admin Approval
                            </div>
                            <p class="text-sm text-gray-600 text-center mb-4">Your enrollment is waiting for admin approval. You will be notified once approved.</p>
                        @elseif(auth()->check() && (auth()->user()->isLecturer() || auth()->user()->isAdmin()))
                            {{-- Instructors and Admins don't see enroll button --}}
                            <div class="w-full bg-gray-100 text-gray-600 py-3 px-6 rounded-lg font-semibold text-center mb-4">
                                <i class="fas fa-info-circle mr-2"></i>Instructor/Admin View
                            </div>
                        @else
                            <form action="{{ route('courses.enroll', $course) }}" method="POST" class="mb-4">
                                @csrf
                                <button type="submit" class="w-full bg-blue-600 text-white py-3 px-6 rounded-lg font-semibold hover:bg-blue-700 transition-colors myanmar-text">
                                    {{ $course->isFree() ? 'အခမဲ့စာရင်းသွင်းရန်' : 'စာရင်းသွင်းရန်' }}
                                </button>
                            </form>
                        @endif
                        
                        @if(!auth()->check() || (!auth()->user()->isLecturer() && !auth()->user()->isAdmin()))
                        <button onclick="toggleWishlist({{ $course->id }})" 
                                class="w-full border border-gray-300 text-gray-700 py-3 px-6 rounded-lg font-semibold hover:bg-gray-50 transition-colors"
                                data-course-id="{{ $course->id }}">
                            <i class="{{ $isInWishlist ? 'fas fa-heart text-red-500' : 'far fa-heart' }} mr-2"></i>
                            {{ $isInWishlist ? 'Remove from Wishlist' : 'Add to Wishlist' }}
                        </button>
                        @endif
                        
                        <div class="mt-6 space-y-3 text-sm text-gray-600">
                            <div class="flex items-center">
                                <i class="fas fa-infinity mr-3 text-gray-400"></i>
                                Full lifetime access
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-mobile-alt mr-3 text-gray-400"></i>
                                Access on mobile and TV
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-certificate mr-3 text-gray-400"></i>
                                Certificate of completion
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Course Content -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2">
            <!-- Tabs -->
            <div class="border-b border-gray-200 mb-8">
                <nav class="-mb-px flex space-x-8">
                    <button class="tab-button active border-b-2 border-blue-600 py-2 px-1 text-blue-600 font-medium" data-tab="overview">
                        Overview
                    </button>
                    <button class="tab-button border-b-2 border-transparent py-2 px-1 text-gray-500 hover:text-gray-700" data-tab="curriculum">
                        Curriculum
                    </button>
                    <button class="tab-button border-b-2 border-transparent py-2 px-1 text-gray-500 hover:text-gray-700" data-tab="reviews">
                        Reviews
                    </button>
                    <button class="tab-button border-b-2 border-transparent py-2 px-1 text-gray-500 hover:text-gray-700" data-tab="instructor">
                        Instructor
                    </button>
                    <button class="tab-button border-b-2 border-transparent py-2 px-1 text-gray-500 hover:text-gray-700" data-tab="comments-section">
                        Comments ({{ $course->comments->count() }})
                    </button>
                </nav>
            </div>
            
            <!-- Tab Content -->
            <div id="overview" class="tab-content">
                <div class="prose max-w-none">
                    <h3 class="text-2xl font-bold mb-4">About this course</h3>
                    <p class="text-gray-700 leading-relaxed mb-6">{{ $course->description }}</p>
                    
                    @if($course->what_you_learn)
                    <h4 class="text-xl font-semibold mb-4">What you'll learn</h4>
                    <ul class="grid grid-cols-1 md:grid-cols-2 gap-2 mb-6">
                        @foreach($course->what_you_learn as $item)
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-600 mr-3 mt-1"></i>
                            <span>{{ $item }}</span>
                        </li>
                        @endforeach
                    </ul>
                    @endif
                    
                    @if($course->requirements)
                    <h4 class="text-xl font-semibold mb-4">Requirements</h4>
                    <ul class="space-y-2 mb-6">
                        @foreach($course->requirements as $requirement)
                        <li class="flex items-start">
                            <i class="fas fa-dot-circle text-gray-400 mr-3 mt-2 text-xs"></i>
                            <span>{{ $requirement }}</span>
                        </li>
                        @endforeach
                    </ul>
                    @endif
                </div>
            </div>
            
            <div id="curriculum" class="tab-content hidden">
                <h3 class="text-2xl font-bold mb-6">Course Content</h3>
                <div class="space-y-4">
                    @foreach($course->sections as $section)
                    <div class="border border-gray-200 rounded-lg">
                        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                            <h4 class="font-semibold text-lg">{{ $section->title }}</h4>
                            <p class="text-sm text-gray-600">{{ $section->lessons->count() }} lessons</p>
                        </div>
                        <div class="divide-y divide-gray-200">
                            @foreach($section->lessons as $lesson)
                            <div class="px-6 py-4 flex items-center justify-between">
                                <div class="flex items-center">
                                    <i class="fas fa-play-circle text-gray-400 mr-3"></i>
                                    <div>
                                        <div class="font-medium">{{ $lesson->title }}</div>
                                        @if($lesson->description)
                                        <div class="text-sm text-gray-600">{{ $lesson->description }}</div>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center space-x-4">
                                    @if($lesson->is_preview)
                                    <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-semibold">Preview</span>
                                    @endif
                                    @if($lesson->video_duration)
                                    <span class="text-sm text-gray-500">{{ $lesson->formatted_duration }}</span>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            
            <div id="reviews" class="tab-content hidden">
                <h3 class="text-2xl font-bold mb-6">Student Reviews</h3>
                
                @if($course->reviews->count() > 0)
                <div class="space-y-6">
                    @foreach($course->reviews->take(5) as $review)
                    <div class="border-b border-gray-200 pb-6">
                        <div class="flex items-start space-x-4">
                            <img src="{{ $review->user->avatar_url }}" alt="{{ $review->user->name }}" class="w-12 h-12 rounded-full">
                            <div class="flex-1">
                                <div class="flex items-center mb-2">
                                    <h5 class="font-semibold mr-4">{{ $review->user->name }}</h5>
                                    <div class="flex items-center">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fas fa-star text-sm {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300' }}"></i>
                                        @endfor
                                    </div>
                                </div>
                                @if($review->comment)
                                <p class="text-gray-700">{{ $review->comment }}</p>
                                @endif
                                <p class="text-sm text-gray-500 mt-2">{{ $review->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-gray-600">No reviews yet. Be the first to review this course!</p>
                @endif
            </div>
            
            <div id="instructor" class="tab-content hidden">
                <div class="flex items-start space-x-6">
                    <img src="{{ $course->instructor->avatar_url }}" alt="{{ $course->instructor->name }}" class="w-24 h-24 rounded-full">
                    <div>
                        <h3 class="text-2xl font-bold mb-2">{{ $course->instructor->name }}</h3>
                        <p class="text-gray-600 mb-4">{{ $course->instructor->bio }}</p>
                        
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <div class="font-semibold">Total Students</div>
                                <div class="text-gray-600">{{ number_format($course->instructor->courses->sum('total_students')) }}</div>
                            </div>
                            <div>
                                <div class="font-semibold">Courses</div>
                                <div class="text-gray-600">{{ $course->instructor->courses->count() }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Comments Tab -->
            <div id="comments-section" class="tab-content hidden">
                <h3 class="text-2xl font-bold mb-6">Comments</h3>
                
                @auth
                <!-- Comment Form -->
                <div class="bg-gray-50 rounded-lg p-6 mb-8">
                    <form action="{{ route('comments.store', $course) }}" method="POST">
                        @csrf
                        <textarea name="comment" rows="4" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-teal-500 focus:border-teal-500" placeholder="Write your comment..." required></textarea>
                        @error('comment')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                        <div class="mt-3 flex justify-end">
                            <button type="submit" class="btn-3d btn-3d-teal">Post Comment</button>
                        </div>
                    </form>
                </div>
                @else
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-8">
                    <p class="text-blue-800">Please <a href="{{ route('login') }}" class="font-semibold underline">login</a> to post a comment.</p>
                </div>
                @endauth
                
                <!-- Comments List -->
                <div class="space-y-6">
                    @forelse($course->comments as $comment)
                    <div class="border-b border-gray-200 pb-6">
                        <div class="flex items-start space-x-4">
                            <img src="{{ $comment->user->avatar_url }}" alt="{{ $comment->user->name }}" class="w-12 h-12 rounded-full">
                            <div class="flex-1">
                                <div class="flex items-center mb-2">
                                    <h5 class="font-semibold mr-2">{{ $comment->user->name }}</h5>
                                    @if($comment->user->isLecturer())
                                    <span class="bg-teal-100 text-teal-800 text-xs px-2 py-1 rounded">Instructor</span>
                                    @elseif($comment->user->isAdmin())
                                    <span class="bg-purple-100 text-purple-800 text-xs px-2 py-1 rounded">Admin</span>
                                    @endif
                                    <span class="text-sm text-gray-500 ml-auto">{{ $comment->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="text-gray-700 mb-3">{{ $comment->comment }}</p>
                                
                                @auth
                                <div class="flex items-center space-x-4 text-sm">
                                    <button onclick="toggleReplyForm({{ $comment->id }})" class="text-teal-600 hover:text-teal-700 font-medium">
                                        <i class="fas fa-reply mr-1"></i>Reply
                                    </button>
                                    @if($comment->user_id === auth()->id())
                                    <form action="{{ route('comments.destroy', $comment) }}" method="POST" class="inline" onsubmit="return confirm('Delete this comment?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-700 font-medium">
                                            <i class="fas fa-trash mr-1"></i>Delete
                                        </button>
                                    </form>
                                    @endif
                                </div>
                                
                                <!-- Reply Form -->
                                <div id="reply-form-{{ $comment->id }}" class="hidden mt-4 bg-gray-50 rounded-lg p-4">
                                    <form action="{{ route('comments.store', $course) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                                        <textarea name="comment" rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-teal-500 focus:border-teal-500" placeholder="Write your reply..." required></textarea>
                                        <div class="mt-2 flex justify-end space-x-2">
                                            <button type="button" onclick="toggleReplyForm({{ $comment->id }})" class="px-4 py-2 text-gray-600 hover:text-gray-800">Cancel</button>
                                            <button type="submit" class="btn-3d btn-3d-teal">Post Reply</button>
                                        </div>
                                    </form>
                                </div>
                                @endauth
                                
                                <!-- Replies -->
                                @if($comment->replies->count() > 0)
                                <div class="mt-4 ml-8 space-y-4">
                                    @foreach($comment->replies as $reply)
                                    <div class="flex items-start space-x-3">
                                        <img src="{{ $reply->user->avatar_url }}" alt="{{ $reply->user->name }}" class="w-10 h-10 rounded-full">
                                        <div class="flex-1">
                                            <div class="flex items-center mb-1">
                                                <h6 class="font-semibold text-sm mr-2">{{ $reply->user->name }}</h6>
                                                @if($reply->user->isLecturer())
                                                <span class="bg-teal-100 text-teal-800 text-xs px-2 py-0.5 rounded">Instructor</span>
                                                @elseif($reply->user->isAdmin())
                                                <span class="bg-purple-100 text-purple-800 text-xs px-2 py-0.5 rounded">Admin</span>
                                                @endif
                                                <span class="text-xs text-gray-500 ml-auto">{{ $reply->created_at->diffForHumans() }}</span>
                                            </div>
                                            <p class="text-gray-700 text-sm">{{ $reply->comment }}</p>
                                            @if($reply->user_id === auth()->id())
                                            <form action="{{ route('comments.destroy', $reply) }}" method="POST" class="inline mt-2" onsubmit="return confirm('Delete this reply?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-700 text-xs font-medium">
                                                    <i class="fas fa-trash mr-1"></i>Delete
                                                </button>
                                            </form>
                                            @endif
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @empty
                    <p class="text-gray-600 text-center py-8">No comments yet. Be the first to comment!</p>
                    @endforelse
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="lg:col-span-1">
            @if($relatedCourses->count() > 0)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h4 class="font-semibold text-lg mb-4">Related Courses</h4>
                <div class="space-y-4">
                    @foreach($relatedCourses as $relatedCourse)
                    <div class="flex space-x-3">
                        <img src="{{ $relatedCourse->thumbnail_url }}" alt="{{ $relatedCourse->title }}" class="w-16 h-16 rounded object-cover">
                        <div class="flex-1">
                            <h5 class="font-medium text-sm line-clamp-2 mb-1">{{ $relatedCourse->title }}</h5>
                            <p class="text-xs text-gray-600">{{ $relatedCourse->instructor->name }}</p>
                            <div class="flex items-center mt-1">
                                <div class="flex items-center mr-2">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fas fa-star text-xs {{ $i <= $relatedCourse->average_rating ? 'text-yellow-400' : 'text-gray-300' }}"></i>
                                    @endfor
                                </div>
                                <span class="text-xs text-gray-600">({{ $relatedCourse->total_reviews }})</span>
                            </div>
                            <div class="text-sm font-semibold mt-1">
                                {{ $relatedCourse->isFree() ? 'Free' : '$' . $relatedCourse->current_price }}
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
function toggleReplyForm(commentId) {
    const form = document.getElementById('reply-form-' + commentId);
    form.classList.toggle('hidden');
}

document.addEventListener('DOMContentLoaded', function() {
    // Tab functionality
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            
            // Remove active class from all buttons and contents
            tabButtons.forEach(btn => {
                btn.classList.remove('active', 'border-blue-600', 'text-blue-600');
                btn.classList.add('border-transparent', 'text-gray-500');
            });
            
            tabContents.forEach(content => {
                content.classList.add('hidden');
            });
            
            // Add active class to clicked button and show corresponding content
            this.classList.add('active', 'border-blue-600', 'text-blue-600');
            this.classList.remove('border-transparent', 'text-gray-500');
            
            document.getElementById(tabId).classList.remove('hidden');
        });
    });
});
</script>
@endpush
@endsection
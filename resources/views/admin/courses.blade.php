@extends('layouts.app')

@section('title', 'သင်ခန်းစာများ စီမံခန့်ခွဲမှု')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 myanmar-text">သင်ခန်းစာများ စီမံခန့်ခွဲမှု</h1>
                <p class="text-gray-600 mt-2 myanmar-text">ပလပ်ဖောင်းရှိ သင်ခန်းစာများအားလုံးကို စီမံခန့်ခွဲပါ</p>
            </div>
            <div class="flex space-x-4">
                <button onclick="openUploadModal()" class="bg-green-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-green-700 transition-colors myanmar-text">
                    <i class="fas fa-upload mr-2"></i>ဖိုင်အပ်လုဒ်
                </button>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
        <div class="flex flex-wrap items-center gap-4">
            <select class="border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                <option class="myanmar-text">အားလုံး</option>
                <option class="myanmar-text">ထုတ်ဝေပြီး</option>
                <option class="myanmar-text">မူကြမ်း</option>
                <option class="myanmar-text">ဖိုင်ထားသော</option>
            </select>
            <select class="border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                <option class="myanmar-text">အမျိုးအစားအားလုံး</option>
                @foreach(\App\Models\Category::all() as $category)
                <option class="myanmar-text">{{ $category->name }}</option>
                @endforeach
            </select>
            <input type="text" placeholder="ရှာဖွေရန်..." class="border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500 myanmar-text">
        </div>
    </div>

    <!-- Courses Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider myanmar-text">သင်ခန်းစာ</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider myanmar-text">ဆရာ</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider myanmar-text">အမျိုးအစား</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider myanmar-text">ကျောင်းသားများ</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider myanmar-text">အခြေအနေ</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider myanmar-text">ဖန်တီးသည့်ရက်</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider myanmar-text">လုပ်ဆောင်ချက်များ</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($courses as $course)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <img class="h-12 w-12 rounded object-cover" src="{{ $course->thumbnail_url }}" alt="{{ $course->title }}">
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900 myanmar-text">{{ $course->title }}</div>
                                    <div class="text-sm text-gray-500">{{ \App\Helpers\CurrencyHelper::formatMMK($course->price) }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <img class="h-8 w-8 rounded-full" src="{{ $course->instructor->avatar_url }}" alt="{{ $course->instructor->name }}">
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900 myanmar-text">{{ $course->instructor->name }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 myanmar-text">
                            {{ $course->category->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $course->enrollments->where('payment_status', 'completed')->count() }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center space-x-2">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $course->status === 'published' ? 'bg-green-100 text-green-800' : 
                                       ($course->status === 'draft' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }} myanmar-text">
                                    {{ $course->status === 'published' ? 'ထုတ်ဝေပြီး' : 
                                       ($course->status === 'draft' ? 'မူကြမ်း' : 'ဖိုင်ထားသော') }}
                                </span>
                                @if($course->is_featured)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 myanmar-text">
                                    ထူးခြား
                                </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $course->created_at->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('courses.show', $course) }}" target="_blank" class="text-blue-600 hover:text-blue-900">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($course->status === 'draft')
                                <form action="{{ route('admin.courses.approve', $course) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-green-600 hover:text-green-900" title="Approve">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>
                                @endif
                                @if($course->status === 'published')
                                <form action="{{ route('admin.courses.reject', $course) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-yellow-600 hover:text-yellow-900" title="Move to Draft">
                                        <i class="fas fa-pause"></i>
                                    </button>
                                </form>
                                @endif
                                <form action="{{ route('admin.courses.toggle-feature', $course) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-purple-600 hover:text-purple-900" title="Toggle Feature">
                                        <i class="fas {{ $course->is_featured ? 'fa-star' : 'fa-star-o' }}"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if($courses->hasPages())
    <div class="mt-6 flex justify-center">
        {{ $courses->links() }}
    </div>
    @endif
</div>

<!-- Upload Modal -->
<div id="uploadModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 myanmar-text">ဖိုင်အပ်လုဒ်</h3>
                
                <div class="space-y-4">
                    <!-- Image Upload -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 myanmar-text">ပုံအပ်လုဒ်</label>
                        <input type="file" id="imageUpload" accept="image/*" class="w-full border border-gray-300 rounded-md px-3 py-2">
                        <div id="imagePreview" class="mt-2 hidden">
                            <img id="imagePreviewImg" class="w-full h-32 object-cover rounded">
                            <p id="imageUrl" class="text-sm text-gray-600 mt-1 break-all"></p>
                        </div>
                    </div>

                    <!-- Video Upload -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 myanmar-text">ဗီဒီယိုအပ်လုဒ်</label>
                        <input type="file" id="videoUpload" accept="video/*" class="w-full border border-gray-300 rounded-md px-3 py-2">
                        <div id="videoPreview" class="mt-2 hidden">
                            <video id="videoPreviewVideo" class="w-full h-32 object-cover rounded" controls></video>
                            <p id="videoUrl" class="text-sm text-gray-600 mt-1 break-all"></p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeUploadModal()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 myanmar-text">
                        ပိတ်ရန်
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openUploadModal() {
    document.getElementById('uploadModal').classList.remove('hidden');
}

function closeUploadModal() {
    document.getElementById('uploadModal').classList.add('hidden');
    // Reset form
    document.getElementById('imageUpload').value = '';
    document.getElementById('videoUpload').value = '';
    document.getElementById('imagePreview').classList.add('hidden');
    document.getElementById('videoPreview').classList.add('hidden');
}

// Image Upload
document.getElementById('imageUpload').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const formData = new FormData();
        formData.append('image', file);
        
        fetch('{{ route("api.upload.image") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('imagePreviewImg').src = data.url;
                document.getElementById('imageUrl').textContent = data.url;
                document.getElementById('imagePreview').classList.remove('hidden');
                showNotification('Image uploaded successfully!', 'success');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error uploading image', 'error');
        });
    }
});

// Video Upload
document.getElementById('videoUpload').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const formData = new FormData();
        formData.append('video', file);
        
        showNotification('Uploading video... This may take a while.', 'info');
        
        fetch('{{ route("api.upload.video") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('videoPreviewVideo').src = data.url;
                document.getElementById('videoUrl').textContent = data.url;
                document.getElementById('videoPreview').classList.remove('hidden');
                showNotification('Video uploaded successfully!', 'success');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error uploading video', 'error');
        });
    }
});
</script>
@endpush
@endsection
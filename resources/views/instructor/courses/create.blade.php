@extends('layouts.app')

@section('title', 'သင်ခန်းစာအသစ်ဖန်တီးရန်')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 myanmar-text">သင်ခန်းစာအသစ်ဖန်တီးရန်</h1>
        <p class="text-gray-600 mt-2 myanmar-text">သင့်သင်ခန်းစာအသစ်ကို ဖန်တီးပါ</p>
    </div>

    <form action="{{ route('instructor.courses.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
        @csrf

        <!-- Basic Information -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-6 myanmar-text">အခြေခံအချက်အလက်များ</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2 myanmar-text">သင်ခန်းစာခေါင်းစဉ် *</label>
                    <input type="text" name="title" id="title" value="{{ old('title') }}" required
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="သင်ခန်းစာခေါင်းစဉ်ကို ရိုက်ထည့်ပါ">
                    @error('title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-700 mb-2 myanmar-text">အမျိုးအစား *</label>
                    <select name="category_id" id="category_id" required
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="" class="myanmar-text">အမျိုးအစားရွေးချယ်ပါ</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }} class="myanmar-text">
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="level" class="block text-sm font-medium text-gray-700 mb-2 myanmar-text">အဆင့် *</label>
                    <select name="level" id="level" required
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="" class="myanmar-text">အဆင့်ရွေးချယ်ပါ</option>
                        <option value="beginner" {{ old('level') == 'beginner' ? 'selected' : '' }} class="myanmar-text">အစပိုင်း</option>
                        <option value="intermediate" {{ old('level') == 'intermediate' ? 'selected' : '' }} class="myanmar-text">အလယ်အလတ်</option>
                        <option value="advanced" {{ old('level') == 'advanced' ? 'selected' : '' }} class="myanmar-text">အဆင့်မြင့်</option>
                    </select>
                    @error('level')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700 mb-2 myanmar-text">စျေးနှုန်း (USD) *</label>
                    <input type="number" name="price" id="price" value="{{ old('price') }}" step="0.01" min="0" required
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="0.00">
                    @error('price')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="discount_price" class="block text-sm font-medium text-gray-700 mb-2 myanmar-text">လျှော့စျေးနှုန်း (USD)</label>
                    <input type="number" name="discount_price" id="discount_price" value="{{ old('discount_price') }}" step="0.01" min="0"
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="0.00">
                    @error('discount_price')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="language" class="block text-sm font-medium text-gray-700 mb-2 myanmar-text">ဘာသာစကား *</label>
                    <input type="text" name="language" id="language" value="{{ old('language', 'Myanmar') }}" required
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                    @error('language')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-6">
                <label for="short_description" class="block text-sm font-medium text-gray-700 mb-2 myanmar-text">အကျဉ်းချုပ်ဖော်ပြချက်</label>
                <textarea name="short_description" id="short_description" rows="3"
                          class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500"
                          placeholder="သင်ခန်းစာအကြောင်း အကျဉ်းချုပ်ရေးပါ">{{ old('short_description') }}</textarea>
                @error('short_description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-6">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2 myanmar-text">အသေးစိတ်ဖော်ပြချက် *</label>
                <textarea name="description" id="description" rows="6" required
                          class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500"
                          placeholder="သင်ခန်းစာအကြောင်း အသေးစိတ်ရေးပါ">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Media -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-6 myanmar-text">မီဒီယာ</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="thumbnail" class="block text-sm font-medium text-gray-700 mb-2 myanmar-text">သင်ခန်းစာပုံ *</label>
                    <input type="file" name="thumbnail" id="thumbnail" accept="image/*" required
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                    <p class="mt-1 text-sm text-gray-500 myanmar-text">JPG, PNG, GIF ဖိုင်များကို လက်ခံသည် (အများဆုံး 2MB)</p>
                    @error('thumbnail')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="preview_video" class="block text-sm font-medium text-gray-700 mb-2 myanmar-text">နမူနာဗီဒီယို URL</label>
                    <input type="url" name="preview_video" id="preview_video" value="{{ old('preview_video') }}"
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="https://example.com/video.mp4">
                    <p class="mt-1 text-sm text-gray-500 myanmar-text">YouTube, Vimeo သို့မဟုတ် CDN လင့်ခ်</p>
                    @error('preview_video')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Course Content -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-6 myanmar-text">သင်ခန်းစာအကြောင်းအရာ</h2>
            
            <!-- Requirements -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2 myanmar-text">လိုအပ်ချက်များ</label>
                <div id="requirements-container">
                    <div class="flex items-center space-x-2 mb-2">
                        <input type="text" name="requirements[]" 
                               class="flex-1 border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="လိုအပ်ချက်တစ်ခုရေးပါ">
                        <button type="button" onclick="addRequirement()" class="bg-blue-600 text-white px-3 py-2 rounded-md hover:bg-blue-700">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- What You'll Learn -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2 myanmar-text">သင်ယူရရှိမည့်အရာများ</label>
                <div id="learning-container">
                    <div class="flex items-center space-x-2 mb-2">
                        <input type="text" name="what_you_learn[]" 
                               class="flex-1 border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="သင်ယူရရှိမည့်အရာတစ်ခုရေးပါ">
                        <button type="button" onclick="addLearning()" class="bg-blue-600 text-white px-3 py-2 rounded-md hover:bg-blue-700">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit -->
        <div class="flex justify-end space-x-4">
            <a href="{{ route('instructor.courses') }}" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg font-medium hover:bg-gray-400 transition-colors myanmar-text">
                မလုပ်တော့ပါ
            </a>
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-blue-700 transition-colors myanmar-text">
                သင်ခန်းစာဖန်တီးရန်
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function addRequirement() {
    const container = document.getElementById('requirements-container');
    const div = document.createElement('div');
    div.className = 'flex items-center space-x-2 mb-2';
    div.innerHTML = `
        <input type="text" name="requirements[]" 
               class="flex-1 border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500"
               placeholder="လိုအပ်ချက်တစ်ခုရေးပါ">
        <button type="button" onclick="this.parentElement.remove()" class="bg-red-600 text-white px-3 py-2 rounded-md hover:bg-red-700">
            <i class="fas fa-minus"></i>
        </button>
    `;
    container.appendChild(div);
}

function addLearning() {
    const container = document.getElementById('learning-container');
    const div = document.createElement('div');
    div.className = 'flex items-center space-x-2 mb-2';
    div.innerHTML = `
        <input type="text" name="what_you_learn[]" 
               class="flex-1 border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500"
               placeholder="သင်ယူရရှိမည့်အရာတစ်ခုရေးပါ">
        <button type="button" onclick="this.parentElement.remove()" class="bg-red-600 text-white px-3 py-2 rounded-md hover:bg-red-700">
            <i class="fas fa-minus"></i>
        </button>
    `;
    container.appendChild(div);
}
</script>
@endpush
@endsection
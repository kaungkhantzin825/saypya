@extends('layouts.app')

@section('title', 'ပရိုဖိုင်းတည်းဖြတ်ရန်')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 myanmar-text">ပရိုဖိုင်းတည်းဖြတ်ရန်</h1>
        <p class="text-gray-600 mt-2 myanmar-text">သင့်အကောင့်အချက်အလက်များကို အပ်ဒိတ်လုပ်ပါ</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
            @csrf
            @method('patch')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2 myanmar-text">အမည်အပြည့်အစုံ</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2 myanmar-text">အီးမေးလ်လိပ်စာ</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Phone -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2 myanmar-text">ဖုန်းနံပါတ်</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone', $user->phone) }}"
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                    @error('phone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Date of Birth -->
                <div>
                    <label for="date_of_birth" class="block text-sm font-medium text-gray-700 mb-2 myanmar-text">မွေးသက္ကရာဇ်</label>
                    <input type="date" name="date_of_birth" id="date_of_birth" value="{{ old('date_of_birth', $user->date_of_birth?->format('Y-m-d')) }}"
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                    @error('date_of_birth')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Gender -->
                <div>
                    <label for="gender" class="block text-sm font-medium text-gray-700 mb-2 myanmar-text">ကျား/မ</label>
                    <select name="gender" id="gender" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="" class="myanmar-text">ရွေးချယ်ပါ</option>
                        <option value="male" {{ old('gender', $user->gender) === 'male' ? 'selected' : '' }} class="myanmar-text">ကျား</option>
                        <option value="female" {{ old('gender', $user->gender) === 'female' ? 'selected' : '' }} class="myanmar-text">မ</option>
                        <option value="other" {{ old('gender', $user->gender) === 'other' ? 'selected' : '' }} class="myanmar-text">အခြား</option>
                    </select>
                    @error('gender')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Country -->
                <div>
                    <label for="country" class="block text-sm font-medium text-gray-700 mb-2 myanmar-text">နိုင်ငံ</label>
                    <input type="text" name="country" id="country" value="{{ old('country', $user->country) }}"
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                    @error('country')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Bio -->
            <div class="mt-6">
                <label for="bio" class="block text-sm font-medium text-gray-700 mb-2 myanmar-text">အကြောင်းအရာ</label>
                <textarea name="bio" id="bio" rows="4" 
                          class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500"
                          placeholder="သင့်အကြောင်းကို ရေးသားပါ...">{{ old('bio', $user->bio) }}</textarea>
                @error('bio')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Avatar -->
            <div class="mt-6">
                <label for="avatar" class="block text-sm font-medium text-gray-700 mb-2 myanmar-text">ပရိုဖိုင်းပုံ</label>
                <div class="flex items-center space-x-4">
                    @if($user->avatar)
                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-20 h-20 rounded-full object-cover">
                    @else
                        <div class="w-20 h-20 rounded-full bg-gray-200 flex items-center justify-center">
                            <i class="fas fa-user text-gray-400 text-2xl"></i>
                        </div>
                    @endif
                    <div class="flex-1">
                        <input type="file" name="avatar" id="avatar" accept="image/*"
                               class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                        <p class="mt-1 text-sm text-gray-500 myanmar-text">JPG, PNG သို့မဟုတ် GIF (အများဆုံး 2MB)</p>
                        @error('avatar')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="mt-8 flex justify-end">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-blue-700 transition-colors myanmar-text">
                    အပ်ဒိတ်လုပ်ရန်
                </button>
            </div>
        </form>
    </div>

    @if (session('status') === 'profile-updated')
        <div class="mt-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded myanmar-text">
            ပရိုဖိုင်း အပ်ဒိတ်လုပ်ပြီးပါပြီ။
        </div>
    @endif
</div>
@endsection
@extends('layouts.admin')

@section('title', 'Site Settings')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Site Settings</h1>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        @foreach($settings as $group => $groupSettings)
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4 capitalize border-b pb-2">
                    {{ str_replace('_', ' ', $group) }}
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($groupSettings as $setting)
                        <div class="form-group">
                            <label for="{{ $setting->key }}" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ $setting->label }}
                                @if($setting->description)
                                    <span class="text-xs text-gray-500 block mt-1">{{ $setting->description }}</span>
                                @endif
                            </label>

                            @if($setting->type === 'textarea')
                                <textarea 
                                    name="settings[{{ $setting->key }}]" 
                                    id="{{ $setting->key }}"
                                    rows="4"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                >{{ old('settings.' . $setting->key, $setting->value) }}</textarea>
                            @elseif($setting->type === 'number')
                                <input 
                                    type="number" 
                                    name="settings[{{ $setting->key }}]" 
                                    id="{{ $setting->key }}"
                                    value="{{ old('settings.' . $setting->key, $setting->value) }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                >
                            @else
                                <input 
                                    type="text" 
                                    name="settings[{{ $setting->key }}]" 
                                    id="{{ $setting->key }}"
                                    value="{{ old('settings.' . $setting->key, $setting->value) }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                >
                            @endif

                            @error('settings.' . $setting->key)
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        <div class="flex justify-end space-x-4">
            <a href="{{ route('admin.dashboard') }}" class="px-6 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                Save Settings
            </button>
        </div>
    </form>
</div>
@endsection

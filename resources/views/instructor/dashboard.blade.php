@extends('layouts.app')

@section('title', 'ဆရာ Dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 myanmar-text">ဆရာ Dashboard</h1>
                <p class="text-gray-600 mt-2 myanmar-text">သင့်သင်ခန်းစာများနှင့် ကျောင်းသားများကို စီမံခန့်ခွဲပါ</p>
            </div>
            <a href="{{ route('instructor.courses.create') }}" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-blue-700 transition-colors myanmar-text">
                <i class="fas fa-plus mr-2"></i>သင်ခန်းစာအသစ်ဖန်တီးရန်
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 bg-blue-100 rounded-full">
                    <i class="fas fa-book text-blue-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 myanmar-text">သင်ခန်းစာများ</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total_courses'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 bg-green-100 rounded-full">
                    <i class="fas fa-users text-green-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 myanmar-text">ကျောင်းသားများ</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total_students'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 bg-yellow-100 rounded-full">
                    <i class="fas fa-money-bill text-yellow-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 myanmar-text">ဝင်ငွေ</p>
                    <p class="text-2xl font-bold text-gray-900">{{ \App\Helpers\CurrencyHelper::formatMMK($stats['total_revenue']) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 bg-purple-100 rounded-full">
                    <i class="fas fa-star text-purple-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 myanmar-text">ပျမ်းမျှအဆင့်သတ်မှတ်ချက်</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['average_rating'], 1) }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-xl font-bold text-gray-900 mb-4 myanmar-text">သင့်သင်ခန်းစာများ</h2>
        
        @if($courses->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider myanmar-text">သင်ခန်းစာ</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider myanmar-text">ကျောင်းသားများ</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider myanmar-text">အဆင့်သတ်မှတ်ချက်</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider myanmar-text">အခြေအနေ</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider myanmar-text">လုပ်ဆောင်ချက်များ</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($courses as $course)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <img class="h-10 w-10 rounded object-cover" src="{{ $course->thumbnail_url }}" alt="{{ $course->title }}">
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900 myanmar-text">{{ $course->title }}</div>
                                        <div class="text-sm text-gray-500 myanmar-text">{{ $course->category->name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $course->enrollments_count }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="flex items-center">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fas fa-star text-xs {{ $i <= $course->average_rating ? 'text-yellow-400' : 'text-gray-300' }}"></i>
                                    @endfor
                                    <span class="ml-1">({{ $course->reviews_count }})</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $course->status === 'published' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }} myanmar-text">
                                    {{ $course->status === 'published' ? 'ထုတ်ဝေပြီး' : 'မူကြမ်း' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="{{ route('courses.show', $course) }}" class="text-blue-600 hover:text-blue-900 myanmar-text">ကြည့်ရှုရန်</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-8">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-book text-gray-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2 myanmar-text">သင်ခန်းစာများ မရှိသေးပါ</h3>
                <p class="text-gray-600 mb-4 myanmar-text">သင့်ပထမဆုံးသင်ခန်းစာကို ဖန်တီးပါ</p>
                <a href="{{ route('instructor.courses.create') }}" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-blue-700 transition-colors myanmar-text">
                    သင်ခန်းစာဖန်တီးရန်
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
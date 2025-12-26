@extends('layouts.app')

@section('title', 'ကျွန်ုပ်၏သင်ခန်းစာများ')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 myanmar-text">ကျွန်ုပ်၏သင်ခန်းစာများ</h1>
                <p class="text-gray-600 mt-2 myanmar-text">သင့်သင်ခန်းစာများကို စီမံခန့်ခွဲပါ</p>
            </div>
            <a href="{{ route('instructor.courses.create') }}" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-blue-700 transition-colors myanmar-text">
                <i class="fas fa-plus mr-2"></i>သင်ခန်းစာအသစ်ဖန်တီးရန်
            </a>
        </div>
    </div>

    @if($courses->count() > 0)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
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
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <img class="h-12 w-12 rounded object-cover" src="{{ $course->thumbnail_url }}" alt="{{ $course->title }}">
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900 myanmar-text">{{ $course->title }}</div>
                                        <div class="text-sm text-gray-500 myanmar-text">{{ $course->category->name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $course->enrollments->where('payment_status', 'completed')->count() }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="flex items-center">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fas fa-star text-xs {{ $i <= $course->average_rating ? 'text-yellow-400' : 'text-gray-300' }}"></i>
                                    @endfor
                                    <span class="ml-1">({{ $course->reviews->count() }})</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $course->status === 'published' ? 'bg-green-100 text-green-800' : 
                                       ($course->status === 'draft' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }} myanmar-text">
                                    {{ $course->status === 'published' ? 'ထုတ်ဝေပြီး' : 
                                       ($course->status === 'draft' ? 'မူကြမ်း' : 'ဖိုင်ထားသော') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-3">
                                    <a href="{{ route('courses.show', $course) }}" target="_blank" class="text-blue-600 hover:text-blue-900 myanmar-text">
                                        ကြည့်ရှုရန်
                                    </a>
                                    <a href="{{ route('instructor.courses.content', $course) }}" class="text-green-600 hover:text-green-900 myanmar-text">
                                        အကြောင်းအရာ
                                    </a>
                                    <a href="{{ route('instructor.courses.edit', $course) }}" class="text-yellow-600 hover:text-yellow-900 myanmar-text">
                                        တည်းဖြတ်ရန်
                                    </a>
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
    @else
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
            <i class="fas fa-book text-4xl text-gray-400 mb-4"></i>
            <h3 class="text-lg font-semibold text-gray-900 mb-2 myanmar-text">သင်ခန်းစာများ မရှိသေးပါ</h3>
            <p class="text-gray-600 mb-4 myanmar-text">သင့်ပထမဆုံးသင်ခန်းစာကို ဖန်တီးပြီး ကျောင်းသားများကို သင်ကြားခြင်းကို စတင်ပါ</p>
            <a href="{{ route('instructor.courses.create') }}" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-blue-700 transition-colors myanmar-text">
                <i class="fas fa-plus mr-2"></i>သင်ခန်းစာအသစ်ဖန်တီးရန်
            </a>
        </div>
    @endif
</div>
@endsection
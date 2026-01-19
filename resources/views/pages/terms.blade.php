@extends('layouts.app')

@section('title', 'Terms of Service - စည်းကမ်းနှင့် အချက်အလက်များ')

@section('content')
<!-- Hero Section -->
<div class="relative bg-gradient-to-r from-indigo-600 to-purple-600 text-white py-20" style="background-image: url('https://images.unsplash.com/photo-1450101499163-c8848c66ca85?w=1200'); background-size: cover; background-position: center; background-blend-mode: overlay;">
    <div class="absolute inset-0 bg-black opacity-50"></div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-5xl font-bold mb-4 myanmar-text">Terms & Conditions</h1>
        <p class="text-xl myanmar-text">စည်းကမ်းနှင့် အချက်အလက်များ</p>
    </div>
</div>

<!-- Main Content -->
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="bg-white rounded-lg shadow-lg p-8 mb-8">
        <!-- Introduction -->
        <div class="mb-10 bg-indigo-50 border-l-4 border-indigo-500 p-6 rounded-r-lg">
            <p class="text-gray-700 myanmar-text leading-relaxed">
                ဤစည်းကမ်းနှင့် အချက်အလက်များသည် <strong>SanPya Learning အွန်လိုင်းသင်ကြားရေး ပလက်ဖောင်း</strong>ကို အသုံးပြုသော ကျောင်းသားများ၊ သင်တန်းသားများနှင့် အသုံးပြုသူအားလုံးအတွက် လိုက်နာရမည့် စည်းမျဉ်းများဖြစ်ပါသည်။ ပလက်ဖောင်းကို အသုံးပြုခြင်းဖြင့် အောက်ဖော်ပြပါ စည်းကမ်းများအား လက်ခံသဘောတူပါသည်။
            </p>
        </div>

        <!-- 1. Account Registration -->
        <div class="mb-10">
            <h2 class="text-2xl font-bold text-indigo-600 mb-4 myanmar-text border-b-2 border-indigo-200 pb-2">
                <i class="fas fa-user-plus mr-2"></i>1. အကောင့် ဖွင့်ခြင်း (Account Registration)
            </h2>
            
            <div class="bg-gray-50 p-6 rounded-lg">
                <ul class="space-y-3 text-gray-700 myanmar-text">
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-indigo-600 mr-3 mt-1"></i>
                        <span>အသုံးပြုသူများသည် မိမိ၏ <strong>မှန်ကန်သော အချက်အလက်များ</strong>ဖြင့် အကောင့်ဖွင့်ရပါမည်။</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-indigo-600 mr-3 mt-1"></i>
                        <span>အကောင့်လုံခြုံရေးအတွက် <strong>Password</strong> ကို မိမိတာဝန်ယူ၍ ထိန်းသိမ်းရပါမည်။</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- 2. Course Content Usage -->
        <div class="mb-10">
            <h2 class="text-2xl font-bold text-indigo-600 mb-4 myanmar-text border-b-2 border-indigo-200 pb-2">
                <i class="fas fa-book-reader mr-2"></i>2. သင်တန်းအကြောင်းအရာ အသုံးပြုမှု (Course Content Usage)
            </h2>
            
            <div class="bg-yellow-50 border-l-4 border-yellow-500 p-6 rounded-r-lg">
                <p class="text-gray-700 myanmar-text leading-relaxed mb-3">
                    <i class="fas fa-info-circle text-yellow-600 mr-2"></i>
                    သင်တန်းအကြောင်းအရာများကို <strong>ကိုယ်ပိုင်လေ့လာရန်အတွက်သာ</strong> အသုံးပြုခွင့်ရှိပါသည်။
                </p>
                <p class="text-gray-700 myanmar-text leading-relaxed">
                    <i class="fas fa-exclamation-triangle text-red-600 mr-2"></i>
                    ဗီဒီယို၊ PDF၊ Notes၊ Slides များကို မူပိုင်ခွင့်မရှိဘဲ <strong>ပြန်လည်မျှဝေခြင်း၊ ရောင်းချခြင်း မပြုလုပ်ရပါ</strong>။
                </p>
            </div>
        </div>

        <!-- 3. Copyright -->
        <div class="mb-10">
            <h2 class="text-2xl font-bold text-indigo-600 mb-4 myanmar-text border-b-2 border-indigo-200 pb-2">
                <i class="fas fa-copyright mr-2"></i>3. မူပိုင်ခွင့် (Copyright)
            </h2>
            
            <div class="bg-red-50 border-l-4 border-red-500 p-6 rounded-r-lg">
                <p class="text-gray-700 myanmar-text leading-relaxed">
                    <i class="fas fa-shield-alt text-red-600 mr-2"></i>
                    Platform ပေါ်ရှိ အကြောင်းအရာအားလုံးသည် <strong>SanPya Learning ၏ မူပိုင်ပစ္စည်းများ</strong>ဖြစ်ပြီး ခွင့်ပြုချက်မရှိဘဲ အသုံးပြုခြင်းမပြုရပါ။
                </p>
            </div>
        </div>

        <!-- 4. Payment Policy -->
        <div class="mb-10">
            <h2 class="text-2xl font-bold text-indigo-600 mb-4 myanmar-text border-b-2 border-indigo-200 pb-2">
                <i class="fas fa-money-bill-wave mr-2"></i>4. ငွေပေးချေမှု (Payment Policy)
            </h2>
            
            <div class="bg-gray-50 p-6 rounded-lg">
                <ul class="space-y-3 text-gray-700 myanmar-text">
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-green-600 mr-3 mt-1"></i>
                        <span>သင်တန်းကြေးများကို သတ်မှတ်ထားသော နည်းလမ်းများဖြင့် ပေးချေရပါမည်။</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-times-circle text-red-600 mr-3 mt-1"></i>
                        <span>ပေးချေပြီးပါက <strong>ပြန်လည်အမ်းငွေ (Refund) မပြုလုပ်ပါ</strong>။</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- 5. User Conduct -->
        <div class="mb-10">
            <h2 class="text-2xl font-bold text-indigo-600 mb-4 myanmar-text border-b-2 border-indigo-200 pb-2">
                <i class="fas fa-user-check mr-2"></i>5. ကျင့်ဝတ်နှင့် အပြုအမူ (User Conduct)
            </h2>
            
            <div class="bg-orange-50 border-l-4 border-orange-500 p-6 rounded-r-lg">
                <p class="text-gray-700 myanmar-text leading-relaxed mb-3">
                    <strong>အသုံးပြုသူများသည် အောက်ပါတို့ကို မပြုလုပ်ရပါ:</strong>
                </p>
                <ul class="space-y-2 text-gray-700 myanmar-text ml-4">
                    <li class="flex items-start">
                        <i class="fas fa-ban text-red-600 mr-3 mt-1"></i>
                        <span>အခြားသူများကို စော်ကားခြင်း</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-ban text-red-600 mr-3 mt-1"></i>
                        <span>မသင့်လျော်သော စကားများအသုံးပြုခြင်း</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-ban text-red-600 mr-3 mt-1"></i>
                        <span>Spam ပို့ခြင်း</span>
                    </li>
                </ul>
                <p class="text-gray-700 myanmar-text leading-relaxed mt-4">
                    <i class="fas fa-exclamation-triangle text-orange-600 mr-2"></i>
                    စည်းကမ်းဖောက်ဖျက်ပါက <strong>အကောင့်ပိတ်သိမ်းနိုင်ပါသည်</strong>။
                </p>
            </div>
        </div>

        <!-- 6. Privacy Policy -->
        <div class="mb-10">
            <h2 class="text-2xl font-bold text-indigo-600 mb-4 myanmar-text border-b-2 border-indigo-200 pb-2">
                <i class="fas fa-user-shield mr-2"></i>6. ကိုယ်ရေးကိုယ်တာအချက်အလက် (Privacy Policy)
            </h2>
            
            <div class="bg-green-50 p-6 rounded-lg">
                <p class="text-gray-700 myanmar-text leading-relaxed">
                    <i class="fas fa-lock text-green-600 mr-2"></i>
                    အသုံးပြုသူ၏ ကိုယ်ရေးကိုယ်တာအချက်အလက်များကို <strong>လုံခြုံစွာ ထိန်းသိမ်းမည်</strong>ဖြစ်ပြီး ခွင့်ပြုချက်မရှိဘဲ မျှဝေမည်မဟုတ်ပါ။
                </p>
                <a href="{{ route('privacy') }}" class="inline-block mt-3 text-indigo-600 hover:underline">
                    <i class="fas fa-arrow-right mr-2"></i>Privacy Policy အပြည့်အစုံကြည့်ရန်
                </a>
            </div>
        </div>

        <!-- 7. Course Schedule Changes -->
        <div class="mb-10">
            <h2 class="text-2xl font-bold text-indigo-600 mb-4 myanmar-text border-b-2 border-indigo-200 pb-2">
                <i class="fas fa-calendar-alt mr-2"></i>7. သင်တန်းအချိန်နှင့် အပြောင်းအလဲများ
            </h2>
            
            <div class="bg-gray-50 p-6 rounded-lg">
                <p class="text-gray-700 myanmar-text leading-relaxed">
                    <i class="fas fa-sync-alt text-indigo-600 mr-2"></i>
                    သင်တန်းအချိန်ဇယား၊ အကြောင်းအရာများကို Platform မှ လိုအပ်သလို <strong>ပြောင်းလဲနိုင်ပါသည်</strong>။
                </p>
            </div>
        </div>

        <!-- 8. Limitation of Liability -->
        <div class="mb-10">
            <h2 class="text-2xl font-bold text-indigo-600 mb-4 myanmar-text border-b-2 border-indigo-200 pb-2">
                <i class="fas fa-exclamation-circle mr-2"></i>8. တာဝန်ကန့်သတ်ချက် (Limitation of Liability)
            </h2>
            
            <div class="bg-blue-50 border-l-4 border-blue-500 p-6 rounded-r-lg">
                <p class="text-gray-700 myanmar-text leading-relaxed">
                    <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                    နည်းပညာပိုင်းဆိုင်ရာ ပြဿနာများကြောင့် ဖြစ်ပေါ်လာနိုင်သော ထိခိုက်မှုများအတွက် Platform သည် <strong>အပြည့်အဝတာဝန်မယူပါ</strong>။
                </p>
            </div>
        </div>

        <!-- 9. Terms Changes -->
        <div class="mb-10">
            <h2 class="text-2xl font-bold text-indigo-600 mb-4 myanmar-text border-b-2 border-indigo-200 pb-2">
                <i class="fas fa-edit mr-2"></i>9. စည်းကမ်းပြောင်းလဲမှု
            </h2>
            
            <div class="bg-gray-50 p-6 rounded-lg">
                <p class="text-gray-700 myanmar-text leading-relaxed">
                    <i class="fas fa-sync-alt text-indigo-600 mr-2"></i>
                    ဤစည်းကမ်းများကို အချိန်မရွေး ပြင်ဆင်နိုင်ပြီး အသုံးပြုသူများသည် ပြောင်းလဲချက်များကို <strong>လိုက်နာရပါမည်</strong>။
                </p>
            </div>
        </div>

        <!-- 10. Contact Information -->
        <div class="bg-gradient-to-r from-indigo-50 to-purple-50 p-8 rounded-lg border-2 border-indigo-200">
            <h2 class="text-2xl font-bold text-indigo-600 mb-6 myanmar-text text-center">
                <i class="fas fa-phone-alt mr-2"></i>10. ဆက်သွယ်ရန်
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="flex items-start space-x-3">
                    <i class="fas fa-envelope text-indigo-600 text-xl mt-1"></i>
                    <div>
                        <p class="font-semibold text-gray-800">Email:</p>
                        <a href="mailto:sanpyaeducationcentre@gmail.com" class="text-indigo-600 hover:underline">sanpyaeducationcentre@gmail.com</a>
                    </div>
                </div>

                <div class="flex items-start space-x-3">
                    <i class="fab fa-viber text-purple-600 text-xl mt-1"></i>
                    <div>
                        <p class="font-semibold text-gray-800">Phone (Viber):</p>
                        <a href="tel:+959695238273" class="text-indigo-600 hover:underline">+95 9 69523 8273</a>
                    </div>
                </div>

                <div class="flex items-start space-x-3">
                    <i class="fas fa-globe text-indigo-600 text-xl mt-1"></i>
                    <div>
                        <p class="font-semibold text-gray-800">Website:</p>
                        <a href="https://sanpyalearning.com" target="_blank" class="text-indigo-600 hover:underline">sanpyalearning.com</a>
                    </div>
                </div>

                <div class="flex items-start space-x-3">
                    <i class="fab fa-facebook text-blue-600 text-xl mt-1"></i>
                    <div>
                        <p class="font-semibold text-gray-800">Facebook:</p>
                        <a href="https://www.facebook.com/sanpyalearning2017" target="_blank" class="text-indigo-600 hover:underline">facebook.com/sanpyalearning2017</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Acceptance Notice -->
        <div class="mt-8 bg-gradient-to-r from-indigo-100 to-purple-100 p-6 rounded-lg text-center">
            <p class="text-gray-800 myanmar-text font-semibold">
                <i class="fas fa-handshake text-indigo-600 mr-2"></i>
                ပလက်ဖောင်းကို အသုံးပြုခြင်းဖြင့် ဤစည်းကမ်းများကို လက်ခံသဘောတူပါသည်။
            </p>
        </div>

        <!-- Last Updated -->
        <div class="mt-8 text-center text-gray-500 text-sm">
            <p><i class="fas fa-calendar-alt mr-2"></i>Last Updated: {{ date('F d, Y') }}</p>
        </div>
    </div>
</div>
@endsection

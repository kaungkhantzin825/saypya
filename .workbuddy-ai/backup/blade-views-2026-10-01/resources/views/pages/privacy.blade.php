@extends('layouts.app')

@section('title', 'Privacy Policy - ကိုယ်ရေးကိုယ်တာ အချက်အလက် လုံခြုံရေးမူဝါဒ')

@section('content')
<!-- Hero Section -->
<div class="relative bg-gradient-to-r from-purple-600 to-indigo-600 text-white py-20" style="background-image: url('https://images.unsplash.com/photo-1563986768609-322da13575f3?w=1200'); background-size: cover; background-position: center; background-blend-mode: overlay;">
    <div class="absolute inset-0 bg-black opacity-50"></div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-5xl font-bold mb-4 myanmar-text">Privacy Policy</h1>
        <p class="text-xl myanmar-text">ကိုယ်ရေးကိုယ်တာ အချက်အလက် လုံခြုံရေးမူဝါဒ</p>
    </div>
</div>

<!-- Main Content -->
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="bg-white rounded-lg shadow-lg p-8 mb-8">
        <!-- Introduction -->
        <div class="mb-10 bg-blue-50 border-l-4 border-blue-500 p-6 rounded-r-lg">
            <p class="text-gray-700 myanmar-text leading-relaxed">
                ဤ Privacy Policy သည် <strong>SanPya Learning အွန်လိုင်းသင်ကြားရေး ပလက်ဖောင်း</strong>ကို အသုံးပြုသော ကျောင်းသားများ၊ ဆရာ/ဆရာမများ၊ သင်တန်းပို့ချသူများ၏ ကိုယ်ရေးကိုယ်တာ အချက်အလက်များကို မည်သို့ စုဆောင်း၊ အသုံးပြု၊ ထိန်းသိမ်းမည်ကို ရှင်းလင်းဖော်ပြထားခြင်း ဖြစ်ပါသည်။ ပလက်ဖောင်းကို အသုံးပြုခြင်းဖြင့် ဤမူဝါဒကို လက်ခံသဘောတူသည်ဟု သတ်မှတ်ပါသည်။
            </p>
        </div>

        <!-- 1. Information Collection -->
        <div class="mb-10">
            <h2 class="text-2xl font-bold text-gray-900 mb-4 myanmar-text border-b-2 border-gray-300 pb-2">
                <i class="fas fa-database mr-2 text-gray-900"></i>1. စုဆောင်းသော အချက်အလက်များ (Information Data Storing)
            </h2>
            
            <div class="space-y-6">
                <div class="bg-gray-50 p-6 rounded-lg border-l-4 border-teal-500">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3 myanmar-text flex items-center">
                        <i class="fas fa-user-graduate mr-2 text-teal-600"></i>ကျောင်းသားများအတွက်
                    </h3>
                    <ul class="list-disc list-inside space-y-2 text-gray-700 myanmar-text ml-4">
                        <li>အမည်</li>
                        <li>ဖုန်းနံပါတ်</li>
                        <li>Email</li>
                        <li>သင်တန်းတက်ရောက်မှု အချက်အလက်</li>
                        <li>Payment အချက်အလက် (လိုအပ်ပါက)</li>
                    </ul>
                </div>

                <div class="bg-gray-50 p-6 rounded-lg border-l-4 border-blue-500">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3 myanmar-text flex items-center">
                        <i class="fas fa-chalkboard-teacher mr-2 text-blue-600"></i>ဆရာ/ဆရာမများအတွက်
                    </h3>
                    <ul class="list-disc list-inside space-y-2 text-gray-700 myanmar-text ml-4">
                        <li>အမည်</li>
                        <li>ဘွဲ့/အရည်အချင်း</li>
                        <li>သင်ကြားမှု အတွေ့အကြုံ</li>
                        <li>ဆက်သွယ်ရန် အချက်အလက်</li>
                        <li>သင်ကြားသော သင်တန်းအချက်အလက်</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- 2. Purpose of Use -->
        <div class="mb-10">
            <h2 class="text-2xl font-bold text-gray-900 mb-4 myanmar-text border-b-2 border-gray-300 pb-2">
                <i class="fas fa-bullseye mr-2 text-gray-900"></i>2. အချက်အလက်အသုံးပြုရည်ရွယ်ချက်
            </h2>
            
            <div class="bg-gray-50 p-6 rounded-lg">
                <ul class="space-y-3 text-gray-700 myanmar-text">
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-green-600 mr-3 mt-1"></i>
                        <span>အကောင့်ဖွင့်ခြင်းနှင့် စီမံခန့်ခွဲခြင်း</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-green-600 mr-3 mt-1"></i>
                        <span>သင်တန်းများ ပေးပို့ခြင်း</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-green-600 mr-3 mt-1"></i>
                        <span>သင်ကြားရေး ဆက်သွယ်မှုများ</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-green-600 mr-3 mt-1"></i>
                        <span>ငွေပေးချေမှု စီမံခန့်ခွဲခြင်း</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-green-600 mr-3 mt-1"></i>
                        <span>ပလက်ဖောင်း တိုးတက်ရေး</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- 3. Information Sharing -->
        <div class="mb-10">
            <h2 class="text-2xl font-bold text-gray-900 mb-4 myanmar-text border-b-2 border-gray-300 pb-2">
                <i class="fas fa-share-alt mr-2 text-gray-900"></i>3. အချက်အလက် မျှဝေမှု (Confidential Info Sharing)
            </h2>
            
            <div class="bg-yellow-50 border-l-4 border-yellow-500 p-6 rounded-r-lg">
                <p class="text-gray-700 myanmar-text leading-relaxed mb-3">
                    <i class="fas fa-shield-alt text-yellow-600 mr-2"></i>
                    သင်၏ ကိုယ်ရေးကိုယ်တာအချက်အလက်များကို သင်၏ ခွင့်ပြုချက်မရှိဘဲ အခြားသူများနှင့် <strong>မမျှဝေပါ</strong>။
                </p>
                <p class="text-gray-700 myanmar-text leading-relaxed">
                    ဥပဒေအရ တောင်းဆိုပါက သို့မဟုတ် နည်းပညာပိုင်းဆိုင်ရာ ဝန်ဆောင်မှုပေးသူများနှင့်သာ အကန့်အသတ်ရှိစွာ မျှဝေနိုင်ပါသည်။
                </p>
            </div>
        </div>

        <!-- 4. Security -->
        <div class="mb-10">
            <h2 class="text-2xl font-bold text-gray-900 mb-4 myanmar-text border-b-2 border-gray-300 pb-2">
                <i class="fas fa-lock mr-2 text-gray-900"></i>4. လုံခြုံရေး (Security Concern)
            </h2>
            
            <div class="bg-green-50 p-6 rounded-lg">
                <p class="text-gray-700 myanmar-text leading-relaxed mb-4">
                    သင်၏ အချက်အလက်များကို လုံခြုံစွာ ထိန်းသိမ်းထားပါသည်:
                </p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-white p-4 rounded-lg text-center">
                        <i class="fas fa-key text-3xl text-green-600 mb-2"></i>
                        <p class="font-semibold text-gray-800">Password Protection</p>
                    </div>
                    <div class="bg-white p-4 rounded-lg text-center">
                        <i class="fas fa-server text-3xl text-green-600 mb-2"></i>
                        <p class="font-semibold text-gray-800">Secure Server</p>
                    </div>
                    <div class="bg-white p-4 rounded-lg text-center">
                        <i class="fas fa-user-shield text-3xl text-green-600 mb-2"></i>
                        <p class="font-semibold text-gray-800">Access Control</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 5. Account Security Responsibility -->
        <div class="mb-10">
            <h2 class="text-2xl font-bold text-gray-900 mb-4 myanmar-text border-b-2 border-gray-300 pb-2">
                <i class="fas fa-user-lock mr-2 text-gray-900"></i>5. အကောင့် လုံခြုံရေး တာဝန်
            </h2>
            
            <div class="bg-red-50 border-l-4 border-red-500 p-6 rounded-r-lg">
                <p class="text-gray-700 myanmar-text leading-relaxed">
                    <i class="fas fa-exclamation-triangle text-red-600 mr-2"></i>
                    အသုံးပြုသူများသည် မိမိ၏ <strong>Password</strong> နှင့် <strong>Login အချက်အလက်များ</strong>ကို မိမိတာဝန်ယူ၍ ထိန်းသိမ်းရပါမည်။
                </p>
            </div>
        </div>

        <!-- 6. Data Modification/Deletion -->
        <div class="mb-10">
            <h2 class="text-2xl font-bold text-gray-900 mb-4 myanmar-text border-b-2 border-gray-300 pb-2">
                <i class="fas fa-edit mr-2 text-gray-900"></i>6. အချက်အလက် ပြင်ဆင်ခြင်း / ဖျက်သိမ်းခြင်း
            </h2>
            
            <div class="bg-gray-50 p-6 rounded-lg">
                <p class="text-gray-700 myanmar-text leading-relaxed">
                    အသုံးပြုသူများသည် မိမိ၏ ကိုယ်ရေးကိုယ်တာအချက်အလက်များကို ပြင်ဆင်ခြင်း၊ ဖျက်သိမ်းခြင်းအတွက် Platform ကို ဆက်သွယ်နိုင်ပါသည်။
                </p>
            </div>
        </div>

        <!-- 7. Children's Information -->
        <div class="mb-10">
            <h2 class="text-2xl font-bold text-gray-900 mb-4 myanmar-text border-b-2 border-gray-300 pb-2">
                <i class="fas fa-child mr-2 text-gray-900"></i>7. ကလေးသူငယ်များ၏ အချက်အလက်
            </h2>
            
            <div class="bg-blue-50 border-l-4 border-blue-500 p-6 rounded-r-lg">
                <p class="text-gray-700 myanmar-text leading-relaxed">
                    <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                    အသက် <strong>18 နှစ်အောက်</strong> ကျောင်းသားများအတွက် <strong>မိဘ/အုပ်ထိန်းသူ၏ ခွင့်ပြုချက်</strong>ဖြင့်သာ အကောင့်ဖွင့်ခွင့်ရှိပါသည်။
                </p>
            </div>
        </div>

        <!-- 8. Policy Changes -->
        <div class="mb-10">
            <h2 class="text-2xl font-bold text-gray-900 mb-4 myanmar-text border-b-2 border-gray-300 pb-2">
                <i class="fas fa-sync-alt mr-2 text-gray-900"></i>8. Privacy Policy ပြောင်းလဲမှု
            </h2>
            
            <div class="bg-gray-50 p-6 rounded-lg">
                <p class="text-gray-700 myanmar-text leading-relaxed">
                    ဤမူဝါဒကို အချိန်မရွေး ပြင်ဆင်နိုင်ပြီး ပြောင်းလဲချက်များကို Platform ပေါ်တွင် ကြေညာမည်ဖြစ်ပါသည်။
                </p>
            </div>
        </div>

        <!-- 9. Contact Information -->
        <div class="bg-gradient-to-r from-gray-50 to-gray-100 p-8 rounded-lg border-2 border-gray-300">
            <h2 class="text-2xl font-bold text-gray-900 mb-6 myanmar-text text-center">
                <i class="fas fa-phone-alt mr-2 text-gray-900"></i>9. ဆက်သွယ်ရန်
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="flex items-start space-x-3">
                    <i class="fas fa-envelope text-gray-900 text-xl mt-1"></i>
                    <div>
                        <p class="font-semibold text-gray-800">Email:</p>
                        <a href="mailto:sanpyaeducationcentre@gmail.com" class="text-gray-900 hover:underline">sanpyaeducationcentre@gmail.com</a>
                    </div>
                </div>

                <div class="flex items-start space-x-3">
                    <i class="fab fa-viber text-gray-900 text-xl mt-1"></i>
                    <div>
                        <p class="font-semibold text-gray-800">Phone (Viber):</p>
                        <a href="tel:+959695238273" class="text-gray-900 hover:underline">+95 9 69523 8273</a>
                    </div>
                </div>

                <div class="flex items-start space-x-3">
                    <i class="fas fa-globe text-gray-900 text-xl mt-1"></i>
                    <div>
                        <p class="font-semibold text-gray-800">Website:</p>
                        <a href="https://sanpyalearning.com" target="_blank" class="text-gray-900 hover:underline">sanpyalearning.com</a>
                    </div>
                </div>

                <div class="flex items-start space-x-3">
                    <i class="fab fa-facebook text-blue-600 text-xl mt-1"></i>
                    <div>
                        <p class="font-semibold text-gray-800">Facebook:</p>
                        <a href="https://www.facebook.com/sanpyalearning2017" target="_blank" class="text-gray-900 hover:underline">facebook.com/sanpyalearning2017</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Last Updated -->
        <div class="mt-8 text-center text-gray-500 text-sm">
            <p><i class="fas fa-calendar-alt mr-2"></i>Last Updated: {{ date('F d, Y') }}</p>
        </div>
    </div>
</div>
@endsection

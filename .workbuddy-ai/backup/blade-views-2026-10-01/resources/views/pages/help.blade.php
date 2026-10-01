@extends('layouts.app')

@section('title', 'Help Centre - အကူအညီစင်တာ')

@section('content')
<!-- Hero Section -->
<div class="relative bg-gradient-to-r from-teal-600 to-blue-600 text-white py-20" style="background-image: url('https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=1200'); background-size: cover; background-position: center; background-blend-mode: overlay;">
    <div class="absolute inset-0 bg-black opacity-50"></div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-5xl font-bold mb-4 myanmar-text">Help Centre (အကူအညီစင်တာ)</h1>
        <p class="text-xl myanmar-text">SanPya Learning အွန်လိုင်းသင်ကြားရေး ပလက်ဖောင်း</p>
    </div>
</div>

<!-- Main Content -->
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="bg-white rounded-lg shadow-lg p-8 mb-8">
        <p class="text-gray-700 mb-8 myanmar-text leading-relaxed">
            SanPya Learning Help Centre မှာ ကျောင်းသားများနှင့် ဆရာ/ဆရာမများအတွက် ပလက်ဖောင်းအသုံးပြုနည်း၊ အကောင့်ပြဿနာများ၊ သင်တန်းဆိုင်ရာ မေးခွန်းများကို ကူညီပေးရန် ရည်ရွယ်ထားပါသည်။
        </p>

        <!-- Account Help -->
        <div class="mb-10">
            <h2 class="text-2xl font-bold text-teal-600 mb-4 myanmar-text border-b-2 border-teal-200 pb-2">
                <i class="fas fa-user-circle mr-2"></i>အကောင့်ဆိုင်ရာ အကူအညီ (Account Opening)
            </h2>
            
            <div class="space-y-6">
                <div class="bg-gray-50 p-6 rounded-lg">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3 myanmar-text">1. အကောင့်ဖွင့်နည်း</h3>
                    <ul class="list-disc list-inside space-y-2 text-gray-700 myanmar-text ml-4">
                        <li>(www.sanpyalearning.com) - Website / App ထဲဝင်ပါ</li>
                        <li>"Register / Sign Up" ကိုနှိပ်ပါ → <a href="{{ route('register') }}" class="text-teal-600 hover:underline">{{ route('register') }}</a></li>
                        <li>အမည်၊ Email၊ ဖုန်းနံပါတ် ဖြည့်ပါ</li>
                        <li>Password သတ်မှတ်ပြီး အကောင့်ဖွင့်ပါ</li>
                        <li>Approve ဖြစ်ရင် စသုံးလို့ရပါပြီ</li>
                    </ul>
                </div>

                <div class="bg-gray-50 p-6 rounded-lg">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3 myanmar-text">2. Password မေ့သွားပါက</h3>
                    <ul class="list-disc list-inside space-y-2 text-gray-700 myanmar-text ml-4">
                        <li>"Forgot Password" ကိုနှိပ်ပါ</li>
                        <li>Email / Phone ဖြည့်ပါ</li>
                        <li>Reset Link ဖြင့် Password အသစ်ပြန်သတ်မှတ်ပါ</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Classes -->
        <div class="mb-10">
            <h2 class="text-2xl font-bold text-teal-600 mb-4 myanmar-text border-b-2 border-teal-200 pb-2">
                <i class="fas fa-graduation-cap mr-2"></i>သင်တန်းတက်ရောက်မှုဆိုင်ရာ (For Classes)
            </h2>
            
            <div class="space-y-6">
                <div class="bg-gray-50 p-6 rounded-lg">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3 myanmar-text">3. သင်တန်းဘယ်လိုတက်ရမလဲ?</h3>
                    <ul class="list-disc list-inside space-y-2 text-gray-700 myanmar-text ml-4">
                        <li>Login ဝင်ပါ</li>
                        <li>"My Courses" ကိုနှိပ်ပါ</li>
                        <li>Video / Notes / Live Class များကို လေ့လာနိုင်ပါသည်</li>
                    </ul>
                </div>

                <div class="bg-gray-50 p-6 rounded-lg">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3 myanmar-text">4. သင်တန်းမဖွင့်ရင် ဘာလုပ်ရမလဲ?</h3>
                    <ul class="list-disc list-inside space-y-2 text-gray-700 myanmar-text ml-4">
                        <li>Internet Connection စစ်ပါ</li>
                        <li>Browser / App Update လုပ်ပါ</li>
                        <li>မရသေးပါက Help Centre ကို ဆက်သွယ်ပါ</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Payment -->
        <div class="mb-10">
            <h2 class="text-2xl font-bold text-teal-600 mb-4 myanmar-text border-b-2 border-teal-200 pb-2">
                <i class="fas fa-credit-card mr-2"></i>ငွေပေးချေမှု (Payment)
            </h2>
            
            <div class="space-y-6">
                <div class="bg-gray-50 p-6 rounded-lg">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3 myanmar-text">5. ဘယ်လို Payment လုပ်ရမလဲ?</h3>
                    <ul class="list-disc list-inside space-y-2 text-gray-700 myanmar-text ml-4">
                        <li>KBZPay / AyaPay / Bank Transfer</li>
                        <li>(Viber-09695238273) ကို Screenshot ပို့ပေးရပါမည်</li>
                    </ul>
                </div>

                <div class="bg-gray-50 p-6 rounded-lg">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3 myanmar-text">6. Payment ပြီးပေမယ့် သင်တန်းမဖွင့်ရင်?</h3>
                    <ul class="list-disc list-inside space-y-2 text-gray-700 myanmar-text ml-4">
                        <li>Payment Screenshot ပို့ပါ</li>
                        <li>Admin Team မှ စစ်ဆေးပြီး Activate လုပ်ပေးပါမည်</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- For Teachers -->
        <div class="mb-10">
            <h2 class="text-2xl font-bold text-teal-600 mb-4 myanmar-text border-b-2 border-teal-200 pb-2">
                <i class="fas fa-chalkboard-teacher mr-2"></i>ဆရာ/ဆရာမများအတွက်
            </h2>
            
            <div class="space-y-6">
                <div class="bg-gray-50 p-6 rounded-lg">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3 myanmar-text">7. သင်တန်းဘယ်လိုတင်ရမလဲ?</h3>
                    <ul class="list-disc list-inside space-y-2 text-gray-700 myanmar-text ml-4">
                        <li>Admin ကို ဆက်သွယ်ပါ</li>
                        <li>Course Outline / Video / Notes ပို့ပါ</li>
                        <li>Review ပြီးပါက Platform ပေါ်တင်ပေးပါမည်</li>
                    </ul>
                </div>

                <div class="bg-gray-50 p-6 rounded-lg">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3 myanmar-text">8. Student မေးခွန်းတွေကို ဘယ်လိုဖြေမလဲ?</h3>
                    <ul class="list-disc list-inside space-y-2 text-gray-700 myanmar-text ml-4">
                        <li>Comment / Chat / Live Class မှ ဖြေဆိုနိုင်ပါသည်</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Technical Support -->
        <div class="mb-10">
            <h2 class="text-2xl font-bold text-teal-600 mb-4 myanmar-text border-b-2 border-teal-200 pb-2">
                <i class="fas fa-tools mr-2"></i>နည်းပညာပိုင်းဆိုင်ရာ (Technical Support)
            </h2>
            
            <div class="space-y-6">
                <div class="bg-gray-50 p-6 rounded-lg">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3 myanmar-text">9. Video မဖွင့်ရင်?</h3>
                    <ul class="list-disc list-inside space-y-2 text-gray-700 myanmar-text ml-4">
                        <li>Internet Speed စစ်ပါ</li>
                        <li>Browser Change လုပ်ကြည့်ပါ</li>
                        <li>App Update လုပ်ပါ</li>
                    </ul>
                </div>

                <div class="bg-gray-50 p-6 rounded-lg">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3 myanmar-text">10. App / Website Error ဖြစ်ရင်?</h3>
                    <ul class="list-disc list-inside space-y-2 text-gray-700 myanmar-text ml-4">
                        <li>Screenshot ယူပါ</li>
                        <li>Help Centre ကို ပို့ပါ</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="bg-gradient-to-r from-teal-50 to-blue-50 p-8 rounded-lg border-2 border-teal-200">
            <h2 class="text-2xl font-bold text-teal-600 mb-6 myanmar-text text-center">
                <i class="fas fa-phone-alt mr-2"></i>ဆက်သွယ်ရန်
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="flex items-start space-x-3">
                    <i class="fas fa-envelope text-teal-600 text-xl mt-1"></i>
                    <div>
                        <p class="font-semibold text-gray-800">Email:</p>
                        <a href="mailto:sanpyaeducationcentre@gmail.com" class="text-teal-600 hover:underline">sanpyaeducationcentre@gmail.com</a>
                    </div>
                </div>

                <div class="flex items-start space-x-3">
                    <i class="fab fa-viber text-purple-600 text-xl mt-1"></i>
                    <div>
                        <p class="font-semibold text-gray-800">Phone (Viber):</p>
                        <a href="tel:+959695238273" class="text-teal-600 hover:underline">+95 9 69523 8273</a>
                    </div>
                </div>

                <div class="flex items-start space-x-3">
                    <i class="fas fa-globe text-teal-600 text-xl mt-1"></i>
                    <div>
                        <p class="font-semibold text-gray-800">Website:</p>
                        <a href="https://sanpyalearning.com" target="_blank" class="text-teal-600 hover:underline">sanpyalearning.com</a>
                    </div>
                </div>

                <div class="flex items-start space-x-3">
                    <i class="fab fa-facebook text-blue-600 text-xl mt-1"></i>
                    <div>
                        <p class="font-semibold text-gray-800">Facebook:</p>
                        <a href="https://www.facebook.com/sanpyalearning2017" target="_blank" class="text-teal-600 hover:underline">facebook.com/sanpyalearning2017</a>
                    </div>
                </div>
            </div>

            <div class="mt-6 text-center bg-white p-4 rounded-lg">
                <p class="font-semibold text-gray-800 mb-2 myanmar-text">
                    <i class="fas fa-clock mr-2 text-teal-600"></i>Help Centre Opening Time
                </p>
                <p class="text-gray-700 myanmar-text">နေ့စဉ် မနက် 9:00 မှ ည 9:00 အထိ</p>
            </div>
        </div>
    </div>
</div>
@endsection

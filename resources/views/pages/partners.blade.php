@extends('layouts.app')

@section('title', 'Our Partners')

@section('content')
<!-- Hero -->
<section class="bg-gradient-to-r from-teal-600 to-teal-700 text-white py-16" style="background-image: url('https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=1920&h=400&fit=crop'); background-size: cover; background-position: center; position: relative;">
    <div style="position: absolute; inset: 0; background: linear-gradient(135deg, rgba(13, 148, 136, 0.9) 0%, rgba(6, 182, 212, 0.85) 100%);"></div>
    <div class="max-w-7xl mx-auto px-4 text-center relative z-10">
        <h1 class="text-4xl font-bold mb-4">Our Partners</h1>
        <p class="text-xl text-teal-100">Working together to transform education</p>
    </div>
</section>

<!-- Partners Grid -->
<section class="py-16">
    <div class="max-w-7xl mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold mb-4">Trusted Partners</h2>
            <p class="text-gray-600">We collaborate with leading organizations to bring you the best learning experience.</p>
        </div>
        
        <div class="flex flex-wrap justify-center items-stretch gap-8">
            <!-- Partner 1 - 3 Education -->
            <div class="bg-white p-8 rounded-lg shadow-lg hover:shadow-xl transition flex-1 min-w-[280px] max-w-[350px]">
                <div class="flex items-center justify-center mb-6" style="height: 180px;">
                    <img src="{{ asset('images/logo-6ZHKaEM-.png') }}" alt="3 Education" style="max-width: 100%; max-height: 180px; object-fit: contain;">
                </div>
                <div class="text-center">
                    <h3 class="font-bold text-xl mb-2">Edu Gamekabar</h3>
                    <p class="text-gray-600 text-sm">Educational Partner</p>
                </div>
            </div>
            
            <!-- Partner 2 - MM Certify -->
            <div class="bg-white p-8 rounded-lg shadow-lg hover:shadow-xl transition flex-1 min-w-[280px] max-w-[350px]">
                <div class="flex items-center justify-center mb-6" style="height: 180px;">
                    <img src="{{ asset('images/mmlogo.png') }}" alt="MM Certify" style="max-width: 100%; max-height: 180px; object-fit: contain;">
                </div>
                <div class="text-center">
                    <h3 class="font-bold text-xl mb-2">MM Certify</h3>
                    <p class="text-gray-600 text-sm">Certification Partner</p>
                </div>
            </div>
            
            <!-- Partner 3 - Edu Gamekabar -->
            <div class="bg-white p-8 rounded-lg shadow-lg hover:shadow-xl transition flex-1 min-w-[280px] max-w-[350px]">
                <div class="flex items-center justify-center mb-6" style="height: 180px;">
                    <img src="{{ asset('images/images.png') }}" alt="Edu Gamekabar" style="max-width: 100%; max-height: 180px; object-fit: contain;">
                </div>
                <div class="text-center">
                    <h3 class="font-bold text-xl mb-2"> 3 Education</h3>
                    <p class="text-gray-600 text-sm">Educational Partner</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Partnership Benefits -->
<!-- <section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold mb-4">Partnership Benefits</h2>
        </div>
        
        <div class="grid md:grid-cols-3 gap-8">
            <div class="text-center">
                <div class="w-16 h-16 bg-teal-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-users text-2xl text-white"></i>
                </div>
                <h3 class="font-bold mb-2">Access to Talent</h3>
                <p class="text-gray-600">Connect with skilled learners ready for opportunities.</p>
            </div>
            <div class="text-center">
                <div class="w-16 h-16 bg-cyan-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-certificate text-2xl text-white"></i>
                </div>
                <h3 class="font-bold mb-2">Co-branded Certificates</h3>
                <p class="text-gray-600">Issue certificates with your organization's branding.</p>
            </div>
            <div class="text-center">
                <div class="w-16 h-16 bg-purple-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-chart-line text-2xl text-white"></i>
                </div>
                <h3 class="font-bold mb-2">Brand Visibility</h3>
                <p class="text-gray-600">Increase your reach in the education sector.</p>
            </div>
        </div>
    </div>
</section> -->

<!-- Become a Partner -->
<section class="py-16">
    <div class="max-w-4xl mx-auto px-4 text-center">
        <h2 class="text-3xl font-bold mb-4">Become a Partner</h2>
        <p class="text-gray-600 mb-8">Interested in partnering with Sanpya Academy? Let's discuss how we can work together.</p>
        <a href="{{ route('contact') }}" class="btn-3d btn-3d-cyan">Contact Us</a>
    </div>
</section>
@endsection

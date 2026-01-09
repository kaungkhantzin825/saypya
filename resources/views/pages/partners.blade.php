@extends('layouts.app')

@section('title', 'Our Partners')

@section('content')
<!-- Hero -->
<section class="bg-gradient-to-r from-teal-600 to-teal-700 text-white py-16">
    <div class="max-w-7xl mx-auto px-4 text-center">
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
        
        <div class="grid md:grid-cols-4 gap-8">
            <!-- Partner 1 -->
            <div class="bg-white p-8 rounded-lg shadow-lg text-center hover:shadow-xl transition">
                <div class="w-20 h-20 bg-teal-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-university text-3xl text-teal-600"></i>
                </div>
                <h3 class="font-bold mb-2">Tech University</h3>
                <p class="text-gray-600 text-sm">Academic Partner</p>
            </div>
            
            <!-- Partner 2 -->
            <div class="bg-white p-8 rounded-lg shadow-lg text-center hover:shadow-xl transition">
                <div class="w-20 h-20 bg-cyan-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-building text-3xl text-cyan-600"></i>
                </div>
                <h3 class="font-bold mb-2">Digital Corp</h3>
                <p class="text-gray-600 text-sm">Industry Partner</p>
            </div>
            
            <!-- Partner 3 -->
            <div class="bg-white p-8 rounded-lg shadow-lg text-center hover:shadow-xl transition">
                <div class="w-20 h-20 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-laptop-code text-3xl text-purple-600"></i>
                </div>
                <h3 class="font-bold mb-2">Code Academy</h3>
                <p class="text-gray-600 text-sm">Content Partner</p>
            </div>
            
            <!-- Partner 4 -->
            <div class="bg-white p-8 rounded-lg shadow-lg text-center hover:shadow-xl transition">
                <div class="w-20 h-20 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-handshake text-3xl text-orange-600"></i>
                </div>
                <h3 class="font-bold mb-2">Edu Foundation</h3>
                <p class="text-gray-600 text-sm">NGO Partner</p>
            </div>
        </div>
    </div>
</section>

<!-- Partnership Benefits -->
<section class="py-16 bg-gray-50">
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
</section>

<!-- Become a Partner -->
<section class="py-16">
    <div class="max-w-4xl mx-auto px-4 text-center">
        <h2 class="text-3xl font-bold mb-4">Become a Partner</h2>
        <p class="text-gray-600 mb-8">Interested in partnering with Sanpya Academy? Let's discuss how we can work together.</p>
        <a href="{{ route('contact') }}" class="btn-3d btn-3d-cyan">Contact Us</a>
    </div>
</section>
@endsection

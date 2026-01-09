@extends('layouts.app')

@section('title', 'Our Team')

@section('content')
<!-- Hero -->
<section class="bg-gradient-to-r from-teal-600 to-teal-700 text-white py-16">
    <div class="max-w-7xl mx-auto px-4 text-center">
        <h1 class="text-4xl font-bold mb-4">Our Team</h1>
        <p class="text-xl text-teal-100">Meet the people behind Sanpya Academy</p>
    </div>
</section>

<!-- Team Members -->
<section class="py-16">
    <div class="max-w-7xl mx-auto px-4">
        <div class="grid md:grid-cols-3 gap-8">
            <!-- Team Member 1 -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden text-center">
                <img src="https://ui-avatars.com/api/?name=Aung+Kyaw&size=200&background=0d9488&color=fff" alt="Team Member" class="w-full h-48 object-cover">
                <div class="p-6">
                    <h3 class="text-xl font-bold mb-1">Aung Kyaw</h3>
                    <p class="text-teal-600 mb-3">Founder & CEO</p>
                    <p class="text-gray-600 text-sm">Passionate about education and technology, leading Sanpya Academy's vision.</p>
                    <div class="flex justify-center space-x-3 mt-4">
                        <a href="#" class="text-gray-400 hover:text-teal-600"><i class="fab fa-linkedin"></i></a>
                        <a href="#" class="text-gray-400 hover:text-teal-600"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
            </div>
            
            <!-- Team Member 2 -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden text-center">
                <img src="https://ui-avatars.com/api/?name=Su+Mon&size=200&background=06b6d4&color=fff" alt="Team Member" class="w-full h-48 object-cover">
                <div class="p-6">
                    <h3 class="text-xl font-bold mb-1">Su Mon</h3>
                    <p class="text-teal-600 mb-3">Head of Education</p>
                    <p class="text-gray-600 text-sm">Ensuring quality content and curriculum development for all courses.</p>
                    <div class="flex justify-center space-x-3 mt-4">
                        <a href="#" class="text-gray-400 hover:text-teal-600"><i class="fab fa-linkedin"></i></a>
                        <a href="#" class="text-gray-400 hover:text-teal-600"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
            </div>
            
            <!-- Team Member 3 -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden text-center">
                <img src="https://ui-avatars.com/api/?name=Min+Thu&size=200&background=8b5cf6&color=fff" alt="Team Member" class="w-full h-48 object-cover">
                <div class="p-6">
                    <h3 class="text-xl font-bold mb-1">Min Thu</h3>
                    <p class="text-teal-600 mb-3">Technical Lead</p>
                    <p class="text-gray-600 text-sm">Building and maintaining the platform's technical infrastructure.</p>
                    <div class="flex justify-center space-x-3 mt-4">
                        <a href="#" class="text-gray-400 hover:text-teal-600"><i class="fab fa-linkedin"></i></a>
                        <a href="#" class="text-gray-400 hover:text-teal-600"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Join Our Team -->
<section class="py-16 bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 text-center">
        <h2 class="text-3xl font-bold mb-4">Join Our Team</h2>
        <p class="text-gray-600 mb-8">We're always looking for talented individuals who share our passion for education.</p>
        <a href="{{ route('contact') }}" class="btn-3d btn-3d-cyan">Get in Touch</a>
    </div>
</section>
@endsection

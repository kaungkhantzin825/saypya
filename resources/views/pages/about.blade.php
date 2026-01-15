@extends('layouts.app')

@section('title', 'About Us')

@section('content')
<!-- Hero -->
<section class="bg-gradient-to-r from-teal-600 to-teal-700 text-white py-16" style="background-image: url('https://images.unsplash.com/photo-1523240795612-9a054b0db644?w=1920&h=400&fit=crop'); background-size: cover; background-position: center; position: relative;">
    <div style="position: absolute; inset: 0; background: linear-gradient(135deg, rgba(13, 148, 136, 0.9) 0%, rgba(6, 182, 212, 0.85) 100%);"></div>
    <div class="max-w-7xl mx-auto px-4 text-center relative z-10">
        <h1 class="text-4xl font-bold mb-4">About Sanpya Academy</h1>
        <p class="text-xl text-teal-100">Empowering learners with quality online education</p>
    </div>
</section>

<!-- Our Story -->
<section class="py-16">
    <div class="max-w-4xl mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-8">Our Story</h2>
        <div class="prose prose-lg mx-auto text-gray-600">
            <p class="mb-4">Sanpya Academy was founded with a simple mission: to make quality education accessible to everyone in Myanmar and beyond.</p>
            <p class="mb-4">We believe that education is the key to unlocking potential and creating opportunities. Our platform brings together expert instructors and eager learners, creating a community dedicated to growth and learning.</p>
            <p>Whether you're looking to advance your career, learn a new skill, or explore a passion, Sanpya Academy is here to support your journey.</p>
        </div>
    </div>
</section>

<!-- Stats -->
<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
            <div>
                <div class="text-4xl font-bold text-teal-600">1000+</div>
                <div class="text-gray-600">Students</div>
            </div>
            <div>
                <div class="text-4xl font-bold text-teal-600">50+</div>
                <div class="text-gray-600">Courses</div>
            </div>
            <div>
                <div class="text-4xl font-bold text-teal-600">20+</div>
                <div class="text-gray-600">Instructors</div>
            </div>
            <div>
                <div class="text-4xl font-bold text-teal-600">5+</div>
                <div class="text-gray-600">Partners</div>
            </div>
        </div>
    </div>
</section>

<!-- Mission & Vision -->
<section class="py-16">
    <div class="max-w-7xl mx-auto px-4">
        <div class="grid md:grid-cols-2 gap-12">
            <div class="bg-white p-8 rounded-lg shadow-lg border-t-4 border-teal-500">
                <h3 class="text-2xl font-bold mb-4">Our Mission</h3>
                <p class="text-gray-600">To provide accessible, high-quality education that empowers individuals to achieve their personal and professional goals.</p>
            </div>
            <div class="bg-white p-8 rounded-lg shadow-lg border-t-4 border-cyan-500">
                <h3 class="text-2xl font-bold mb-4">Our Vision</h3>
                <p class="text-gray-600">To be the leading online learning platform in Myanmar, transforming lives through education and creating a community of lifelong learners.</p>
            </div>
        </div>
    </div>
</section>
@endsection

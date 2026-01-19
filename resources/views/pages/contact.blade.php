@extends('layouts.app')

@section('title', 'Contact Us')

@section('content')
<!-- Hero -->
<section class="bg-gradient-to-r from-teal-600 to-teal-700 text-white py-16" style="background-image: url('https://images.unsplash.com/photo-1423666639041-f56000c27a9a?w=1920&h=400&fit=crop'); background-size: cover; background-position: center; position: relative;">
    <div style="position: absolute; inset: 0; background: linear-gradient(135deg, rgba(13, 148, 136, 0.9) 0%, rgba(6, 182, 212, 0.85) 100%);"></div>
    <div class="max-w-7xl mx-auto px-4 text-center relative z-10">
        <h1 class="text-4xl font-bold mb-4">Contact Us</h1>
        <p class="text-xl text-teal-100">We'd love to hear from you</p>
    </div>
</section>

<!-- Contact Section -->
<section class="py-16">
    <div class="max-w-7xl mx-auto px-4">
        <div class="grid md:grid-cols-2 gap-12">
            <!-- Contact Info -->
            <div>
                <h2 class="text-2xl font-bold mb-6">Get in Touch</h2>
                <p class="text-gray-600 mb-8">Have questions? We're here to help. Reach out to us through any of the following channels.</p>
                
                <div class="space-y-6">
                    <div class="flex items-start">
                        <div class="w-12 h-12 bg-teal-100 rounded-full flex items-center justify-center mr-4">
                            <i class="fas fa-map-marker-alt text-teal-600"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold">Address</h3>
                            <p class="text-gray-600">{{ \App\Models\Setting::get('contact_address', 'Yangon, Myanmar') }}</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="w-12 h-12 bg-teal-100 rounded-full flex items-center justify-center mr-4">
                            <i class="fas fa-phone text-teal-600"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold">Phone</h3>
                            <p class="text-gray-600">{{ \App\Models\Setting::get('contact_phone', '+95 9 69523 8273') }}</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="w-12 h-12 bg-teal-100 rounded-full flex items-center justify-center mr-4">
                            <i class="fas fa-envelope text-teal-600"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold">Email</h3>
                            <p class="text-gray-600">{{ \App\Models\Setting::get('contact_email', 'sanpyaeducationcentre@gmail.com') }}</p>
                        </div>
                    </div>
                </div>
                
                <!-- Social Links -->
                <div class="mt-8">
                    <h3 class="font-semibold mb-4">Follow Us</h3>
                    <div class="flex space-x-4">
                        @if(\App\Models\Setting::get('contact_facebook'))
                        <a href="{{ \App\Models\Setting::get('contact_facebook') }}" target="_blank" class="w-10 h-10 bg-teal-600 text-white rounded-full flex items-center justify-center hover:bg-teal-700"><i class="fab fa-facebook-f"></i></a>
                        @endif
                        @if(\App\Models\Setting::get('youtube_url'))
                        <a href="{{ \App\Models\Setting::get('youtube_url') }}" target="_blank" class="w-10 h-10 bg-teal-600 text-white rounded-full flex items-center justify-center hover:bg-teal-700"><i class="fab fa-youtube"></i></a>
                        @endif
                        @if(\App\Models\Setting::get('twitter_url'))
                        <a href="{{ \App\Models\Setting::get('twitter_url') }}" target="_blank" class="w-10 h-10 bg-teal-600 text-white rounded-full flex items-center justify-center hover:bg-teal-700"><i class="fab fa-twitter"></i></a>
                        @endif
                        @if(\App\Models\Setting::get('linkedin_url'))
                        <a href="{{ \App\Models\Setting::get('linkedin_url') }}" target="_blank" class="w-10 h-10 bg-teal-600 text-white rounded-full flex items-center justify-center hover:bg-teal-700"><i class="fab fa-linkedin"></i></a>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Contact Form -->
            <div class="bg-white p-8 rounded-lg shadow-lg">
                <h2 class="text-2xl font-bold mb-6">Send us a Message</h2>
                <form action="#" method="POST" class="space-y-6">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Your Name</label>
                        <input type="text" name="name" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-teal-500 focus:border-teal-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                        <input type="email" name="email" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-teal-500 focus:border-teal-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                        <input type="text" name="subject" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-teal-500 focus:border-teal-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                        <textarea name="message" rows="5" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-teal-500 focus:border-teal-500" required></textarea>
                    </div>
                    <button type="submit" class="btn-3d btn-3d-cyan w-full">
                        Send Message
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection

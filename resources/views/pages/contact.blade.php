@extends('layouts.app')

@section('title', 'Contact Us')

@section('content')
<!-- Hero -->
<section class="bg-gradient-to-r from-teal-600 to-teal-700 text-white py-16">
    <div class="max-w-7xl mx-auto px-4 text-center">
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
                            <p class="text-gray-600">Yangon, Myanmar</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="w-12 h-12 bg-teal-100 rounded-full flex items-center justify-center mr-4">
                            <i class="fas fa-phone text-teal-600"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold">Phone</h3>
                            <p class="text-gray-600">+95 9 123 456 789</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="w-12 h-12 bg-teal-100 rounded-full flex items-center justify-center mr-4">
                            <i class="fas fa-envelope text-teal-600"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold">Email</h3>
                            <p class="text-gray-600">info@sanpya.edu.mm</p>
                        </div>
                    </div>
                </div>
                
                <!-- Social Links -->
                <div class="mt-8">
                    <h3 class="font-semibold mb-4">Follow Us</h3>
                    <div class="flex space-x-4">
                        <a href="#" class="w-10 h-10 bg-teal-600 text-white rounded-full flex items-center justify-center hover:bg-teal-700"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="w-10 h-10 bg-teal-600 text-white rounded-full flex items-center justify-center hover:bg-teal-700"><i class="fab fa-youtube"></i></a>
                        <a href="#" class="w-10 h-10 bg-teal-600 text-white rounded-full flex items-center justify-center hover:bg-teal-700"><i class="fab fa-telegram"></i></a>
                        <a href="#" class="w-10 h-10 bg-teal-600 text-white rounded-full flex items-center justify-center hover:bg-teal-700"><i class="fab fa-tiktok"></i></a>
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

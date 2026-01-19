@extends('layouts.app')

@section('title', 'Checkout - ' . $course->title)

@section('content')
<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column - Order Summary -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h2 class="text-2xl font-bold mb-6">Checkout</h2>
                    
                    <!-- Course Info -->
                    <div class="flex gap-4 mb-6 pb-6 border-b">
                        <img src="{{ $course->thumbnail_url }}" alt="{{ $course->title }}" class="w-32 h-24 object-cover rounded">
                        <div class="flex-1">
                            <h3 class="font-semibold text-lg mb-2">{{ $course->title }}</h3>
                            <p class="text-sm text-gray-600 mb-2">By {{ $course->instructor->name }}</p>
                            <div class="flex items-center gap-4 text-sm text-gray-600">
                                <span><i class="fas fa-clock mr-1"></i>{{ $course->duration_hours }} hours</span>
                                <span><i class="fas fa-signal mr-1"></i>{{ ucfirst($course->level) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="mb-6" style="display:none">
                        <h3 class="text-lg font-semibold mb-4">Payment Method</h3>
                        <div class="space-y-3" style="display:none">
                            <label class="flex items-center p-4 border-2 border-green-500 rounded-lg cursor-pointer bg-green-50">
                                <input type="radio" name="payment_method" value="free" checked class="mr-3">
                                <div class="flex-1">
                                    <div class="font-semibold">Free</div>
                                    <div class="text-sm text-gray-600">Enroll for free</div>
                                </div>
                                <i class="fas fa-gift text-2xl text-green-600"></i>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Price Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6 sticky top-4">
                    <h3 class="text-lg font-semibold mb-4">Order Summary</h3>
                    
                    <div class="space-y-3 mb-4 pb-4 border-b">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Original Price</span>
                            <span class="font-semibold">${{ number_format($course->price, 2) }}</span>
                        </div>
                        
                        @if($course->discount_price)
                        <div class="flex justify-between text-green-600">
                            <span>Discount</span>
                            <span class="font-semibold">-${{ number_format($course->price - $course->discount_price, 2) }}</span>
                        </div>
                        @endif
                    </div>
                    
                    <div class="flex justify-between text-xl font-bold mb-6">
                        <span>Total</span>
                        <span class="text-teal-600">${{ number_format($course->current_price, 2) }}</span>
                    </div>
                    
                    <form action="{{ route('courses.enroll', $course) }}" method="POST">
                        @csrf
                        <input type="hidden" name="payment_method" id="selected_payment_method" value="free">
                        <button type="submit" class="w-full btn-3d btn-3d-cyan text-center">
                            Complete Enrollment
                        </button>
                    </form>
                    
                    <div class="mt-6 text-sm text-gray-600">
                        <p class="mb-2"><i class="fas fa-shield-alt mr-2 text-teal-600"></i>30-day money-back guarantee</p>
                        <p class="mb-2"><i class="fas fa-infinity mr-2 text-teal-600"></i>Full lifetime access</p>
                        <p><i class="fas fa-certificate mr-2 text-teal-600"></i>Certificate of completion</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Update hidden input when payment method changes
    document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
        radio.addEventListener('change', function() {
            document.getElementById('selected_payment_method').value = this.value;
        });
    });
</script>
@endpush
@endsection

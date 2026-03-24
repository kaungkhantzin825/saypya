@extends('layouts.app')

@section('title', $post->title)

@section('content')
<!-- Blog Post -->
<article class="py-12">
    <div class="max-w-4xl mx-auto px-4">
        <!-- Header -->
        <header class="mb-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">{{ $post->title }}</h1>
            <div class="flex items-center text-gray-600">
                <img src="{{ $post->author->avatar_url }}" alt="{{ $post->author->name }}" class="w-12 h-12 rounded-full mr-3">
                <div>
                    <div class="font-semibold">{{ $post->author->name }}</div>
                    <div class="text-sm">
                        {{ $post->published_at->format('F d, Y') }} • {{ $post->views_count }} views
                    </div>
                </div>
            </div>
        </header>

        <!-- Featured Image -->
        @if($post->featured_image)
            <img src="{{ $post->featured_image_url }}" alt="{{ $post->title }}" class="w-full h-96 object-cover rounded-lg mb-8">
        @endif

        <!-- Content -->
        <div class="prose prose-lg max-w-none">
            {!! nl2br(e($post->content)) !!}
        </div>

        <!-- Share -->
        <div class="mt-12 pt-8 border-t">
            <h3 class="text-lg font-semibold mb-4">Share this post</h3>
            <div class="flex space-x-4">
                <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(route('blog.show', $post->slug)) }}" target="_blank" class="btn-3d btn-3d-teal">
                    <i class="fab fa-facebook-f"></i> Facebook
                </a>
                <a href="https://twitter.com/intent/tweet?url={{ urlencode(route('blog.show', $post->slug)) }}&text={{ urlencode($post->title) }}" target="_blank" class="btn-3d btn-3d-cyan">
                    <i class="fab fa-twitter"></i> Twitter
                </a>
            </div>
        </div>
    </div>
</article>

<!-- Related Posts -->
@if($relatedPosts->count() > 0)
<section class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-2xl font-bold mb-8">Related Posts</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($relatedPosts as $relatedPost)
                <article class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition">
                    <a href="{{ route('blog.show', $relatedPost->slug) }}">
                        <img src="{{ $relatedPost->featured_image_url }}" alt="{{ $relatedPost->title }}" class="w-full h-40 object-cover">
                    </a>
                    <div class="p-4">
                        <h3 class="font-bold text-gray-900 mb-2 hover:text-teal-600">
                            <a href="{{ route('blog.show', $relatedPost->slug) }}">{{ $relatedPost->title }}</a>
                        </h3>
                        <p class="text-sm text-gray-600">{{ $relatedPost->excerpt }}</p>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endif
@endsection

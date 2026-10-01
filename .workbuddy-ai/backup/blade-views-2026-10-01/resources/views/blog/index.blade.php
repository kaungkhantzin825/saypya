@extends('layouts.app')

@section('title', 'Blog')

@section('content')
<!-- Hero -->
<section class="bg-gradient-to-r from-teal-600 to-cyan-600 text-white py-16">
    <div class="max-w-7xl mx-auto px-4 text-center">
        <h1 class="text-4xl font-bold mb-4">Our Blog</h1>
        <p class="text-xl text-teal-100">Latest news, tips, and insights from Sanpya Academy</p>
    </div>
</section>

<!-- Blog Posts -->
<section class="py-12">
    <div class="max-w-7xl mx-auto px-4">
        @if($posts->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($posts as $post)
                    <article class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition">
                        <a href="{{ route('blog.show', $post->slug) }}">
                            <img src="{{ $post->featured_image_url }}" alt="{{ $post->title }}" class="w-full h-48 object-cover">
                        </a>
                        <div class="p-6">
                            <div class="flex items-center text-sm text-gray-500 mb-3">
                                <span>{{ $post->published_at->format('M d, Y') }}</span>
                                <span class="mx-2">•</span>
                                <span>{{ $post->author->name }}</span>
                                <span class="mx-2">•</span>
                                <span><i class="fas fa-eye"></i> {{ $post->views_count }}</span>
                            </div>
                            <h2 class="text-xl font-bold text-gray-900 mb-3 hover:text-teal-600">
                                <a href="{{ route('blog.show', $post->slug) }}">{{ $post->title }}</a>
                            </h2>
                            <p class="text-gray-600 mb-4">{{ $post->excerpt }}</p>
                            <a href="{{ route('blog.show', $post->slug) }}" class="text-teal-600 hover:text-teal-700 font-semibold">
                                Read More <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-12">
                {{ $posts->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <i class="fas fa-newspaper text-6xl text-gray-300 mb-4"></i>
                <p class="text-xl text-gray-600">No blog posts yet. Check back soon!</p>
            </div>
        @endif
    </div>
</section>
@endsection

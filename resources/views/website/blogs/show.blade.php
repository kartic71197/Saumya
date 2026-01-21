@extends('website.main')
@section('content')

    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-10 max-w-8xl">
        <!-- Grid for Main + Related -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-16">
            <!-- Main Article -->
            <article class="col-span-1 lg:col-span-2 bg-white rounded-2xl shadow-xl overflow-hidden">
                @if($blog->image)
                    <div class="relative h-[500px] overflow-hidden">
                        <img src="{{ Storage::url($blog->image) }}" alt="{{ $blog->title }}" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent"></div>
                    </div>
                @endif

                <div class="p-8 sm:p-12">
                    <!-- Header Section -->
                    <div class="flex flex-col lg:flex-row justify-between items-start gap-6 mb-8">
                        <div class="flex-1">
                            <h1 class="text-4xl sm:text-5xl font-bold text-gray-900 mb-6 leading-tight">
                                {{ $blog->title }}
                            </h1>

                            <div class="flex flex-wrap items-center gap-4 text-gray-600">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <span class="font-medium">{{ $blog->created_at->format('F d, Y') }}</span>
                                </div>

                                @if($blog->updated_at->ne($blog->created_at))
                                    <div class="flex items-center text-sm text-gray-500">
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                        <span>Updated {{ $blog->updated_at->format('F d, Y') }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <hr class="border-gray-200 mb-10">

                    <!-- Content -->
                    <div
                        class="prose prose-lg prose-gray max-w-none prose-headings:font-bold prose-headings:text-gray-900 prose-p:text-gray-700 prose-p:leading-relaxed prose-a:text-blue-600 prose-a:no-underline hover:prose-a:underline prose-img:rounded-lg prose-img:shadow-md">
                        {!! $blog->content !!}
                    </div>
                </div>
            </article>

            <!-- Related Posts Sidebar -->
            @if($recentBlogs->count() > 0)
                <aside class="space-y-6 border-l border-gray-300 pl-6">
                    <h3 class="text-2xl font-bold text-gray-900 border-b border-gray-500 py-3">Related Posts</h3>
                    <div class="space-y-4">
                        @foreach($recentBlogs as $relatedBlog)
                            <article
                                class="bg-white rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 overflow-hidden border border-gray-100">
                                @if($relatedBlog->image)
                                    <div class="h-32 overflow-hidden">
                                        <img src="{{ Storage::url($relatedBlog->image) }}" alt="{{ $relatedBlog->title }}"
                                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                    </div>
                                @else
                                    <div class="h-32 bg-gray-100 flex items-center justify-center">
                                        <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                @endif
                                <div class="p-4">
                                    <h4 class="font-semibold text-gray-900 line-clamp-2">{{ $relatedBlog->title }}</h4>
                                    <a href="{{ route('admin.blogs.show', $relatedBlog->slug) }}"
                                        class="text-blue-600 text-sm font-medium hover:underline mt-2 inline-block">
                                        Read Article →
                                    </a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </aside>
            @endif
        </div>

        <!-- Back Button -->
        <div class="flex justify-center mt-10">
            <a href="{{ route('admin.blogs.index') }}"
                class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900 font-semibold transition-colors group">
                <svg class="w-5 h-5 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to All Posts
            </a>
        </div>
    </div>
@endsection
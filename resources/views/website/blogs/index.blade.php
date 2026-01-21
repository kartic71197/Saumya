@extends('website.main')
@section('content')


  <div class="p-4">
        <div class="min-h-screen bg-gray-50 rounded-lg">
            <!-- Blog Grid -->
            <main class="container mx-auto px-4 sm:px-6 lg:px-8 py-5">
                @forelse($blogs as $blog)
                    @if($loop->first)
                        <div class="grid gap-6 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @endif

                        <article
                            class="bg-white rounded-xl shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col overflow-hidden group border border-gray-100">
                            <!-- Image -->
                            <div
                                class="h-48 bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center overflow-hidden relative">
                                @if($blog->image)
                                    <img src="{{ Storage::url($blog->image) }}" alt="{{ $blog->title }}"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                @else
                                    <div class="text-center">
                                        <svg class="w-12 h-12 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <span class="text-gray-400 text-sm font-medium">No Image</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Content -->
                            <div class="p-5 flex-1 flex flex-col justify-between">
                                <div>
                                    <h2
                                        class="text-base font-bold text-gray-900 mb-2 line-clamp-2 leading-snug group-hover:text-blue-600 transition-colors">
                                        {{ $blog->title }}
                                    </h2>
                                    <div class="flex items-center text-gray-500 text-xs mb-3">
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        {{ $blog->created_at->format('M d, Y') }}
                                    </div>
                                </div>
                                <a href="{{ route('blogs.show', $blog->slug) }}"
                                    class="inline-flex items-center text-blue-600 text-sm font-semibold hover:text-blue-700 transition-colors group/link">
                                    Read Article
                                    <svg class="w-4 h-4 ml-1 group-hover/link:translate-x-1 transition-transform"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                    </svg>
                                </a>
                            </div>
                        </article>

                        @if($loop->last)
                            </div>
                        @endif
                @empty
                    <div class="text-center py-20">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-4">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">No blog posts yet</h3>
                    </div>
                @endforelse

                <!-- Pagination -->
                @if($blogs->hasPages())
                    <div class="mt-10">
                        {{ $blogs->links() }}
                    </div>
                @endif
            </main>
        </div>
    </div>


@endsection
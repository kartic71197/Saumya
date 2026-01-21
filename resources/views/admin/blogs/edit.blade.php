<x-app-layout>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-10">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-4xl">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-4xl font-bold text-gray-900 mb-2">Edit Blog Post</h1>
                <p class="text-gray-600">Update your blog post content and settings</p>
            </div>

            <!-- Form Card -->
            <form action="{{ route('admin.blogs.update', $blog) }}" method="POST" enctype="multipart/form-data"
                class="bg-white rounded-2xl shadow-xl overflow-hidden" id="editBlogForm">
                @csrf
                @method('PUT')

                <div class="p-8 sm:p-10 space-y-8">
                    <!-- Title Field -->
                    <div>
                        <label for="title" class="block text-sm font-bold text-gray-900 mb-2">
                            Title <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="title" id="title" value="{{ old('title', $blog->title) }}"
                            class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all @error('title') border-red-500 @enderror"
                            placeholder="Enter a compelling title..." required>
                        @error('title')
                            <div class="flex items-center mt-2 text-red-600 text-sm">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                        clip-rule="evenodd" />
                                </svg>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Excerpt Field -->
                    <div>
                        <label for="excerpt" class="block text-sm font-bold text-gray-900 mb-2">
                            Excerpt <span class="text-gray-500 font-normal">(Optional)</span>
                        </label>
                        <textarea name="excerpt" id="excerpt" rows="3"
                            class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all @error('excerpt') border-red-500 @enderror"
                            placeholder="Brief summary of your blog post...">{{ old('excerpt', $blog->excerpt) }}</textarea>
                        @error('excerpt')
                            <div class="flex items-center mt-2 text-red-600 text-sm">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                        clip-rule="evenodd" />
                                </svg>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Featured Image -->
                    <div>
                        <label for="image" class="block text-sm font-bold text-gray-900 mb-2">
                            Featured Image
                        </label>

                        @if($blog->image)
                            <div class="mb-4 relative inline-block">
                                <img src="{{ Storage::url($blog->image) }}" alt="{{ $blog->title }}"
                                    class="max-w-sm rounded-lg shadow-md border-2 border-gray-200">
                                <div class="absolute top-2 right-2 bg-blue-600 text-white text-xs px-2 py-1 rounded-full">
                                    Current
                                </div>
                            </div>
                        @endif

                        <div class="relative">
                            <input type="file" name="image" id="image"
                                class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 text-gray-900 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all @error('image') border-red-500 @enderror"
                                accept="image/*">
                            <p class="mt-2 text-sm text-gray-500">Upload a new image to replace the current one</p>
                        </div>
                        @error('image')
                            <div class="flex items-center mt-2 text-red-600 text-sm">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                        clip-rule="evenodd" />
                                </svg>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Content Editor -->
                    <div>
                        <label for="content" class="block text-sm font-bold text-gray-900 mb-2">
                            Content <span class="text-red-500">*</span>
                        </label>
                        <div
                            class="border-2 border-gray-300 rounded-lg overflow-hidden focus-within:border-blue-500 focus-within:ring-2 focus-within:ring-blue-200 transition-all @error('content') border-red-500 @enderror">
                            <div id="editor" style="min-height: 400px;"></div>
                        </div>
                        <input type="hidden" name="content" id="content" value="{{ old('content', $blog->content) }}">
                        @error('content')
                            <div class="flex items-center mt-2 text-red-600 text-sm">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                        clip-rule="evenodd" />
                                </svg>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

                <!-- Form Actions -->
                <div
                    class="bg-gray-50 px-8 sm:px-10 py-6 border-t border-gray-200 flex flex-col sm:flex-row gap-3 sm:justify-end">
                    <a href="{{ route('admin.blogs.show', $blog->slug) }}"
                        class="inline-flex justify-center items-center gap-2 bg-white hover:bg-gray-50 text-gray-700 border-2 border-gray-300 px-6 py-3 rounded-lg font-semibold transition-all duration-200 shadow-sm hover:shadow">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Cancel
                    </a>
                    <button type="submit"
                        class="inline-flex justify-center items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition-all duration-200 shadow-md hover:shadow-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Update Blog Post
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- QuillJS Editor -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

    <script>
        let quill = new Quill('#editor', {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    ['blockquote', 'code-block'],
                    [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                    [{ 'indent': '-1' }, { 'indent': '+1' }],
                    ['link', 'image'],
                    [{ 'align': [] }],
                    ['clean']
                ]
            },
            placeholder: 'Write your blog content here...'
        });

        // Load existing content
        quill.root.innerHTML = document.querySelector('#content').value;

        // Save content on form submit
        document.getElementById('editBlogForm').addEventListener('submit', function (e) {
            const content = quill.root.innerHTML;
            const contentInput = document.querySelector('#content');
            
            contentInput.value = content;
            
            // Validate that content isn't empty
            if (!content || content.trim() === '' || content.trim() === '<p><br></p>' || quill.getText().trim() === '') {
                e.preventDefault();
                alert('Please write some content for your blog post');
                return false;
            }
        });
    </script>
</x-app-layout>
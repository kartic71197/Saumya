<x-app-layout>
    <div class="container mx-auto px-4 py-8 max-w-3xl">
        <h1 class="text-3xl font-bold mb-8">Create New Blog Post</h1>

        <form action="{{ route('admin.blogs.store') }}" method="POST" enctype="multipart/form-data"
            class="bg-white p-8 rounded-lg shadow" id="blogForm">
            @csrf

            <div class="mb-6">
                <label for="title" class="block text-sm font-bold mb-2">Title</label>
                <input type="text" name="title" id="title"
                    class="w-full border border-gray-300 rounded px-3 py-2 @error('title') border-red-500 @enderror"
                    required>
                @error('title')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="mb-6">
                <label for="excerpt" class="block text-sm font-bold mb-2">Excerpt (Optional)</label>
                <textarea name="excerpt" id="excerpt" rows="2"
                    class="w-full border border-gray-300 rounded px-3 py-2 @error('excerpt') border-red-500 @enderror"></textarea>
                @error('excerpt')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="mb-6">
                <label for="image" class="block text-sm font-bold mb-2">Featured Image</label>
                <input type="file" name="image" id="image" class="w-full @error('image') border-red-500 @enderror"
                    accept="image/*">
                @error('image')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="mb-6">
                <label for="content" class="block text-sm font-bold mb-2">Content</label>
                <div id="editor" class="h-64 bg-white border border-gray-300 rounded"></div>
                <input type="hidden" name="content" id="content">
                @error('content')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="flex gap-4">
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded">
                    Create Blog
                </button>
                <a href="{{ route('admin.blogs.index') }}"
                    class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded">
                    Cancel
                </a>
            </div>
        </form>
    </div>

    <!-- QuillJS Editor -->
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">

    <script>
        let quill = new Quill('#editor', {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    ['blockquote', 'code-block'],
                    [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                    ['link', 'image'],
                    ['clean']
                ]
            },
            placeholder: 'Write your blog content here...'
        });

        document.getElementById('blogForm').addEventListener('submit', function (e) {
            // Get HTML content from Quill
            const content = quill.root.innerHTML;
            const contentInput = document.querySelector('#content');
            
            // Set the hidden input value
            contentInput.value = content;
            
            // Debug: Check what's being sent
            console.log('Content being sent:', content);
            
            // Validate that content isn't empty
            if (!content || content.trim() === '' || content.trim() === '<p><br></p>' || quill.getText().trim() === '') {
                e.preventDefault();
                alert('Please write some content for your blog post');
                return false;
            }
        });
    </script>

</x-app-layout>
<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BlogController extends Controller
{
    public function index()
    {
        if (Auth::check() && Auth::user()->role_id == 1) {
            $blogs = Blog::latest()->paginate(10);
            return view('admin.blogs.index', compact('blogs'));
        }
        return back()->with('error', 'Unauthorized access.');
    }

    public function userView()
    {
        $blogs = Blog::latest()->paginate(6);
        return view('website.blogs.index', compact('blogs'));
    }
    public function create()
    {
        if (Auth::check() && Auth::user()->role_id == 1) {
            return view('admin.blogs.create');
        }
        return back()->with('error', 'Unauthorized access.');

    }

    public function show($slug)
    {
        $blog = Blog::where('slug', $slug)->firstOrFail();
        $recentBlogs = Blog::where('id', '!=', $blog->id)
            ->latest()
            ->limit(3)
            ->get();

        return view('admin.blogs.show', compact('blog', 'recentBlogs'));
    }

    public function userShow($slug)
    {
        $blog = Blog::where('slug', $slug)->firstOrFail();
        $recentBlogs = Blog::where('id', '!=', $blog->id)
            ->latest()
            ->limit(3)
            ->get();

        return view('website.blogs.show', compact('blog', 'recentBlogs'));
    }



    public function store(Request $request)
    {
        // Check authorization FIRST
        if (!Auth::check() || Auth::user()->role_id != 1) {
            return back()->with('error', 'Unauthorized access.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'excerpt' => 'nullable|string|max:500'
        ]);

        if ($request->hasFile('image')) {
            \Log::info('Image upload detected');
            $path = $request->file('image')->store('blogs', 'public');
            \Log::info('Image stored at: ' . $path);
            $validated['image'] = $path;
        }

        Blog::create($validated);

        return redirect()->route('admin.blogs.index')
            ->with('success', 'Blog created successfully!');
    }

    public function edit(Blog $blog)
    {
        return view('admin.blogs.edit', compact('blog'));
    }

    public function update(Request $request, Blog $blog)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'excerpt' => 'nullable|string|max:500'
        ]);

        if ($request->hasFile('image')) {
            if ($blog->image) {
                \Storage::disk('public')->delete($blog->image);
            }
            $path = $request->file('image')->store('blogs', 'public');
            $validated['image'] = $path;
        }

        $blog->update($validated);

        return redirect()->route('admin.blogs.show', $blog->slug)
            ->with('success', 'Blog updated successfully!');
    }

    public function destroy(Blog $blog)
    {

        if ($blog->image) {
            \Storage::disk('public')->delete($blog->image);
        }

        $blog->delete();
        $blogs = Blog::latest()->paginate(10);
        return redirect()->route('admin.blogs.index', compact('blogs'))
            ->with('success', 'Blog deleted successfully!');
    }
}

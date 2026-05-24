<?php

namespace App\Services;

use App\Http\Resources\BlogResource;
use App\Models\Blog;
use Illuminate\Http\Request;

class BlogService
{
    public function index(Request $request)
    {

        $data = $request->validate([
            'search' => 'nullable|string',
            'date' => 'nullable|date',
        ]);

        $blogs = Blog::where('status', 'published')->get();

        if (isset($data['search'])) {
            $blogs = $blogs->filter(function ($blog) use ($data) {
                return str_contains(strtolower($blog->title), strtolower($data['search'])) ||
                       str_contains(strtolower($blog->description), strtolower($data['search'])) ||
                       str_contains(strtolower($blog->content), strtolower($data['search']));
            });
        }

        if (isset($data['date'])) {
            $blogs = $blogs->filter(function ($blog) use ($data) {
                return $blog->created_at->toDateString() === $data['date'];
            });
        }

        return BlogResource::collection($blogs);
    }

    public function create() {}

    public function update() {}

    public function delete() {}
}

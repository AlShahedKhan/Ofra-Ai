<?php

namespace App\Http\Controllers;

use App\Http\Requests\BlogRequest;
use App\Http\Requests\BlogUpdateRequest;
use App\Models\Blog;
use App\Traits\HandlesApiResponse;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    use HandlesApiResponse;
    public function index()
    {
        return $this->safeCall(function () {
            $blogs = Blog::all();
            return $this->successResponse(
                'Blogs fetched successfully',
                ['blogs' => $blogs]
            );
        });
    }

    public function store(BlogRequest $request)
    {
        return $this->safeCall(function () use ($request) {
            // Retrieve validated data from BlogRequest
            $validated = $request->validated();

            // Handle the image upload if an image is provided
            if ($request->hasFile('image')) {
                $validated['image'] = $request->file('image')->store('images', 'public');
            }

            // Create the blog with the validated data
            $blog = Blog::create($validated);

            // Return a success response
            return $this->successResponse(
                'Blog created successfully',
                ['blog' => $blog]
            );
        });
    }


    public function show($id)
    {
        return $this->safeCall(function () use ($id) {
            $blog = Blog::findOrFail($id);

            return $this->successResponse(
                'Blog fetched successfully',
                ['blog' => $blog]
            );
        });
    }

    public function update(BlogUpdateRequest $request, $id)
    {
        return $this->safeCall(function () use ($request, $id) {
            // Retrieve validated data from BlogUpdateRequest
            $validated = $request->validated();

            // Find the blog or fail if not found
            $blog = Blog::findOrFail($id);

            // Handle image upload if a new image is provided
            if ($request->hasFile('image')) {
                $validated['image'] = $request->file('image')->store('images', 'public');
            }

            // Update the blog with validated data
            $blog->update($validated);

            // Return a success response
            return $this->successResponse(
                'Blog updated successfully',
                ['blog' => $blog]
            );
        });
    }


    public function destroy($id)
    {
        return $this->safeCall(function () use ($id) {
            $blog = Blog::findOrFail($id);
            $blog->delete();
            return $this->successResponse(
                'Blog deleted successfully',
                ['blog' => $blog]
            );
        });
    }
}

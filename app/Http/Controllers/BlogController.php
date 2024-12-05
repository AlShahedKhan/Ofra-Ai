<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Input;


class BlogController extends Controller
{
    /**
     * Fetch all blogs
     */
    public function index()
    {
        try {
            $blogs = Blog::all();
            return response()->json([
                'message' => 'Blogs fetched successfully',
                'data' => $blogs,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error fetching blogs: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch blogs'], 500);
        }
    }

    /**
     * Create a new blog
     */
    public function store(Request $request)
    {
        try {
            // Validate the request
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'author' => 'required|string|max:255',
                'status' => 'required|string|in:draft,published',
                'tags' => 'nullable|string',
            ]);

            // Handle image upload
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('images');
                $validated['image'] = $imagePath;
                Log::info('Image uploaded', ['path' => $imagePath]);
            }

            // Create the blog
            $blog = Blog::create($validated);

            return response()->json([
                'message' => 'Blog created successfully',
                'data' => $blog,
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating blog: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create blog'], 500);
        }
    }

    /**
     * Fetch a specific blog
     */
    public function show($id)
    {
        try {
            $blog = Blog::findOrFail($id);
            return response()->json([
                'message' => 'Blog fetched successfully',
                'data' => $blog,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error fetching blog: ' . $e->getMessage());
            return response()->json(['error' => 'Blog not found'], 404);
        }
    }


    public function update(Request $request, $id)
    {
        try {
            Log::info('Updating blog with ID: ' . $id);

            // Fetch the blog record
            $blog = Blog::findOrFail($id);

            // Parse input data
            $data = $request->all();
            Log::info('Parsed input data:', $data);

            // Check if the image is a file or a string path
            $isFile = $request->hasFile('image');

            // Validate fields
            $validated = \Validator::make($data, [
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'author' => 'required|string|max:255',
                'status' => 'required|string|in:draft,published',
                'tags' => 'nullable|string',
                'image' => [
                    'nullable',
                    function ($attribute, $value, $fail) use ($isFile) {
                        // If it's not a file, check if it's a valid path
                        if (!$isFile && !is_string($value)) {
                            $fail("The $attribute field must be a valid file or string path.");
                        }
                    },
                ],
            ]);

            if ($validated->fails()) {
                Log::error('Validation failed:', $validated->errors()->toArray());
                return response()->json([
                    'error' => 'Validation failed',
                    'details' => $validated->errors(),
                ], 422);
            }

            $validatedData = $validated->validated();

            // Handle image if provided as a file
            if ($isFile) {
                $uploadedFile = $request->file('image');

                // Delete the old image if it exists
                if ($blog->image && file_exists(public_path('storage/' . $blog->image))) {
                    unlink(public_path('storage/' . $blog->image));
                    Log::info('Old image deleted: ' . $blog->image);
                }

                // Save the new image
                $imagePath = $uploadedFile->store('images', 'public'); // Save in storage/app/public/images
                $validatedData['image'] = $imagePath;
                Log::info('New image uploaded:', ['path' => $imagePath]);
            }

            // Handle image if provided as a string path
            elseif (isset($validatedData['image']) && !empty($validatedData['image'])) {
                Log::info('Using existing image path:', ['path' => $validatedData['image']]);
            }

            // Update the blog
            $blog->update($validatedData);

            // Include full URL for the image in the response
            $blogData = $blog->fresh();
            $blogData->image = $blogData->image ? asset('storage/' . $blogData->image) : null;

            return response()->json([
                'message' => 'Blog updated successfully',
                'data' => $blogData,
            ], 200);
        } catch (ModelNotFoundException $e) {
            Log::error('Blog not found: ' . $id);
            return response()->json(['error' => 'Blog not found'], 404);
        } catch (\Exception $e) {
            Log::error('Error updating blog: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update blog', 'details' => $e->getMessage()], 500);
        }
    }




    /**
     * Delete a blog
     */
    public function destroy($id)
    {
        try {
            $blog = Blog::findOrFail($id);

            // Delete image if exists
            if ($blog->image && file_exists(storage_path('app/' . $blog->image))) {
                unlink(storage_path('app/' . $blog->image));
                Log::info('Image deleted', ['path' => $blog->image]);
            }

            // Delete the blog
            $blog->delete();

            return response()->json([
                'message' => 'Blog deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error deleting blog: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete blog'], 500);
        }
    }
}

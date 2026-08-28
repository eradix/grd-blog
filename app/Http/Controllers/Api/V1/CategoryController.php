<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\PostSummaryResource;
use App\Models\Category;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CategoryController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return CategoryResource::collection(Category::orderBy('name')->get());
    }

    public function show(string $slug): AnonymousResourceCollection
    {
        $category = Category::where('slug', $slug)->firstOrFail();

        $posts = $category->posts()
            ->published()
            ->withPublicRelations()
            ->latest('published_at')
            ->simplePaginate(12);

        return PostSummaryResource::collection($posts)
            ->additional(['category' => new CategoryResource($category)]);
    }
}

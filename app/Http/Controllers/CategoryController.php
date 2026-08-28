<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Contracts\View\View;

class CategoryController extends Controller
{
    public function show(string $slug): View
    {
        $category = Category::where('slug', $slug)->firstOrFail();

        $posts = $category->posts()
            ->published()
            ->withPublicRelations()
            ->latest('published_at')
            ->simplePaginate(12);

        return view('categories.show', compact('category', 'posts'));
    }
}

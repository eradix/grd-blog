<?php

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;

test('the public feed query eager loads relations in a bounded number of queries', function () {
    $tags = Tag::factory()->count(3)->create();

    Post::factory()
        ->count(15)
        ->for(User::factory(), 'author')
        ->for(Category::factory())
        ->published()
        ->create()
        ->each(fn (Post $post) => $post->tags()->attach($tags->random(2)->pluck('id')));

    // 1 query for the posts page, plus one each for the eager-loaded author,
    // category, and tags relations — regardless of how many posts are on the page.
    $this->expectsDatabaseQueryCount(4);

    $posts = Post::published()->withPublicRelations()->latest('published_at')->simplePaginate(12);

    foreach ($posts as $post) {
        $post->author->name;
        $post->category?->name;
        $post->tags->pluck('name');
        $post->approved_comments_count;
    }
});

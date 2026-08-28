<?php

use App\Models\Post;
use App\Models\User;

test('a script tag in a post body is stripped from the rendered html', function () {
    $post = Post::factory()->for(User::factory(), 'author')->create([
        'body' => "Hello <script>alert('xss')</script> world",
    ]);

    // The tag itself is stripped; any leftover text is inert, HTML-escaped content,
    // never a live <script> element.
    expect($post->body_html)->not->toContain('<script>')
        ->and($post->body_html)->not->toContain('</script>');
});

test('an unsafe link scheme in a post body is stripped from the rendered html', function () {
    $post = Post::factory()->for(User::factory(), 'author')->create([
        'body' => '[click me](javascript:alert(1))',
    ]);

    expect($post->body_html)->not->toContain('javascript:');
});

test('the rendered post html is echoed on the public show page and is not double-escaped', function () {
    $post = Post::factory()->for(User::factory(), 'author')->published()->create([
        'body' => "# Heading\n\nSome **bold** text.",
    ]);

    $this->get("/posts/{$post->slug}")
        ->assertOk()
        ->assertSee('<h1>Heading</h1>', escape: false)
        ->assertSee('<strong>bold</strong>', escape: false);
});

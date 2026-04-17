<?php

namespace App\Tests;

use App\Entity\Post;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class PostTest extends TestCase
{
    public function testLikeAndIsLikedBy(): void
    {
        $post = new Post();

        $user = new User();
        $user->setUsername('testuser');
        $user->setEmail('test@example.com');
        $user->setPassword('password');

        // Au début : pas liké
        $this->assertFalse($post->isLikedBy($user));

        // On ajoute un like
        $post->addLike($user);

        // Maintenant : liké
        $this->assertTrue($post->isLikedBy($user));

        // On retire le like
        $post->removeLike($user);

        // Plus liké
        $this->assertFalse($post->isLikedBy($user));
    }
}
<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PublicPageTest extends WebTestCase
{
    public function testLandingPageIsSuccessful(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
    }

    public function testRedirectToLoginWhenNotAuthenticated(): void
    {
        $client = static::createClient();

        $client->request('GET', '/feed');

        $this->assertResponseRedirects('/login');
    }

    public function testAuthenticatedUserCanAccessFeedAndSeePostForm(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $entityManager = $container->get(\Doctrine\ORM\EntityManagerInterface::class);
        $passwordHasher = $container->get(\Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface::class);

        $unique = uniqid();

        $user = new \App\Entity\User();
        $user->setUsername('testuser_' . $unique);
        $user->setEmail('test_' . $unique . '@example.com');
        $user->setPassword($passwordHasher->hashPassword($user, 'password123'));
        $user->setRoles(['ROLE_USER']);
        $user->setCreatedAt(new \DateTimeImmutable());

        $entityManager->persist($user);
        $entityManager->flush();

        $client->loginUser($user);
        $crawler = $client->request('GET', '/feed');

        $this->assertResponseIsSuccessful();
        $this->assertGreaterThan(
            0,
            $crawler->filter('form')->count(),
            'Le formulaire de création de post doit être visible.'
        );
    }

    public function testApiPostsReturnsJson(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/posts');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
    }
}
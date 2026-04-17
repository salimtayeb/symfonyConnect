<?php

namespace App\DataFixtures;

use App\Entity\Post;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $usersData = [
            [
                'username' => 'admin',
                'email' => 'admin@symfoconnect.local',
                'password' => 'password',
                'roles' => ['ROLE_ADMIN'],
                'bio' => 'Administrateur de SymfoConnect.',
                'avatarUrl' => null,
            ],
            [
                'username' => 'alice',
                'email' => 'alice@symfoconnect.local',
                'password' => 'password',
                'roles' => ['ROLE_USER'],
                'bio' => 'Passionnée de balades et de café.',
                'avatarUrl' => null,
            ],
            [
                'username' => 'bob',
                'email' => 'bob@symfoconnect.local',
                'password' => 'password',
                'roles' => ['ROLE_USER'],
                'bio' => 'Toujours partant pour un nouveau projet.',
                'avatarUrl' => null,
            ],
        ];

        $users = [];

        foreach ($usersData as $userData) {
            $user = new User();
            $user->setUsername($userData['username']);
            $user->setEmail($userData['email']);
            $user->setPassword(
                $this->passwordHasher->hashPassword($user, $userData['password'])
            );
            $user->setRoles($userData['roles']);
            $user->setBio($userData['bio']);
            $user->setAvatarUrl($userData['avatarUrl']);
            $user->setCreatedAt(new \DateTimeImmutable());

            $manager->persist($user);
            $users[$userData['username']] = $user;
        }

        $postsData = [
            [
                'author' => 'admin',
                'description' => 'Bienvenue sur SymfoConnect. Ceci est le premier post de démonstration.',
                'image' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=1200&q=80',
                'lieu' => 'Paris, France',
            ],
            [
                'author' => 'alice',
                'description' => 'Déjeuner en terrasse après une matinée bien remplie.',
                'image' => 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=1200&q=80',
                'lieu' => 'Lyon, France',
            ],
            [
                'author' => 'alice',
                'description' => 'Nouvelle randonnée, la lumière était incroyable au sommet.',
                'image' => 'https://images.unsplash.com/photo-1501785888041-af3ef285b470?auto=format&fit=crop&w=1200&q=80',
                'lieu' => 'Annecy, France',
            ],
            [
                'author' => 'bob',
                'description' => 'Soirée entre amis autour d’un café et de quelques idées de projets.',
                'image' => 'https://images.unsplash.com/photo-1497215842964-222b430dc094?auto=format&fit=crop&w=1200&q=80',
                'lieu' => 'Bordeaux, France',
            ],
        ];

        foreach ($postsData as $postData) {
            $post = new Post();
            $post->setDescription($postData['description']);
            $post->setImage($postData['image']);
            $post->setLieu($postData['lieu']);
            $post->setUser($users[$postData['author']]);
            $post->setCreatedAt(new \DateTimeImmutable());

            $manager->persist($post);
        }

        $manager->flush();
    }
}
<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\User;
use App\Form\CommentType;
use App\Form\PostType;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_landing')]
    public function landing(): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        return $this->render('home/landing.html.twig');
    }

    #[Route('/feed', name: 'app_home')]
    public function index(
        Request $request,
        PostRepository $postRepository,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        CacheInterface $cache
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var User|null $user */
        $user = $this->getUser();

        if ($user === null) {
            throw $this->createAccessDeniedException('Vous devez être connecté.');
        }

        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post->setUser($user);
            $post->setCreatedAt(new \DateTimeImmutable());

            $entityManager->persist($post);
            $entityManager->flush();

            $cache->clear();

            $this->addFlash('success', 'Le post a bien été créé.');

            return $this->redirectToRoute('app_home');
        }

        $following = $user->getFollowing()->toArray();
        $following[] = $user;

        $userIds = array_map(fn(User $followedUser) => $followedUser->getId(), $following);
        sort($userIds);

        $cacheKey = 'feed_posts_user_' . $user->getId() . '_' . md5(implode('_', $userIds));

        $posts = $cache->get($cacheKey, function (ItemInterface $item) use ($postRepository, $following) {
            $item->expiresAfter(300);

            return $postRepository->createQueryBuilder('p')
                ->where('p.user IN (:users)')
                ->setParameter('users', $following)
                ->orderBy('p.createdAt', 'DESC')
                ->getQuery()
                ->getResult();
        });

        $suggestedUsers = $userRepository->createQueryBuilder('u')
            ->where('u != :currentUser')
            ->setParameter('currentUser', $user)
            ->orderBy('u.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        $suggestedUsers = array_filter($suggestedUsers, function (User $suggestedUser) use ($user) {
            return !$user->isFollowing($suggestedUser);
        });

        $commentForms = [];
        foreach ($posts as $feedPost) {
            $comment = new Comment();

            $commentForms[$feedPost->getId()] = $this->createForm(CommentType::class, $comment, [
                'action' => $this->generateUrl('app_post_comment', [
                    'id' => $feedPost->getId(),
                ]),
                'method' => 'POST',
            ])->createView();
        }

        return $this->render('home/index.html.twig', [
            'posts' => $posts,
            'suggestedUsers' => $suggestedUsers,
            'postForm' => $form->createView(),
            'commentForms' => $commentForms,
        ]);
    }

    #[Route('/discover', name: 'app_discover')]
    public function discover(UserRepository $userRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var User|null $user */
        $user = $this->getUser();

        if ($user === null) {
            throw $this->createAccessDeniedException('Vous devez être connecté.');
        }

        $users = $userRepository->createQueryBuilder('u')
            ->where('u != :currentUser')
            ->setParameter('currentUser', $user)
            ->orderBy('u.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        $users = array_filter($users, function (User $suggestedUser) use ($user) {
            return !$user->isFollowing($suggestedUser);
        });

        return $this->render('home/discover.html.twig', [
            'users' => $users,
        ]);
    }
}
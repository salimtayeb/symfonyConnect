<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Notification;
use App\Entity\User;
use App\Form\CommentType;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SearchController extends AbstractController
{
    #[Route('/profil/{username}', name: 'app_profile')]
    public function profile(
        string $username,
        UserRepository $userRepository,
        PostRepository $postRepository,
        EntityManagerInterface $entityManager,
        Request $request
    ): Response {
        $user = $userRepository->findOneBy(['username' => $username]);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur introuvable');
        }

        $posts = $postRepository->findBy(
            ['user' => $user],
            ['createdAt' => 'DESC']
        );

        $commentForms = [];

        foreach ($posts as $post) {
            $comment = new Comment();
            $comment->setPost($post);

            $form = $this->createForm(CommentType::class, $comment);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $this->denyAccessUnlessGranted('ROLE_USER');

                /** @var User $currentUser */
                $currentUser = $this->getUser();

                $comment->setUser($currentUser);
                $comment->setCreatedAt(new \DateTimeImmutable());

                $entityManager->persist($comment);
                $entityManager->flush();

                return $this->redirectToRoute('app_profile', [
                    'username' => $username,
                ]);
            }

            $commentForms[$post->getId()] = $form->createView();
        }

        return $this->render('search/index.html.twig', [
            'profileUser' => $user,
            'posts' => $posts,
            'isOwner' => $this->getUser() === $user,
            'commentForms' => $commentForms,
        ]);
    }

    #[Route('/profil/{username}/follow', name: 'app_profile_follow', methods: ['POST'])]
    public function follow(
        string $username,
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $targetUser = $userRepository->findOneBy(['username' => $username]);

        if (!$targetUser) {
            throw $this->createNotFoundException('Utilisateur introuvable');
        }

        /** @var User|null $currentUser */
        $currentUser = $this->getUser();

        if ($currentUser === null) {
            throw $this->createAccessDeniedException('Vous devez être connecté.');
        }

        if ($currentUser === $targetUser) {
            $this->addFlash('error', 'Vous ne pouvez pas vous suivre vous-même.');

            return $this->redirectToRoute('app_profile', [
                'username' => $targetUser->getUsername(),
            ]);
        }

        if ($currentUser->isFollowing($targetUser)) {
            $currentUser->unfollow($targetUser);
            $this->addFlash('success', 'Vous ne suivez plus ' . $targetUser->getUsername() . '.');
        } else {
            $currentUser->follow($targetUser);

            $notification = new Notification();
            $notification->setRecipient($targetUser);
            $notification->setType('follow');
            $notification->setContent($currentUser->getUsername() . ' a commencé à vous suivre.');
            $notification->setIsRead(false);
            $notification->setCreatedAt(new \DateTimeImmutable());

            $entityManager->persist($notification);

            $this->addFlash('success', 'Vous suivez maintenant ' . $targetUser->getUsername() . '.');
        }

        $entityManager->flush();

        $referer = $request->headers->get('referer');

        if ($referer) {
            return $this->redirect($referer);
        }

        return $this->redirectToRoute('app_profile', [
            'username' => $targetUser->getUsername(),
        ]);
    }
}
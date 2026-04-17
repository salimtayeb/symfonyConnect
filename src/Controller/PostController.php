<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\User;
use App\Form\CommentType;
use App\Form\PostType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PostController extends AbstractController
{
    #[Route('/post/nouveau', name: 'app_post_new')]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $post = new Post();

        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User|null $author */
            $author = $this->getUser();

            if ($author === null) {
                throw $this->createAccessDeniedException('Vous devez être connecté pour publier.');
            }

            $post->setUser($author);
            $post->setCreatedAt(new \DateTimeImmutable());

            $entityManager->persist($post);
            $entityManager->flush();

            $this->addFlash('success', 'Le post a bien été créé.');

            return $this->redirectToRoute('app_home');
        }

        return $this->render('post/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/post/{id}/like', name: 'app_post_like', methods: ['POST'])]
    public function like(
        Post $post,
        EntityManagerInterface $entityManager
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var User|null $user */
        $user = $this->getUser();

        if ($user === null) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour liker.');
        }

        if ($post->isLikedBy($user)) {
            $post->removeLike($user);
        } else {
            $post->addLike($user);
        }

        $entityManager->flush();

        return $this->redirectToRoute('app_home');
    }

    #[Route('/post/{id}/comment', name: 'app_post_comment', methods: ['POST'])]
    public function comment(
        Post $post,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var User|null $user */
        $user = $this->getUser();

        if ($user === null) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour commenter.');
        }

        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setUser($user);
            $comment->setPost($post);
            $comment->setCreatedAt(new \DateTimeImmutable());

            $entityManager->persist($comment);
            $entityManager->flush();

            $this->addFlash('success', 'Commentaire ajouté.');
        } else {
            $this->addFlash('error', 'Le commentaire est invalide.');
        }

        return $this->redirectToRoute('app_home');
    }

    #[Route('/post/{id}/delete', name: 'app_post_delete', methods: ['POST'])]
    public function delete(
        Post $post,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var User|null $user */
        $user = $this->getUser();

        if ($user === null || $post->getUser() !== $user) {
            throw $this->createAccessDeniedException('Vous ne pouvez supprimer que vos propres posts.');
        }

        if ($this->isCsrfTokenValid('delete_post_' . $post->getId(), $request->request->get('_token'))) {
            $entityManager->remove($post);
            $entityManager->flush();

            $this->addFlash('success', 'Le post a bien été supprimé.');
        }

        return $this->redirectToRoute('app_home');
    }
}
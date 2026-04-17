<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\User;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MessageController extends AbstractController
{
    #[Route('/messages', name: 'app_messages')]
    public function index(EntityManagerInterface $em, MessageRepository $messageRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /** @var User $user */
        $user = $this->getUser();

        $messages = $em->getRepository(Message::class)
            ->createQueryBuilder('m')
            ->where('m.sender = :user OR m.recipient = :user')
            ->setParameter('user', $user)
            ->orderBy('m.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        $conversations = [];

        foreach ($messages as $message) {
            if ($message->getSender() === $user) {
                $otherUser = $message->getRecipient();
            } else {
                $otherUser = $message->getSender();
            }

            if ($otherUser !== null) {
                $conversations[$otherUser->getId()] = $otherUser;
            }
        }

        $followedUsers = $user->getFollowing();
        $unreadCount = $messageRepository->countUnreadForUser($user);

        return $this->render('message/index.html.twig', [
            'conversations' => $conversations,
            'followedUsers' => $followedUsers,
            'unreadCount' => $unreadCount,
        ]);
    }

    #[Route('/messages/{id}', name: 'app_message_show', requirements: ['id' => '\d+'])]
    public function show(User $otherUser, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /** @var User $user */
        $user = $this->getUser();

        if ($user->getId() === $otherUser->getId()) {
            $this->addFlash('error', 'Tu ne peux pas t’envoyer un message à toi-même.');

            return $this->redirectToRoute('app_messages');
        }

        if ($request->isMethod('POST')) {
            $content = trim((string) $request->request->get('content'));

            if ($content === '') {
                $this->addFlash('error', 'Le message ne peut pas être vide.');
            } else {
                $message = new Message();
                $message->setSender($user);
                $message->setRecipient($otherUser);
                $message->setContent($content);

                $em->persist($message);
                $em->flush();

                $this->addFlash('success', 'Message envoyé avec succès.');

                return $this->redirectToRoute('app_message_show', [
                    'id' => $otherUser->getId(),
                ]);
            }
        }

        $messages = $em->getRepository(Message::class)
            ->createQueryBuilder('m')
            ->where('(m.sender = :user AND m.recipient = :otherUser) OR (m.sender = :otherUser AND m.recipient = :user)')
            ->setParameter('user', $user)
            ->setParameter('otherUser', $otherUser)
            ->orderBy('m.createdAt', 'ASC')
            ->getQuery()
            ->getResult();

        foreach ($messages as $message) {
            if ($message->getRecipient() === $user && !$message->isRead()) {
                $message->setIsRead(true);
            }
        }

        $em->flush();

        return $this->render('message/show.html.twig', [
            'otherUser' => $otherUser,
            'messages' => $messages,
        ]);
    }
}
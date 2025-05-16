<?php

namespace App\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CommentController extends AbstractController
{
    #[Route('/tweet/{id}/comment', name: 'tweet_comment_front', methods: ['POST'])]
    public function comment(
        int $id,
        Request $request,
        HttpClientInterface $httpClient,
        RequestStack $requestStack
    ): RedirectResponse {
        $session = $requestStack->getSession();
        $token = $session->get('auth_token');

        if (!$token) {
            $this->addFlash('error', 'Vous devez être connecté pour commenter.');
            return $this->redirectToRoute('app_login');
        }

        $content = trim($request->request->get('content', ''));
        if (!$content) {
            $this->addFlash('error', 'Le contenu du commentaire est vide.');
            return $this->redirectToRoute('app_feed');
        }

        try {
            $response = $httpClient->request('POST', "http://php/api/tweets/{$id}/comments", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'content' => $content,
                ],
            ]);

            if ($response->getStatusCode() === 201) {
                $this->addFlash('success', 'Commentaire ajouté avec succès !');
            } else {
                $data = $response->toArray(false);
                $this->addFlash('warning', $data['message'] ?? 'Impossible d\'ajouter le commentaire.');
            }
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de l\'appel à l\'API : ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_feed');
    }
}

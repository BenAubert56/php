<?php

namespace App\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TweetController extends AbstractController
{
    #[Route('/tweet/{id}/like', name: 'tweet_like_front')]
    public function like(int $id, HttpClientInterface $httpClient, RequestStack $requestStack): Response
    {
        $session = $requestStack->getSession();
        $token = $session->get('auth_token');

        if (!$token) {
            $this->addFlash('error', 'Vous devez être connecté pour liker un tweet.');
            return $this->redirectToRoute('app_login');
        }

        try {
            $response = $httpClient->request('POST', "http://php/api/tweets/{$id}/like", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                ],
            ]);

            if ($response->getStatusCode() === 201) {
                $this->addFlash('success', 'Tweet liké avec succès !');
            } else {
                $data = $response->toArray(false);
                $this->addFlash('warning', $data['message'] ?? 'Impossible de liker le tweet.');
            }

        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de l\'appel à l\'API : ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_feed');
    }
}
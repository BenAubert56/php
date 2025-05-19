<?php

namespace App\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

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

    #[Route('/tweet/{id}/retweet', name: 'tweet_retweet_front', methods: ['POST'])]
    public function retweet(int $id, HttpClientInterface $httpClient, RequestStack $requestStack): Response
    {
        $session = $requestStack->getSession();
        $token = $session->get('auth_token');

        if (!$token) {
            $this->addFlash('error', 'Vous devez être connecté pour retweeter.');
            return $this->redirectToRoute('app_login');
        }

        try {
            $response = $httpClient->request('POST', "http://php/api/tweets/{$id}/retweet", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                ],
            ]);

            if ($response->getStatusCode() === 201) {
                $this->addFlash('success', 'Retweet effectué avec succès !');
            } else {
                $data = $response->toArray(false);
                $this->addFlash('warning', $data['message'] ?? 'Impossible de retweeter ce tweet.');
            }

        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de l\'appel à l\'API : ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_feed');
    }

    #[Route('/tweet/{id}/unlike', name: 'tweet_unlike_front', methods: ['POST'])]
    public function unlike(int $id, HttpClientInterface $httpClient, RequestStack $requestStack): Response
    {
        $session = $requestStack->getSession();
        $token = $session->get('auth_token');

        if (!$token) {
            $this->addFlash('error', 'Vous devez être connecté pour annuler un like.');
            return $this->redirectToRoute('app_login');
        }

        try {
            $response = $httpClient->request('DELETE', "http://php/api/tweets/{$id}/unlike", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                ],
            ]);

            if ($response->getStatusCode() === 200) {
                $this->addFlash('success', 'Like supprimé avec succès !');
            } else {
                $data = $response->toArray(false);
                $this->addFlash('warning', $data['message'] ?? 'Impossible de supprimer le like.');
            }

        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de l\'appel à l\'API : ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_feed');
    }

    #[Route(path: '/tweet/delete/{id}', name: 'tweet_delete', methods: ['POST'])]
    public function deleteTweet(int $id, HttpClientInterface $client, RequestStack $requestStack): RedirectResponse
    {
        $session = $requestStack->getSession();
        $token = $session->get('auth_token');

        if (!$token) {
            $this->addFlash('error', 'Vous devez être connecté pour supprimer un tweet.');
            return $this->redirectToRoute('app_login');
        }

        $response = $client->request('DELETE', "http://php/api/tweets/$id", [
            'headers' => [  
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ],
        ]);

        if ($response->getStatusCode() === 200) {
            $this->addFlash('success', 'Tweet supprimé avec succès !');
        } elseif ($response->getStatusCode() === 403) {
            $this->addFlash('error', 'Vous n\'avez pas les droits pour supprimer ce tweet.');
        } elseif ($response->getStatusCode() === 404) {
            $this->addFlash('error', 'Tweet introuvable.');
        } else {
            $this->addFlash('error', 'Une erreur est survenue lors de la suppression.');
        }

        return $this->redirectToRoute('app_feed');
    }

    
    #[Route('/tweet/create', name: 'tweet_create_front', methods: ['POST'])]
    public function createTweet(RequestStack $requestStack, HttpClientInterface $httpClient, Request $request): RedirectResponse
    {
        $session = $requestStack->getSession();
        $token = $session->get('auth_token');

        if (!$token) {
            $this->addFlash('error', 'Vous devez être connecté pour tweeter.');
            return $this->redirectToRoute('app_login');
        }

        $content = trim($request->request->get('content', ''));

        if (empty($content)) {
            $this->addFlash('warning', 'Le contenu du tweet ne peut pas être vide.');
            return $this->redirectToRoute('app_feed');
        }

        try {
            $response = $httpClient->request('POST', 'http://php/api/tweets', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'content' => $content,
                ],
            ]);

            if ($response->getStatusCode() === 201) {
                $this->addFlash('success', 'Tweet publié avec succès !');
            } else {
                $data = $response->toArray(false);
                $this->addFlash('warning', $data['message'] ?? 'Impossible de publier le tweet.');
            }
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de l\'appel à l\'API : ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_feed');
    }
}
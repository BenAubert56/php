<?php

namespace App\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FeedController extends AbstractController
{
    #[Route(path: '/feed', name: 'app_feed')]
    public function index(HttpClientInterface $client, RequestStack $requestStack): Response
    {
        $session = $requestStack->getSession();
        $token = $session->get('auth_token');
    
        if (!$token) {
            $this->addFlash('error', 'Vous devez être connecté pour accéder au fil d’actualité.');
            return $this->redirectToRoute('app_login');
        }
    
        $request = $requestStack->getCurrentRequest();
        $query = $request->query->get('q');
    
        $tweetUrl = 'http://php/api/tweets/';
        if ($query) {
            $tweetUrl .= '?q=' . urlencode($query);
        }
    
        $tweetsResponse = $client->request('GET', $tweetUrl, [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
        ]);
    
        $usersResponse = $client->request('GET', 'http://php/api/users', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
        ]);
    
        $tweets = $tweetsResponse->toArray();
        $users = $usersResponse->toArray();
        $connectedUserEmail = $session->get('user_email');

    
        return $this->render('feed/feed.html.twig', [
            'current_page' => 'feed',
            'tweets' => $tweets,
            'users' => $users,
            'connectedUserEmail' => $connectedUserEmail
        ]);
    }
}

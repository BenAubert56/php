<?php

namespace App\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ProfileController extends AbstractController
{
    #[Route('/profile/{email}', name: 'app_profile')]
    public function show(
        string $email,
        HttpClientInterface $client,
        RequestStack $requestStack
    ): Response {
        $session = $requestStack->getSession();
        $token = $session->get('auth_token');

        if (!$token) {
            $this->addFlash('error', 'Vous devez être connecté pour voir un profil.');
            return $this->redirectToRoute('app_login');
        }

        $response = $client->request('GET', "http://php/api/users/email/{$email}", [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
        ]);

        if (200 !== $response->getStatusCode()) {
            $this->addFlash('error', 'Impossible de charger les informations du profil.');
            return $this->redirectToRoute('app_feed');
        }

        $user = $response->toArray();
        $connectedUserEmail = $session->get('user_email');

        $tweetsResponse = $client->request('GET', "http://php/api/users/{$email}/tweets", [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
        ]);
        
        $tweets = $tweetsResponse->toArray();

        return $this->render('profile/show.html.twig', [
            'current_page' => 'profil',
            'user' => $user,
            'followers' => $user['followers'],
            'followings' => $user['followings'],
            'tweetCount' => $user['tweetCount'],
            'likeCount' => $user['likeCount'],
            'retweetCount' => $user['retweetCount'],
            'connectedUserEmail' => $connectedUserEmail,
            'tweets' => $tweets,
        ]);
        
    }
}

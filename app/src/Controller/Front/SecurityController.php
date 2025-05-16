<?php

namespace App\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SecurityController extends AbstractController
{
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    #[Route(path: '/login', name: 'app_login')]
public function login(Request $request, SessionInterface $session): Response
{
    $lastUsername = '';
    $error = null;

    if ($request->isMethod('POST')) {
        $email = $request->request->get('_username');
        $password = $request->request->get('_password');
        $lastUsername = $email;

        try {
            $response = $this->httpClient->request('POST', 'http://php/api/login', [
                'json' => [
                    'email' => $email,
                    'password' => $password,
                ],
            ]);

            if ($response->getStatusCode() === 200) {
                $data = $response->toArray();

                $session->set('auth_token', $data['token']);

                $this->addFlash('success', 'Connexion réussie');
                return $this->redirectToRoute('app_feed');
            }

            $data = $response->toArray(false);
            $error = $data['error'] ?? 'Erreur inconnue lors de la connexion.';

        } catch (\Exception $e) {
            $error = 'Erreur de connexion avec l\'API : ' . $e->getMessage();
        }
    }

    return $this->render('security/login.html.twig', [
        'last_username' => $lastUsername,
        'error' => $error,
        'current_page' => 'login',
    ]);
}

    #[Route(path: '/register', name: 'app_register')]
    public function register(Request $request): Response
    {
        $lastUsername = '';
        $lastEmail = '';
        $error = null;

        if ($request->isMethod('POST')) {
            $username = $request->request->get('_username');
            $email = $request->request->get('_email');
            $password = $request->request->get('_password');
            $confirmPassword = $request->request->get('_confirm_password');

            $lastUsername = $username;
            $lastEmail = $email;

            if ($password !== $confirmPassword) {
                $error = 'Les mots de passe ne correspondent pas.';
            } else {
                try {
                    $response = $this->httpClient->request('POST', 'http://php/api/register', [
                        'json' => [
                            'name' => $username,
                            'email' => $email,
                            'password' => $password,
                        ],
                    ]);

                    if ($response->getStatusCode() === 201) {
                        $this->addFlash('success', 'Inscription réussie. Connectez-vous.');
                        return $this->redirectToRoute('app_login');
                    }

                    $data = $response->toArray(false);
                    $error = $data['error'] ?? 'Erreur inconnue lors de l\'inscription.';

                } catch (\Exception $e) {
                    $error = 'Erreur de connexion avec l\'API : ' . $e->getMessage();
                }
            }
        }

        return $this->render('security/register.html.twig', [
            'last_username' => $lastUsername,
            'last_email' => $lastEmail,
            'error' => $error,
            'current_page' => 'register',
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('Cette méthode peut rester vide - Symfony interceptera la route /logout automatiquement.');
    }
}

<?php

namespace App\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FeedController extends AbstractController
{
    #[Route(path: '/feed', name: 'app_feed')]
    public function index(): Response
    {
        return $this->render('feed/feed.html.twig', [
            'current_page' => 'feed',
        ]);
    }
}
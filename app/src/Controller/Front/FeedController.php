<?php

namespace App\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController; // <-- il faut ça !
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FeedController extends AbstractController
{
    #[Route(path: '/feed', name: 'app_feed')]
    public function index(HttpClientInterface $client): Response
    {
        $response = $client->request('GET', 'http://php/api/tweets/', [
            'headers' => [
                'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE3NDczMDkyNzMsImV4cCI6MTc0NzMxMjg3Mywicm9sZXMiOlsiUk9MRV9VU0VSIl0sInVzZXJuYW1lIjoidGVzdHRAZXhhbXBsZS5jb20ifQ.b02y8dm2PamFT7cgrFpvBAJOZG3v3W3kujMi64oF--v95q6UigYM9-82iY4jc4jqin-hY69mtNHXzvbP5My9WVlsJZy7RxkGEhjkk6Em_t2fRmJRrmdduXsN79SMIYbVir-XSU_edDqt52Jav0ELlV8ITyHs3HtRh0s_VTcI2HB3EzcNPFukp8GJ9nTQGu0OjBEsxgXUYzdRRgShfy1HX61BaO4qbi37NNIeSPg-9Nyqk33BoLr_YQkU6RRy5XuPkD3sW_Q_Qw5-wIn5QlNcfNMvFkF2Ha4_jKNihcaFuskJ-HY3e3t7pS_tIC9_kLdke5_IsPojJW0auz34GcuMR-m8y_iryn8QWa7cuiUXUh9qaVlaCdM0BT7srCoOD5U4ztKGyDYUv3UngJr8sY1piRuk9c27jq5oWtNbLkx4WhqcmNx4lmYVsQMvzRf2CTSQ64WvFN1vNO6jvNVIzKejBKfYVw1Ui0uDWXKes5mTBXipCVFWeru06VtDCijmBSL-pG7GlRh5-g_e2X2wHeyZc6RBZHS-eyiTVd5pfzxGHHOjX5z9ErBMJFHYP5zSuZXfnVsm2n6mkXCbHu9qALo_T1tpRGu34PxepgLhX924hh7e0EHAugPc2YF7YijKu_vAJH3cJETmqpoLDmsWpK0pITsYe2StZWLxglTnYSVbGjM',
            ],
        ]);

        $tweets = $response->toArray();

        return $this->render('feed/feed.html.twig', [
            'current_page' => 'feed',
            'tweets' => $tweets,
        ]);
    }
}

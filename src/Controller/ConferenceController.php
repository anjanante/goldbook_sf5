<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ConferenceController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(): Response
    {
        dump($_ENV['DATABASE_URL']);
        return new Response(<<<EOF
<html>
    <body>
        <img src="/images/coming-soon.gif" />
    </body>
</html>
EOF
        );
    }
}

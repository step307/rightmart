<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class GetCountController extends AbstractController
{
    #[Route('/count', name: 'app_get_count', methods: ['GET'])]
    public function getCount(): JsonResponse
    {
        return $this->json([
            'count' => 'DUMMY',
        ]);
    }
}

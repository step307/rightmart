<?php

namespace App\Controller;

use App\Service\LogCounterInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

final class GetCountController extends AbstractController
{

    public function __construct(
        private readonly LogCounterInterface $logCounter,
    ) {
    }

    #[Route('/count', name: 'app_get_count', methods: ['GET'])]
    public function getCount(
        #[MapQueryParameter] ?array $serviceNames = [],
        #[MapQueryParameter] ?string $statusCode = null,
        #[MapQueryParameter] ?string $startDate = null,
        #[MapQueryParameter] ?string $endDate = null,
    ): JsonResponse
    {
        return $this->json([
            'count' => $this->logCounter->count($serviceNames, $statusCode, $startDate, $endDate),
        ]);
    }
}

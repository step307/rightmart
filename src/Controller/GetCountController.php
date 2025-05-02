<?php

namespace App\Controller;

use App\Enum\HttpStatusCode;
use App\Service\LogCounterInterface;
use DateMalformedStringException;
use DateTimeImmutable;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use TypeError;
use ValueError;

final class GetCountController extends AbstractController
{

    public function __construct(
        private readonly LogCounterInterface $logCounter,
    ) {
    }

    #[Route(path: '/count', name: 'app_get_count', methods: ['GET'], format: 'json')]
    #[OA\Response(
        response: 200,
        description: 'Returns count of stored log lines accordingly filter criteria.',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'count', type: 'integer', example: 300)
            ],
            type: 'object'
        )
    )]
    public function getCount(
        #[MapQueryParameter] ?array $serviceNames = [],
        #[MapQueryParameter] ?string $statusCode = null,
        #[MapQueryParameter] ?string $startDate = null,
        #[MapQueryParameter] ?string $endDate = null,
    ): JsonResponse
    {
        $statusCodeObject = $statusCode !== null ? $this->parseStatusCode($statusCode) : null;
        $startDateObject = $startDate !== null ? $this->parseDate($startDate) : null;
        $endDateObject = $endDate !== null ? $this->parseDate($endDate) : null;

        return $this->json([
            'count' => $this->logCounter->count(
                $serviceNames,
                $statusCodeObject,
                $startDateObject,
                $endDateObject
            ),
        ]);
    }

    /**
     * @throws BadRequestException
     */
    private function parseStatusCode(string $statusCode): HttpStatusCode
    {
        try {
            return HttpStatusCode::from($statusCode);
        } catch (TypeError|ValueError $e) {
            throw new BadRequestException(
                sprintf('Invalid status code: %s', $statusCode),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * @throws BadRequestException
     */
    public function parseDate(string $startDate): DateTimeImmutable
    {
        try {
            return new DateTimeImmutable($startDate);
        } catch (DateMalformedStringException $e) {
            throw new BadRequestException(
                sprintf('Invalid date string: %s', $e->getMessage()),
                $e->getCode(),
                $e
            );
        }
    }
}

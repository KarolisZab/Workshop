<?php

namespace App\Controller\API;

use App\Document\Workshop;
use App\Repository\WorkshopRepository;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/workshop')]
class WorkshopController extends AbstractController
{
    public function __construct(private DocumentManager $documentManager, private SerializerInterface $serializer)
    {
        
    }

    #[Route('/{id}', name: 'workshop_get', methods: ['GET'])]
    public function getWorkshop(Request $request, string $id)
    {
        /** @var WorkshopRepository $workshopRepository */
        $workshopRepository = $this->documentManager->getRepository(Workshop::class);

        $workshop = $workshopRepository->find($id);

        if($workshop === null)
        {
            return new JsonResponse('404 Not Found', JsonResponse::HTTP_NOT_FOUND);
        }

        return new JsonResponse($this->serializer->serialize($workshop, 'json'), JsonResponse::HTTP_OK, [], true);
    }
}

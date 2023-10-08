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

    #[Route('', name: 'workshop_getall', methods: ['GET'])]
    public function getWorkshopAll(Request $request)
    {
        /** @var WorkshopRepository $workshopRepository */
        $workshopRepository = $this->documentManager->getRepository(Workshop::class);

        $allWorkshops = $workshopRepository->findAll();

        return new JsonResponse($this->serializer->serialize($allWorkshops, 'json'), JsonResponse::HTTP_OK, [], true);
    }

    #[Route('', name: 'workshop_post', methods: ['POST'])]
    public function createWorkshop(Request $request)
    {

        $parameters = json_decode($request->getContent(), true);

        $workshop = new Workshop();
        $workshop->setTitle($parameters['title'])
            ->setCategory($parameters['category']);

        $this->documentManager->persist($workshop);
        $this->documentManager->flush();

        return new JsonResponse($this->serializer->serialize($workshop, 'json'), JsonResponse::HTTP_CREATED, [], true);
    }

    #[Route('/{id}', name: 'workshop_patch', methods: ['PATCH'])]
    public function updateWorkshop(Request $request, string $id)
    {
        /** @var WorkshopRepository $workshopRepository */
        $workshopRepository = $this->documentManager->getRepository(Workshop::class);

        $workshop = $workshopRepository->find($id);
        
        if($workshop === null)
        {
            return new JsonResponse('404 Not Found', JsonResponse::HTTP_NOT_FOUND);
        }

        // upd
        // pasiemu parametrus
        // is parametro set ant workshop
        // validation
        

        $this->documentManager->flush();

        return new JsonResponse($this->serializer->serialize($workshop, 'json'), JsonResponse::HTTP_OK, [], true);
    }

    // #[Route('/{workshopId}/worker/{workerId}', name: 'workshop_get', methods: ['GET'])]
    // public function getWorkshopWorker(Request $request, string $workshopId, string $workerId)
    // {
    //     /** @var WorkshopRepository $workshopRepository */
    //     $workshopRepository = $this->documentManager->getRepository(Workshop::class);

    //     $worker = $workerRepository->findOneBy([
    //         '_id' => $workerId,
    //         'workshopId' => $workshopId
    //     ]);

    //     if($worker === null)
    //     {
    //         return new JsonResponse('404 Not Found', JsonResponse::HTTP_NOT_FOUND);
    //     }

    //     return new JsonResponse($this->serializer->serialize($worker, 'json'), JsonResponse::HTTP_OK, [], true);
    // }
}

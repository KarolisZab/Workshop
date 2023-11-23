<?php

namespace App\Controller\API;

use App\Document\Workshop;
use App\Document\Worker;
use App\Document\Duty;
use App\Repository\WorkshopRepository;
use App\Repository\WorkerRepository;
use App\Repository\DutyRepository;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

#[Route('/api/workshop')]
class WorkshopController extends AbstractController
{
    public function __construct(
        private DocumentManager $documentManager,
        private SerializerInterface $serializer,
        private ValidatorInterface $validatorInterface
    ) { 
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

    #[Route('/{id}', name: 'workshop_post_to_id', methods: ['POST'])]
    public function postToId(Request $request, string $id)
    {
        if ($request->isMethod('POST')) {
            return new JsonResponse('POST request to /api/workshop/id is not allowed.', JsonResponse::HTTP_METHOD_NOT_ALLOWED);
        }
    }

    //#[IsGranted('ROLE_ADMIN')]
    #[Route('', name: 'workshop_post', methods: ['POST'])]
    public function createWorkshop(Request $request, ValidatorInterface $validator)
    {
        try {
            $parameters = json_decode($request->getContent(), true);

            $existingWorkshop = $this->documentManager->getRepository(Workshop::class)->findOneBy(['title' => $parameters['title']]);

            if ($existingWorkshop) {
                // Workshop with the same title already exists, return an error response
                    return new JsonResponse('Workshop with the same title already exists.', JsonResponse::HTTP_CONFLICT);
            }

            $workshop = new Workshop();
            $workshop->setTitle($parameters['title'])
                    ->setCategory($parameters['category']);

            $errors = $validator->validate($workshop);

            // Check if the user has ROLE_ADMIN, if not, throw an AccessDeniedException
            if (!$this->isGranted('ROLE_ADMIN')) {
                return new JsonResponse('Access denied', JsonResponse::HTTP_FORBIDDEN);
            }

            if (count($errors) > 0) {
                // Handle validation errors, for example, return a 400 Bad Request response
                $validationErrors = [];
                foreach ($errors as $error) 
                {
                    $validationErrors[$error->getPropertyPath()] = $error->getMessage();
                }

                return new JsonResponse($validationErrors, JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
            }

            $this->documentManager->persist($workshop);
            $this->documentManager->flush();

            return new JsonResponse($this->serializer->serialize($workshop, 'json'), JsonResponse::HTTP_CREATED, [], true);
        } 
        catch (\Exception $exception) 
        {
            return new JsonResponse($exception->getMessage(), 400);
            //return new JsonResponse($request->getContent(), 400, [], true);
        }
    }

    #[Route('/{id}', name: 'workshop_patch', methods: ['PATCH'])]
    public function updateWorkshop(Request $request, string $id, ValidatorInterface $validator)
    {
        try 
        {
            /** @var WorkshopRepository $workshopRepository */
        $workshopRepository = $this->documentManager->getRepository(Workshop::class);

        $workshop = $workshopRepository->find($id);
        
        if($workshop === null)
        {
            return new JsonResponse('404 Not Found', JsonResponse::HTTP_NOT_FOUND);
        }

        // pasiemu parametrus
        $parameters = json_decode($request->getContent(), true);

        // is parametro set ant workshop
        $workshop->setTitle($parameters['title'])
                ->setCategory($parameters['category']);

        // validation
        $errors = $validator->validate($workshop);
        
        // Check if the user has ROLE_ADMIN, if not, throw an AccessDeniedException
        if (!$this->isGranted('ROLE_ADMIN')) {
            return new JsonResponse('Access denied', JsonResponse::HTTP_FORBIDDEN);
        }

        if (count($errors) > 0) {
            // Handle validation errors, for example, return a 400 Bad Request response
            $validationErrors = [];
            foreach ($errors as $error) 
            {
                $validationErrors[$error->getPropertyPath()] = $error->getMessage();
            }

            return new JsonResponse($validationErrors, JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->documentManager->flush();

        return new JsonResponse($this->serializer->serialize($workshop, 'json'), JsonResponse::HTTP_OK, [], true);
        } 
        catch (\Exception $exception) 
        {
            return new JsonResponse($exception->getMessage(), 400);
        }
    }

    #[Route('/{id}', name: 'workshop_delete', methods: ['DELETE'])]
    public function deleteWorkshop(Request $request, string $id, ValidatorInterface $validator)
    {
        /** @var WorkshopRepository $workshopRepository */
        $workshopRepository = $this->documentManager->getRepository(Workshop::class);

        $workshop = $workshopRepository->find($id);
        
        // Check if the user has ROLE_ADMIN, if not, throw an AccessDeniedException
        if (!$this->isGranted('ROLE_ADMIN')) {
            return new JsonResponse('Access denied', JsonResponse::HTTP_FORBIDDEN);
        }

        if($workshop === null)
        {
            return new JsonResponse('404 Not Found', JsonResponse::HTTP_NOT_FOUND);
        }

        $this->documentManager->remove($workshop);
        $this->documentManager->flush();

        return new JsonResponse($this->serializer->serialize($workshop, 'json'), JsonResponse::HTTP_OK, [], true);
    }

    ////////// 2nd level domain ////////////////////////////////////////////////////////////////////////////////////////////////////

    #[Route('/{id}/workers/{workerId}', name: 'worker_get', methods: ['GET'])]
    public function getWorkshopWorker(Request $request, string $workerId, string $id)
    {
        // pagetint viena konkretu workshopo darbuotoja
        
        /** @var WorkshopRepository $workshopRepository */
        $workshopRepository = $this->documentManager->getRepository(Workshop::class);
        /** @var WorkerRepository $workerRepository */
        $workerRepository = $this->documentManager->getRepository(Worker::class);

        $workshop = $workshopRepository->find($id); // visu pirma randam to workshop route'o id workshopo.

        if($workshop === null)
        {
            return new JsonResponse('404 Workshop Not Found', JsonResponse::HTTP_NOT_FOUND);
        }

        $worker = $workerRepository->findOneBy([
            '_id' => $workerId,
            'workshopId' => $id
        ]);

        if($worker === null)
        {
            return new JsonResponse('404 Worker Not Found', JsonResponse::HTTP_NOT_FOUND);
        }

        return new JsonResponse($this->serializer->serialize($worker, 'json'), JsonResponse::HTTP_OK, [], true);
    }

    #[Route('/{id}/workers', name: 'get_workers_in_workshop', methods: ['GET'])]
    public function getWorkersInWorkshop(Request $request, string $id)
    {
        // Get all workers in a specific workshop

        /** @var WorkshopRepository $workshopRepository */
        $workshopRepository = $this->documentManager->getRepository(Workshop::class);
        /** @var WorkerRepository $workerRepository */
        $workerRepository = $this->documentManager->getRepository(Worker::class);

        /** @var Workshop $workshop */
        $workshop = $workshopRepository->find($id);

        if ($workshop === null) {
            return new JsonResponse('404 Workshop Not Found', JsonResponse::HTTP_NOT_FOUND);
        }

        $workers = $workerRepository->findBy(['workshopId' => $id]);

        return new JsonResponse($this->serializer->serialize($workers, 'json'), JsonResponse::HTTP_OK, [], true);
    }

    #[Route('/{id}/workers/{workerId}', name: 'workshop_worker_post_to_id', methods: ['POST'])]
    public function postToIdWorkers(Request $request, string $id, string $workerId)
    {
        if ($request->isMethod('POST')) {
            return new JsonResponse('POST request to /api/workshop/id/workers/id is not allowed.', JsonResponse::HTTP_METHOD_NOT_ALLOWED);
        }

        // Handle other scenarios, if needed
    }

    #[Route('/{id}/workers', name: 'workshop_worker_post', methods: ['POST'])]
    public function createWorkshopWorker(Request $request, ValidatorInterface $validator, string $id)
    {
        try {
            $parameters = json_decode($request->getContent(), true);

            $existingWorkshop = $this->documentManager->getRepository(Workshop::class)->findOneBy(['_id' => $id]);

            if ($existingWorkshop === null) {
                // Workshop with the same title already exists, return an error response
                    return new JsonResponse('404 Workshop doesn\'t exists.', JsonResponse::HTTP_NOT_FOUND);
            }
            
            $worker = new Worker();
            $worker->setName($parameters['name'])
                ->setSurname($parameters['surname'])
                ->setWorkshopId($id);

            $errors = $validator->validate($worker);

            if (count($errors) > 0) {
                // Handle validation errors, for example, return a 400 Bad Request response
                $validationErrors = [];
                foreach ($errors as $error) 
                {
                    $validationErrors[$error->getPropertyPath()] = $error->getMessage();
                }

                return new JsonResponse($validationErrors, JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
            }

            $this->documentManager->persist($worker);
            $this->documentManager->flush();


            return new JsonResponse($this->serializer->serialize($worker, 'json'), JsonResponse::HTTP_CREATED, [], true);
        } 
        catch (\Exception $exception) 
        {
            return new JsonResponse($exception->getMessage(), 400);
        }
    }

    #[Route('/{id}/workers/{workerId}', name: 'workshop_worker_patch', methods: ['PATCH'])]
    public function updateWorkshopWorker(Request $request, ValidatorInterface $validator, string $id, string $workerId)
    {
        try {
            $parameters = json_decode($request->getContent(), true);

            /** @var WorkshopRepository $workshopRepository */
            $workshopRepository = $this->documentManager->getRepository(Workshop::class);
            /** @var WorkerRepository $workerRepository */
            $workerRepository = $this->documentManager->getRepository(Worker::class);

            $existingWorkshop = $this->documentManager->getRepository(Workshop::class)->findOneBy(['_id' => $id]);

            if ($existingWorkshop === null) {
                // Workshop with the same title already exists, return an error response
                    return new JsonResponse('404 Workshop doesn\'t exists.', JsonResponse::HTTP_NOT_FOUND);
            }
            
            // 2. Patikrinti findint workeri pagal workerId (jeigu nera 404, jeigu yra ref 3.)
            $worker = $workerRepository->find($workerId);

            if($worker === null)
            {
                return new JsonResponse('404 Worker doesn\'t exists.', JsonResponse::HTTP_NOT_FOUND);
            }

            // 3. Jeigu yra, patikrinti ar is body atejes workshopId egzistuoja toks workshop'as (jeigu nera 404, jeigu yra - setint i worker, pakeist 
            // name is body ,surname is body, validation - persist ir done)
            $workshopIdFromBody = $parameters['workshopId'];
            $workshopFromBody = $workshopRepository->find($workshopIdFromBody);

            if($workshopFromBody === null)
            {
                return new JsonResponse('404 Workshop doesn\'t exists.', JsonResponse::HTTP_NOT_FOUND);
            }

            $worker->setName($parameters['name'])
                ->setSurname($parameters['surname'])
                ->setWorkshopId($workshopIdFromBody);
                //->setWorkshopId($parameters['workshopId']);

            $errors = $validator->validate($worker);

            if (count($errors) > 0) {
                // Handle validation errors, for example, return a 400 Bad Request response
                $validationErrors = [];
                foreach ($errors as $error) 
                {
                    $validationErrors[$error->getPropertyPath()] = $error->getMessage();
                }

                return new JsonResponse($validationErrors, JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
            }

            $this->documentManager->flush();

            return new JsonResponse($this->serializer->serialize($worker, 'json'), JsonResponse::HTTP_CREATED, [], true);
        } 
        catch (\Exception $exception) 
        {
            return new JsonResponse($exception->getMessage(), 400);
        }
    }

    #[Route('/{id}/workers/{workerId}', name: 'workshop_worker_delete', methods: ['DELETE'])]
    public function deleteWorkshopWorker(Request $request, string $id, ValidatorInterface $validator, string $workerId)
    {
        /** @var WorkshopRepository $workshopRepository */
        $workshopRepository = $this->documentManager->getRepository(Workshop::class);
        /** @var WorkerRepository $workerRepository */
        $workerRepository = $this->documentManager->getRepository(Worker::class);

        $workshop = $workshopRepository->find($id);
        
        if($workshop === null)
        {
            return new JsonResponse('404 Workshop Not Found', JsonResponse::HTTP_NOT_FOUND);
        }

        $worker = $workerRepository->findOneBy([
            '_id' => $workerId,
            'workshopId' => $id
        ]);

        if($worker === null)
        {
            return new JsonResponse('404 Worker Not Found', JsonResponse::HTTP_NOT_FOUND);
        }

        $this->documentManager->remove($worker);
        $this->documentManager->flush();

        return new JsonResponse($this->serializer->serialize($worker, 'json'), JsonResponse::HTTP_OK, [], true);
    }    


    ////////////////////////////// 3rd level ////////////////////////////////////////////////////////////////////////////

    #[Route('/{id}/workers/{workerId}/duties/{dutyId}', name: 'worker_duty_get', methods: ['GET'])]
    public function getWorkshopWorkerDuty(Request $request, string $workerId, string $id, string $dutyId)
    {
        // pagetint viena konkrecius workshopo darbuotojo duties
        
        /** @var WorkshopRepository $workshopRepository */
        $workshopRepository = $this->documentManager->getRepository(Workshop::class);
        /** @var WorkerRepository $workerRepository */
        $workerRepository = $this->documentManager->getRepository(Worker::class);
        /** @var DutyRepository $dutyRepository */
        $dutyRepository = $this->documentManager->getRepository(Duty::class);

        $workshop = $workshopRepository->find($id);
        
        if($workshop === null)
        {
            return new JsonResponse('404 Workshop Not Found', JsonResponse::HTTP_NOT_FOUND);
        }

        $worker = $workerRepository->find($workerId); // visu pirma randam to workshop route'o id workshopo.

        if($worker === null)
        {
            return new JsonResponse('404 Worker Not Found', JsonResponse::HTTP_NOT_FOUND);
        }

        $duties = $dutyRepository->findOneBy([
            '_id' => $dutyId,
            'workerId' => $workerId,
            'workshopId' => $id
        ]);

        if($duties === null)
        {
            return new JsonResponse('404 Duties Not Found', JsonResponse::HTTP_NOT_FOUND);
        }

        return new JsonResponse($this->serializer->serialize($duties, 'json'), JsonResponse::HTTP_OK, [], true);
    }

    #[Route('/{id}/workers/{workerId}/duties', name: 'get_worker_duties_in_workshop', methods: ['GET'])]
    public function getWorkerDuties(Request $request, string $id, string $workerId)
    {
        // Get all duties in a specific workshop

        /** @var WorkshopRepository $workshopRepository */
        $workshopRepository = $this->documentManager->getRepository(Workshop::class);
        /** @var WorkerRepository $workerRepository */
        $workerRepository = $this->documentManager->getRepository(Worker::class);
        /** @var DutyRepository $dutyRepository */
        $dutyRepository = $this->documentManager->getRepository(Duty::class);

        /** @var Workshop $workshop */
        $workshop = $workshopRepository->find($id);

        if ($workshop === null) {
            return new JsonResponse('404 Workshop Not Found', JsonResponse::HTTP_NOT_FOUND);
        }

        $workers = $workerRepository->find($workerId);

        if($workers === null)
        {
            return new JsonResponse('404 Worker Not Found', JsonResponse::HTTP_NOT_FOUND);
        }

        $duties = $dutyRepository->findBy(['workerId' => $workerId, 'workshopId' => $id]);

        if($duties === null)
        {
            return new JsonResponse('404 Duties Not Found', JsonResponse::HTTP_NOT_FOUND);
        }

        return new JsonResponse($this->serializer->serialize($duties, 'json'), JsonResponse::HTTP_OK, [], true);
    }

    #[Route('/{id}/workers/{workerId}/duties/{dutyId}', name: 'workshop_worker_duties_post_to_id', methods: ['POST'])]
    public function postToIdDuties(Request $request, string $id, string $workerId, string $dutyId)
    {
        if ($request->isMethod('POST')) {
            return new JsonResponse('POST request to /api/workshop/id/workers/id/duties/ud is not allowed.', JsonResponse::HTTP_METHOD_NOT_ALLOWED);
        }
    }

    #[Route('/{id}/workers/{workerId}/duties', name: 'workshop_worker_duty_post', methods: ['POST'])]
    public function createWorkshopWorkerDuty(Request $request, ValidatorInterface $validator, string $id, string $workerId)
    {
        try {
            $parameters = json_decode($request->getContent(), true);

            $existingWorkshop = $this->documentManager->getRepository(Workshop::class)->findOneBy(['_id' => $id]);

            if ($existingWorkshop === null) {
                // Workshop with the same title already exists, return an error response
                    return new JsonResponse('404 Workshop doesn\'t exists.', JsonResponse::HTTP_NOT_FOUND);
            }
            
            $existingWorker = $this->documentManager->getRepository(Worker::class)->findOneBy(['_id' => $workerId]);

            if($existingWorker === null) {
                return new JsonResponse('404 Worker doesn\'t exists.', JsonResponse::HTTP_NOT_FOUND);
            }

            $duty = new Duty();
            $duty->setDuty($parameters['duty'])
                ->setDescription($parameters['description'])
                ->setWorkerId($workerId)
                ->setWorkshopId($id);

            $errors = $validator->validate($duty);

            if (count($errors) > 0) {
                // Handle validation errors, for example, return a 400 Bad Request response
                $validationErrors = [];
                foreach ($errors as $error) 
                {
                    $validationErrors[$error->getPropertyPath()] = $error->getMessage();
                }

                return new JsonResponse($validationErrors, JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
            }

            $this->documentManager->persist($duty);
            $this->documentManager->flush();


            return new JsonResponse($this->serializer->serialize($duty, 'json'), JsonResponse::HTTP_CREATED, [], true);
        } 
        catch (\Exception $exception) 
        {
            return new JsonResponse($exception->getMessage(), 400);
        }
    }

    #[Route('/{id}/workers/{workerId}/duties/{dutyId}', name: 'workshop_worker_duty_patch', methods: ['PATCH'])]
    public function updateWorkshopWorkerDuty(Request $request, ValidatorInterface $validator, string $id, string $workerId, string $dutyId)
    {
        try {
            $parameters = json_decode($request->getContent(), true);

            /** @var WorkshopRepository $workshopRepository */
            $workshopRepository = $this->documentManager->getRepository(Workshop::class);
            /** @var WorkerRepository $workerRepository */
            $workerRepository = $this->documentManager->getRepository(Worker::class);
            /** @var DutyRepository $dutyRepository */
            $dutyRepository = $this->documentManager->getRepository(Duty::class);

            $existingWorkshop = $this->documentManager->getRepository(Workshop::class)->findOneBy(['_id' => $id]);

            if ($existingWorkshop === null) {
                // Workshop with the same title already exists, return an error response
                    return new JsonResponse('404 Workshop doesn\'t exists.', JsonResponse::HTTP_NOT_FOUND);
            }

            $workshop = $workshopRepository->find($id);
            
            if($workshop === null) 
            {
                return new JsonResponse('404 Workshop doesn\'t exists.', JsonResponse::HTTP_NOT_FOUND);
            }

            // 2. Patikrinti findint workeri pagal workerId (jeigu nera 404, jeigu yra ref 3.)
            $worker = $workerRepository->find($workerId);

            if($worker === null)
            {
                return new JsonResponse('404 Worker doesn\'t exists.', JsonResponse::HTTP_NOT_FOUND);
            }

            $duty = $dutyRepository->find($dutyId);

            if($duty === null)
            {
                return new JsonResponse('404 Duty doesn\'t exists.', JsonResponse::HTTP_NOT_FOUND);
            }

            //$workerIdFromBody = $parameters['workerId'];
            //$workerFromBody = $workerRepository->find($workerIdFromBody);

            // if($workerFromBody === null)
            // {
            //     return new JsonResponse('404 Worker doesn\'t exists.', JsonResponse::HTTP_NOT_FOUND);
            // }

            $duty->setDuty($parameters['duty'])
                ->setDescription($parameters['description'])
                ->setWorkerId($workerId);
                //->setWorkerId($parameters['workerId']);

            $errors = $validator->validate($duty);

            if (count($errors) > 0) {
                // Handle validation errors, for example, return a 400 Bad Request response
                $validationErrors = [];
                foreach ($errors as $error) 
                {
                    $validationErrors[$error->getPropertyPath()] = $error->getMessage();
                }

                return new JsonResponse($validationErrors, JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
            }

            $this->documentManager->flush();

            return new JsonResponse($this->serializer->serialize($duty, 'json'), JsonResponse::HTTP_OK, [], true);
        } 
        catch (\Exception $exception)
        {
            return new JsonResponse($exception->getMessage(), 400);
        }
    }

    #[Route('/{id}/workers/{workerId}/duties/{dutyId}', name: 'workshop_worker_duty_delete', methods: ['DELETE'])]
    public function deleteWorkshopWorkerDuty(Request $request, string $id, ValidatorInterface $validator, string $workerId, string $dutyId)
    {
        /** @var WorkshopRepository $workshopRepository */
        $workshopRepository = $this->documentManager->getRepository(Workshop::class);
        /** @var WorkerRepository $workerRepository */
        $workerRepository = $this->documentManager->getRepository(Worker::class);
        /** @var DutyRepository $dutyRepository */
        $dutyRepository = $this->documentManager->getRepository(Duty::class);

        $workshop = $workshopRepository->find($id);
        
        if($workshop === null)
        {
            return new JsonResponse('404 Workshop Not Found', JsonResponse::HTTP_NOT_FOUND);
        }

        $worker = $workerRepository->find($workerId);

        if($worker === null)
        {
            return new JsonResponse('404 Worker Not Found', JsonResponse::HTTP_NOT_FOUND);
        }

        $duty = $dutyRepository->findOneBy([
            '_id' => $dutyId,
            'workerId' => $workerId,
            'workshopId' => $id
        ]);

        if($duty === null)
        {
            return new JsonResponse('404 Duty Not Found', JsonResponse::HTTP_NOT_FOUND);
        }

        $this->documentManager->remove($duty);
        $this->documentManager->flush();

        return new JsonResponse($this->serializer->serialize($duty, 'json'), JsonResponse::HTTP_OK, [], true);
    } 
}

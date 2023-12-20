<?php

namespace App\Controller\API;

use App\Document\Workshop;
use App\Document\Worker;
use App\Document\Duty;
use App\Document\User;
use App\Repository\WorkshopRepository;
use App\Repository\WorkerRepository;
use App\Repository\DutyRepository;
use App\Repository\UserRepository;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use \Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;

#[Route('/api/workshop')]
class WorkshopController extends AbstractController
{
    public function __construct(
        private DocumentManager $documentManager,
        private SerializerInterface $serializer,
        private ValidatorInterface $validatorInterface,
        private Security $security
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

    #[Route('', name: 'workshop_getall', methods: ['GET', 'OPTIONS'])]
    public function getWorkshopAll(Request $request): Response
    {
        if ($request->getMethod() === 'OPTIONS') {
            // Handle preflight OPTIONS request
            $response = new Response();
            $response->headers->set('Access-Control-Allow-Origin', 'http://localhost:3000');
            $response->headers->set('Access-Control-Allow-Methods', 'GET, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization');
            // Add other necessary headers
    
            return $response;
        }
        
        /** @var WorkshopRepository $workshopRepository */
        $workshopRepository = $this->documentManager->getRepository(Workshop::class);

        $allWorkshops = $workshopRepository->findAll();
        
        //return new JsonResponse($this->serializer->serialize($allWorkshops, 'json'), JsonResponse::HTTP_OK, [], true);
        $response = new JsonResponse($this->serializer->serialize($allWorkshops, 'json'), JsonResponse::HTTP_OK, [], true);
        // Add other necessary headers

        return $response;
    }

    #[Route('/{id}', name: 'workshop_post_to_id', methods: ['POST'])]
    public function postToId(Request $request, string $id)
    {
        if ($request->isMethod('POST')) {
            return new JsonResponse('POST request to /api/workshop/id is not allowed.', JsonResponse::HTTP_METHOD_NOT_ALLOWED);
        }
    }

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
            /** @var UserRepository $userRepository */
            $userRepository = $this->documentManager->getRepository(User::class);

            if ($existingWorkshop === null) {
                // Workshop with the same title already exists, return an error response
                    return new JsonResponse('404 Workshop doesn\'t exists.', JsonResponse::HTTP_NOT_FOUND);
            }
            
            $userEmail = $parameters['email'];

            if (!$userEmail) {
                return new JsonResponse('Email not provided.', JsonResponse::HTTP_BAD_REQUEST);
            }

            $user = $userRepository->findOneBy(['email' => $userEmail]);

            if ($user === null) {
                return new JsonResponse('404 User with provided email not found.', JsonResponse::HTTP_NOT_FOUND);
            }

            $worker = new Worker();
            $worker->setName($parameters['name'])
                ->setSurname($parameters['surname'])
                ->setWorkshopId($id);

            // Check if the user has ROLE_ADMIN, if not, throw an AccessDeniedException
            if (!$this->isGranted('ROLE_ADMIN')) {
                return new JsonResponse('Access denied', JsonResponse::HTTP_FORBIDDEN);
            }

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

            //$this->documentManager->persist($worker);

            // Check if the user does not have a workerId set
            if (empty($user->getWorkerId())) {
                $this->documentManager->persist($worker);

                // Associate the worker with the user
                $user->setWorkerId($worker->getId());

                $this->documentManager->flush();

                return new JsonResponse($this->serializer->serialize($worker, 'json'), JsonResponse::HTTP_CREATED, [], true);
            } else {
                return new JsonResponse('User already has a worker associated.', JsonResponse::HTTP_BAD_REQUEST);
            }

            // //Associate the worker with the user
            // $user->setWorkerId($worker->getId());

            // $this->documentManager->flush();

            // return new JsonResponse($this->serializer->serialize($worker, 'json'), JsonResponse::HTTP_CREATED, [], true);
        } 
        catch (\Exception $exception) 
        {
            return new JsonResponse($exception->getMessage(), 400);
        }
    }

    #[Route('/{id}/workers/{workerId}', name: 'workshop_worker_patch', methods: ['PATCH'])]
    public function updateWorkshopWorker(Request $request, ValidatorInterface $validator, string $id, string $workerId, Security $security)
    {
        try {
            $parameters = json_decode($request->getContent(), true);

            /** @var WorkshopRepository $workshopRepository */
            $workshopRepository = $this->documentManager->getRepository(Workshop::class);
            /** @var WorkerRepository $workerRepository */
            $workerRepository = $this->documentManager->getRepository(Worker::class);
            /** @var UserRepository $userRepository */
            $userRepository = $this->documentManager->getRepository(User::class);

            $existingWorkshop = $this->documentManager->getRepository(Workshop::class)->findOneBy(['_id' => $id]);

            if ($existingWorkshop === null) {
                // Workshop with the same title already exists, return an error response
                    return new JsonResponse('404 Workshop doesn\'t exists.', JsonResponse::HTTP_NOT_FOUND);
            }

            // // Get the authenticated user
            // $user = $security->getUser();
            
            // // Find the user by workerId
            // $targetUser = $userRepository->findOneBy(['workerId' => $workerId]);

            // // if ($user === null || ($user->getWorkerId() === '' && !$this->isGranted('ROLE_ADMIN'))) {
            // //     return new JsonResponse('Access denied', JsonResponse::HTTP_FORBIDDEN);
            // // }

            // if ($user === null || !($user instanceof User) || ($user->getWorkerId() !== $workerId && !$this->isGranted('ROLE_ADMIN'))) {
            //     return new JsonResponse('Access denied', JsonResponse::HTTP_FORBIDDEN);
            // }

            // if ($targetUser === null) {
            //     return new JsonResponse('404 Worker doesn\'t exist.', JsonResponse::HTTP_NOT_FOUND);
            // }

            // $user = $security->getUser();

            // if ($user === null || !($user instanceof User)) {
            //     return new JsonResponse('Access denied', JsonResponse::HTTP_FORBIDDEN);
            // }

            // // Find the worker by workerId
            // $worker = $workerRepository->findOneBy(['_id' => $workerId]);

            // if ($worker === null) {
            //     return new JsonResponse('404 Worker doesn\'t exist.', JsonResponse::HTTP_NOT_FOUND);
            // }

            // // Check if the user is authorized to modify this worker
            // if ($user->getWorkerId() !== $workerId && !$this->isGranted('ROLE_ADMIN')) {
            //     return new JsonResponse('Access denied', JsonResponse::HTTP_FORBIDDEN);
            // }

            // Check if the user has ROLE_ADMIN, if not, throw an AccessDeniedException
            if (!$this->isGranted('ROLE_ADMIN')) {
                return new JsonResponse('Access denied', JsonResponse::HTTP_FORBIDDEN);
            }

            // 2. Patikrinti findint workeri pagal workerId (jeigu nera 404, jeigu yra ref 3.)
            $worker = $workerRepository->find($workerId);

            if($worker === null)
            {
                return new JsonResponse('404 Worker doesn\'t exists.', JsonResponse::HTTP_NOT_FOUND);
            }

            // 3. Jeigu yra, patikrinti ar is body atejes workshopId egzistuoja toks workshop'as (jeigu nera 404, jeigu yra - setint i worker, pakeist 
            // name is body ,surname is body, validation - persist ir done)
            //$workshopIdFromBody = $parameters['workshopId'];
            //$workshopFromBody = $workshopRepository->find($id);

            if($id === null)
            {
                return new JsonResponse('404 Workshop doesn\'t exists.', JsonResponse::HTTP_NOT_FOUND);
            }

            $worker->setName($parameters['name'])
                ->setSurname($parameters['surname'])
                ->setWorkshopId($id);
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

    
    // #[Route('/{id}/workers/{workerId}', name: 'workshop_worker_patch', methods: ['PATCH'])]
    // public function updateWorkshopWorker(
    //     Request $request,
    //     ValidatorInterface $validator,
    //     string $id,
    //     string $workerId,
    //     Security $security
    // ) {
    //     try {
    //         $parameters = json_decode($request->getContent(), true);

    //         /** @var WorkerRepository $workerRepository */
    //         $workerRepository = $this->documentManager->getRepository(Worker::class);
    //         $user = $security->getUser();

    //         // Check if the user has ROLE_ADMIN, if not, throw an AccessDeniedException
    //         if (!$this->isGranted('ROLE_ADMIN')) {
    //             return new JsonResponse('Access denied', JsonResponse::HTTP_FORBIDDEN);
    //         }

    //         // Find the worker being updated
    //         $worker = $workerRepository->find($workerId);

    //         if ($worker === null) {
    //             return new JsonResponse('404 Worker doesn\'t exist.', JsonResponse::HTTP_NOT_FOUND);
    //         }

    //         // Update worker details
    //         $worker->setName($parameters['name'] ?? $worker->getName())
    //             ->setSurname($parameters['surname'] ?? $worker->getSurname())
    //             ->setWorkshopId($id);

    //         // Validate and flush changes
    //         $errors = $validator->validate($worker);

    //         if (count($errors) > 0) {
    //             $validationErrors = [];
    //             foreach ($errors as $error) {
    //                 $validationErrors[$error->getPropertyPath()] = $error->getMessage();
    //             }

    //             return new JsonResponse($validationErrors, JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
    //         }

    //         $this->documentManager->flush();

    //         return new JsonResponse($this->serializer->serialize($worker, 'json'), JsonResponse::HTTP_CREATED, [], true);
    //     } catch (\Exception $exception) {
    //         return new JsonResponse($exception->getMessage(), 400);
    //     }
    // }

    #[Route('/{id}/workers/{workerId}', name: 'workshop_worker_delete', methods: ['DELETE'])]
    public function deleteWorkshopWorker(Request $request, string $id, ValidatorInterface $validator, string $workerId)
    {
        /** @var WorkshopRepository $workshopRepository */
        $workshopRepository = $this->documentManager->getRepository(Workshop::class);
        /** @var WorkerRepository $workerRepository */
        $workerRepository = $this->documentManager->getRepository(Worker::class);

        $workshop = $workshopRepository->find($id);
        
        // Check if the user has ROLE_ADMIN, if not, throw an AccessDeniedException
        if (!$this->isGranted('ROLE_ADMIN')) {
            return new JsonResponse('Access denied', JsonResponse::HTTP_FORBIDDEN);
        }

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
    public function getWorkshopWorkerDuty(Request $request, string $workerId, string $id, string $dutyId, Security $security)
    {
        // pagetint viena konkrecius workshopo darbuotojo duties
        
        /** @var WorkshopRepository $workshopRepository */
        $workshopRepository = $this->documentManager->getRepository(Workshop::class);
        /** @var WorkerRepository $workerRepository */
        $workerRepository = $this->documentManager->getRepository(Worker::class);
        /** @var DutyRepository $dutyRepository */
        $dutyRepository = $this->documentManager->getRepository(Duty::class);

        $workshop = $workshopRepository->find($id);
        
        // Get the authenticated user via the Security component
        $user = $security->getUser();

        if ($user === null || !($user instanceof User)) {
            return new JsonResponse('User not authenticated or not found.', JsonResponse::HTTP_FORBIDDEN);
        }

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
    public function getWorkerDuties(Request $request, string $id, string $workerId, Security $security)
    {
        // Get all duties in a specific workshop

        /** @var WorkshopRepository $workshopRepository */
        $workshopRepository = $this->documentManager->getRepository(Workshop::class);
        /** @var WorkerRepository $workerRepository */
        $workerRepository = $this->documentManager->getRepository(Worker::class);
        /** @var DutyRepository $dutyRepository */
        $dutyRepository = $this->documentManager->getRepository(Duty::class);

        // Get the authenticated user via the Security component
        $user = $security->getUser();

        if ($user === null || !($user instanceof User)) {
            return new JsonResponse('User not authenticated or not found.', JsonResponse::HTTP_FORBIDDEN);
        }


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
    public function createWorkshopWorkerDuty(Request $request, ValidatorInterface $validator, string $id, string $workerId, Security $security)
    {
        try {
            $parameters = json_decode($request->getContent(), true);

            $existingWorkshop = $this->documentManager->getRepository(Workshop::class)->findOneBy(['_id' => $id]);
            /** @var UserRepository $userRepository */
            $userRepository = $this->documentManager->getRepository(User::class);

            if ($existingWorkshop === null) {
                // Workshop with the same title already exists, return an error response
                    return new JsonResponse('404 Workshop doesn\'t exists.', JsonResponse::HTTP_NOT_FOUND);
            }
            
            $existingWorker = $this->documentManager->getRepository(Worker::class)->findOneBy(['_id' => $workerId]);

            if($existingWorker === null) {
                return new JsonResponse('404 Worker doesn\'t exists.', JsonResponse::HTTP_NOT_FOUND);
            }

             // Get the authenticated user via the Security component
            $user = $security->getUser();

            if ($user === null || !($user instanceof User)) {
                return new JsonResponse('User not authenticated or not found.', JsonResponse::HTTP_NOT_FOUND);
            }

            // Check if the authenticated user has the correct workerId or is an admin (replace with your logic)
            if (!($this->isGranted('ROLE_ADMIN') || $user->getWorkerId() === $workerId)) {
                return new JsonResponse('Access denied', JsonResponse::HTTP_FORBIDDEN);
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
    public function updateWorkshopWorkerDuty(Request $request, ValidatorInterface $validator, string $id, string $workerId, string $dutyId, Security $security)
    {
        try {
            $parameters = json_decode($request->getContent(), true);

            /** @var WorkshopRepository $workshopRepository */
            $workshopRepository = $this->documentManager->getRepository(Workshop::class);
            /** @var WorkerRepository $workerRepository */
            $workerRepository = $this->documentManager->getRepository(Worker::class);
            /** @var DutyRepository $dutyRepository */
            $dutyRepository = $this->documentManager->getRepository(Duty::class);
            /** @var UserRepository $userRepository */
            $userRepository = $this->documentManager->getRepository(User::class);

            $existingWorkshop = $this->documentManager->getRepository(Workshop::class)->findOneBy(['_id' => $id]);

            if ($existingWorkshop === null) {
                // Workshop with the same title already exists, return an error response
                    return new JsonResponse('404 Workshop doesn\'t exists.', JsonResponse::HTTP_NOT_FOUND);
            }

            // Find the user by workerId
            //$user = $userRepository->findOneBy(['workerId' => $workerId]);

            // Get the authenticated user
            $user = $security->getUser();

            // Check if user exists and is authenticated
            if ($user === null || !($user instanceof User)) {
                return new JsonResponse('User not found or not authenticated.', JsonResponse::HTTP_NOT_FOUND);
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

            // Check if the authenticated user matches the duty's workerId or is an admin
            if ($user->getWorkerId() !== $duty->getWorkerId() && !$this->isGranted('ROLE_ADMIN')) {
                return new JsonResponse('Access denied', JsonResponse::HTTP_FORBIDDEN);
            }

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
    public function deleteWorkshopWorkerDuty(Request $request, string $id, ValidatorInterface $validator, string $workerId, string $dutyId, Security $security)
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

        // Get the authenticated user
        $user = $security->getUser();

        // Check if user is authenticated
        if ($user === null || !($user instanceof User)) {
            return new JsonResponse('User not found or not authenticated.', JsonResponse::HTTP_NOT_FOUND);
        }

        // Check if the authenticated user matches the duty's workerId or is an admin
        if ($user->getWorkerId() !== $duty->getWorkerId() && !$this->isGranted('ROLE_ADMIN')) {
            return new JsonResponse('Access denied', JsonResponse::HTTP_FORBIDDEN);
        }

        $this->documentManager->remove($duty);
        $this->documentManager->flush();

        return new JsonResponse($this->serializer->serialize($duty, 'json'), JsonResponse::HTTP_OK, [], true);
    } 
}

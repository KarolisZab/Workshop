<?php

namespace App\Controller\API;

use App\Document\User;
use App\Repository\UserRepository;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

class AuthController extends AbstractController
{
    
    public function __construct(private DocumentManager $documentManager, private UserPasswordHasherInterface $passwordHasher, private SerializerInterface $serializer, private JWTTokenManagerInterface $jwtManager)
    {
    
    }
    
    #[Route('/api/register', name: 'api_register')]
    public function register(Request $request)
    {   
        $decoded = json_decode($request->getContent(), true);
        $email = $decoded['email'];
        $plaintextPassword = $decoded['password'];

        /** @var UserRepository $userRepository */
        $userRepository = $this->documentManager->getRepository(User::class);

        $existingUser = $userRepository->findOneBy(['email' => $email]);

        if ($existingUser !== null) {
            return new JsonResponse('User with this email already exists', JsonResponse::HTTP_CONFLICT);
            // You can customize the error message or status code as needed
        }
    
        // Check if a user with the given username already exists
        $existingUsername = $userRepository->findOneBy(['username' => $email]);
    
        if ($existingUsername !== null) {
            return new JsonResponse('User with this username already exists', JsonResponse::HTTP_CONFLICT);
            // You can customize the error message or status code as needed
        }

        $user = new User();
        $hashedPassword = $this->passwordHasher->hashPassword(
            $user, 
            $plaintextPassword
        );

        $user->setPassword($hashedPassword);
        $user->setEmail($email);
        $user->setUsername($email);
        $user->setRoles(['ROLE_USER']);

        $this->documentManager->persist($user);
        $this->documentManager->flush();

        // TODO: Give JsonResponse instead maybe with registered user info (201 Created)
        return $this->json(['message' => 'Registered successfully!']);
    }

    #[Route('/api/login', name: 'api_login')]
    public function login(Request $request)
    {
        try
        {
            $credentials = json_decode($request->getContent(), true);

            // Retrieve the user from your user repository or storage
            /** @var User $user */
            $user = $this->documentManager->getRepository(User::class)->findOneBy(['username' => $credentials['username']]);
            

            if (!$user instanceof UserInterface) {
                throw new BadCredentialsException('Invalid username or password');
            }

            // Check if the password matches
            if (!$this->passwordHasher->isPasswordValid($user, $credentials['password'])) {
                throw new BadCredentialsException('Invalid username or password');
            }

            // $payload = [
            //     '_id' => $user->getUserId(), // Adjust this according to your User entity structured
            //     // 'username' => $user->getUsername(),
            //     // Include other necessary data in the payload
            // ];
    

            // If the credentials are valid, generate a JWT token
            $token = $this->jwtManager->create($user);

            // Return a response with the username and access token
            return $this->json([
                'username' => $user->getUsername(),
                'access_token' => $token,
            ]);
        } catch(BadCredentialsException $e) {
            return new JsonResponse('Invalid username or password', 401);
        }
    }

    #[Route('/api/registeradmin', name: 'api_register_admin')]
    public function registerAdmin(Request $request)
    {   
        $decoded = json_decode($request->getContent(), true);
        $email = $decoded['email'];
        $plaintextPassword = $decoded['password'];

        /** @var UserRepository $userRepository */
        $userRepository = $this->documentManager->getRepository(User::class);

        $existingUser = $userRepository->findOneBy(['email' => $email]);

        if ($existingUser !== null) {
            return new JsonResponse('User with this email already exists', JsonResponse::HTTP_CONFLICT);
            // You can customize the error message or status code as needed
        }
    
        // Check if a user with the given username already exists
        $existingUsername = $userRepository->findOneBy(['username' => $email]);
    
        if ($existingUsername !== null) {
            return new JsonResponse('User with this username already exists', JsonResponse::HTTP_CONFLICT);
            // You can customize the error message or status code as needed
        }

        $user = new User();
        $hashedPassword = $this->passwordHasher->hashPassword(
            $user, 
            $plaintextPassword
        );

        $user->setPassword($hashedPassword);
        $user->setEmail($email);
        $user->setUsername($email);
        $user->setRoles(['ROLE_ADMIN', 'ROLE_USER']);

        $this->documentManager->persist($user);
        $this->documentManager->flush();

        // TODO: Give JsonResponse instead maybe with registered user info
        return $this->json(['message' => 'Administrator registered successfully!']);
    }
}
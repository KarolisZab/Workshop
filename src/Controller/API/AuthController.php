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
use Gesdinet\JWTRefreshTokenBundle\Generator\RefreshTokenGeneratorInterface;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;

class AuthController extends AbstractController
{
    
    public function __construct(
        private DocumentManager $documentManager,
        private UserPasswordHasherInterface $passwordHasher,
        private SerializerInterface $serializer,
        private JWTTokenManagerInterface $jwtManager,
        private RefreshTokenGeneratorInterface $refreshTokenGenerator,
    )
    {}
    
    #[Route('/api/register', name: 'api_register')]
    public function register(Request $request)
    {   
        try {
            $decoded = json_decode($request->getContent(), true);

            // Check if email and password fields exist and are not empty
            if (!isset($decoded['email']) || empty($decoded['email']) || !isset($decoded['password']) || empty($decoded['password'])) {
                return new JsonResponse('Email and password fields cannot be null', JsonResponse::HTTP_BAD_REQUEST);
            }

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
            return $this->json(['message' => 'User registered successfully!']);
        } catch (\Exception $exception) 
        {
            return new JsonResponse($exception->getMessage(), 400);
        }
        
    }

    #[Route('/api/login', name: 'api_login')]
    public function login(Request $request, RefreshTokenGeneratorInterface $refreshTokenGenerator, RefreshTokenManagerInterface $refreshTokenManager)
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

            // Generate refresh token using the RefreshTokenGeneratorInterface
            $refreshToken = $refreshTokenGenerator->createForUserWithTtl($user, 1800);

            $refreshTokenManager->save($refreshToken);

            // Return a response with the username and access token
            return $this->json([
                'username' => $user->getUsername(),
                'roles' => $user->getRoles(),
                'access_token' => $token,
                'refresh_token' => $refreshToken->getRefreshToken()
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

    // #[Route('/api/refresh_token', name: 'refresh_token')]
    // public function refreshAccessToken(
    //     Request $request,
    //     RefreshTokenManagerInterface $refreshTokenManager,
    //     JWTTokenManagerInterface $jwtManager
    // ) {
    //     $data = json_decode($request->getContent(), true);
    
    //     if (!isset($data['refresh_token'])) {
    //         return new JsonResponse(['message' => 'Refresh token is required'], 400);
    //     }
    
    //     try {
    //         // Generate a new access token based on the refresh token
    //         //$refreshToken = $refreshTokenGenerator->get($data['refresh_token']);
    //         // Validate and retrieve the refresh token from the database or storage
    //         $refreshToken = $refreshTokenManager->get($data['refresh_token']);

    //         //if (!$refreshToken) {
    //           //  return new JsonResponse(['message' => 'Invalid or expired refresh token'], 401);
    //         //}

    //         // Retrieve user information associated with the refresh token (if needed)
    //         $user = $this->getUserFromRefreshToken($refreshToken);

    //         // Generate a new access token
    //         $accessToken = $jwtManager->create($user);

    //         // Return the new access token in the response
    //         return new JsonResponse(['access_token' => $accessToken]);
    //     } catch (\Exception $e) {
    //         return new JsonResponse(['message' => 'Something went wrong while refreshing the access token'], 500);
    //     }
    // }
    
    //Method to retrieve user information associated with the refresh token
    // private function getUserFromRefreshToken($refreshToken)
    // {
    //     // Assuming you store user information within the refresh token (e.g., username)
    //     $username = $refreshToken->getUsername();
    
    //     // Fetch user from your MongoDB using the DocumentManager
    //     $user = $this->documentManager->getRepository(User::class)->findOneBy(['username' => $username]);
    
    //     return $user; // Return the user instance associated with the refresh token

    //     //Ensure $refreshToken is not null before accessing methods
    // }

    // private function isRefreshTokenExpired($refreshToken)
    // {
    //     // Assuming $refreshToken contains your token entity or object
    //     $expirationTime = $refreshToken->getExpirationTime(); // Get the token's expiration timestamp

    //     return $expirationTime < new \DateTime(); // Check if the expiration time is in the past
    // }

    // #[Route('/api/refresh_token', name: 'refresh_token', methods: ['POST'])]
    // public function refreshAccessToken(
    //     Request $request,
    //     RefreshTokenManagerInterface $refreshTokenManager,
    //     JWTTokenManagerInterface $jwtManager
    // ): JsonResponse {
    //     $data = json_decode($request->getContent(), true);

    //     // Check if the refresh token is provided in the request body
    //     if (!isset($data['refresh_token'])) {
    //         return $this->json(['message' => 'Refresh token not provided'], 400);
    //     }

    //     // Retrieve the refresh token from the request body
    //     $refreshToken = $refreshTokenManager->get($data['refresh_token']);

    //     if (!$refreshToken) {
    //         return $this->json(['message' => 'Invalid refresh token'], 401);
    //     }

    //     // Retrieve the user associated with the refresh token
    //     $user = $this->getUserFromRefreshToken($refreshToken);

    //     if (!$user instanceof UserInterface) {
    //         return $this->json(['message' => 'User not found'], 401);
    //     }

    //     // Generate a new access token for the user
    //     $accessToken = $jwtManager->create($user);

    //     // Return the new access token in the response
    //     return $this->json(['access_token' => $accessToken]);
    // }
}
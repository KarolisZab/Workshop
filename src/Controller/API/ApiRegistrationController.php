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

class ApiRegistrationController extends AbstractController
{
    
    public function __construct(private DocumentManager $documentManager, private UserPasswordHasherInterface $passwordHasher, private SerializerInterface $serializer, private JWTTokenManagerInterface $jwtManager)
    {
    
    }
    
    #[Route('/api/register', name: 'api_register')]
    public function index(Request $request)
    {
        /** @var UserRepository $userRepository */
        $userRepository = $this->documentManager->getRepository(User::class);
        
        $decoded = json_decode($request->getContent(), true);
        $email = $decoded['email'];
        $plaintextPassword = $decoded['password'];

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

        return $this->json(['message' => 'Registered successfully!']);
    }

    // #[Route('api/login', name: 'api_login')]
    // public function login(Request $request)
    // {
    //     $credentials = json_decode($request->getContent(), true);

    //     // Retrieve the user from your user repository or storage
    //     $user = $this->documentManager->getRepository(User::class)->findOneBy(['username' => $credentials['username']]);
        
    //     if (!$user instanceof UserInterface) {
    //         throw new BadCredentialsException('Invalid username or password');
    //     }

    //     // Check if the password matches
    //     if (!$this->passwordHasher->isPasswordValid($user, $credentials['password'])) {
    //         throw new BadCredentialsException('Invalid username or password');
    //     }

    //     // If the credentials are valid, generate a JWT token
    //     $token = $this->generateJwtToken($user);

    //     // Return a response with the username and access token
    //     return $this->json([
    //         'username' => $user->getUsername(),
    //         'access_token' => $token,
    //     ]);
    // }

    // private function generateJwtToken(UserInterface $user): string
    // {
    //     // Generate JWT token logic using LexikJWTAuthenticationBundle's token manager
    //     // Assuming $jwtManager is injected or accessible here
    //     return $this->jwtManager->create(['username' => $user->getUsername()]);
    // }
}
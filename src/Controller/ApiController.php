<?php


namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Entity\ApiToken;
use App\Repository\ApiTokenRepository;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ApiController extends AbstractController
{
    /**
     * @Route ("/api/users", methods={"OPTIONS"})
     * @Route ("/api/user", methods={"OPTIONS"})
     * @Route ("/api/user/{id}", methods={"OPTIONS"})
     * @Route ("/api/login", methods={"OPTIONS"})
     */
    public function preflight(): JsonResponse
    {
        return new JsonResponse(
            '{}',
            JsonResponse::HTTP_OK,
            [
                'Access-Control-Allow-Headers' => 'Authorization, Content-Type',
                'Access-Control-Allow-Origin' => 'https://editor.swagger.io',
                'Access-Control-Allow-Methods' => 'PUT, DELETE'
            ],
            true
        );
    }

    /**
     * @Route ("/api/user", methods={"POST"}, name="saveUser")
     */
    public function saveUser(Request $request, UserPasswordEncoderInterface $passwordEncoder): JsonResponse
    {
        $jsonContent = $request->getContent();
        $content = json_decode($jsonContent);

        if (!$content || !isset($content->email) || !isset($content->roles) || !isset($content->password)) {
            throw $this->createNotFoundException('Required data must be provided. Please view the documentation for this API.');
        }
        $user = new User();
        $user->setEmail($content->email);
        $user->setRoles($content->roles);
        $user->setPassword($passwordEncoder->encodePassword(
            $user,
            $content->password
        ));

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse(
            $jsonContent,
            JsonResponse::HTTP_CREATED,
            [
                'Access-Control-Allow-Origin' => 'https://editor.swagger.io'
            ],
            true
        );
    }

    /**
     * @Route ("/api/user", methods={"PUT"}, name="updateUser")
     */
    public function updateUser(Request $request, UserPasswordEncoderInterface $passwordEncoder, UserRepository $userRepository): JsonResponse
    {
        $jsonContent = $request->getContent();
        $content = json_decode($jsonContent);
        if (!$content || !isset($content->id) || (!isset($content->email) && !isset($content->roles) && !isset($content->password))) {
            throw $this->createNotFoundException('Required data must be provided. Please view the documentation for this API.');
        }

        $user = $userRepository->find($content->id);
        if (!$user) {
            throw $this->createNotFoundException('No user found');
        }

        if (isset($content->email)) {
            $user->setEmail($content->email);
        }
        if (isset($content->roles)) {
            $user->setRoles($content->roles);
        }
        if (isset($content->password)) {
            $user->setPassword($passwordEncoder->encodePassword(
                $user,
                $content->password
            ));
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->flush();

        return new JsonResponse(
            $jsonContent,
            JsonResponse::HTTP_CREATED,
            [
                'Access-Control-Allow-Origin' => 'https://editor.swagger.io'
            ],
            true
        );
    }

    /**
     * @Route ("/api/users", methods={"GET"}, name="getAllUsers")
     */
    public function getAllUsers(UserRepository $userRepository, SerializerInterface $serializer): JsonResponse
    {
        $users = $userRepository->findAll();

        $jsonUsers = $serializer->serialize($users, 'json');

        return new JsonResponse(
            $jsonUsers,
            JsonResponse::HTTP_OK,
            [
                'Access-Control-Allow-Origin' => 'https://editor.swagger.io'
            ],
            true
        );
    }

    /**
     * @Route ("/api/user/{id}", methods={"GET"}, name="getOneUser")
     */
    public function getOneUser(UserRepository $userRepository,  SerializerInterface $serializer, $id): JsonResponse
    {
        $user = $userRepository->find($id);
        if (!$user) {
            throw $this->createNotFoundException('No user found');
        }
        $jsonUser = $serializer->serialize($user, 'json');

        return new JsonResponse(
            $jsonUser,
            JsonResponse::HTTP_OK,
            [
                'Access-Control-Allow-Origin' => 'https://editor.swagger.io'
            ],
            true
        );
    }

    /**
     * @Route ("/api/user/{id}", methods={"DELETE"}, name="deleteOneUser")
     *
     */
    public function deleteOneUser(ApiTokenRepository $apiTokenRepository, UserRepository $userRepository, SerializerInterface $serializer, $id): JsonResponse
    {
        $user = $userRepository->find($id);
        if (!$user) {
            throw $this->createNotFoundException('No user found');
        }
        $jsonUser = $serializer->serialize($user, 'json');

        $entityManager = $this->getDoctrine()->getManager();

        if ($apiToken = $apiTokenRepository->findOneBy(['user' => $user])) {
            $entityManager->remove($apiToken);
        }

        $entityManager->remove($user);
        $entityManager->flush();

        return new JsonResponse(
            $jsonUser,
            JsonResponse::HTTP_OK,
            [
                'Access-Control-Allow-Origin' => 'https://editor.swagger.io'
            ],
            true
        );

    }

    /**
     * @Route ("/api/login", methods={"POST"}, name="login")
     *
     */
    public function login(ApiTokenRepository $apiTokenRepository): JsonResponse
    {
        $user = $this->getUser();
        
        $token = bin2hex(random_bytes(60));

        $entityManager = $this->getDoctrine()->getManager();

        if ($apiToken = $apiTokenRepository->findOneBy(['user' => $user])) {
            $apiToken->setToken($token);
            $apiToken->setExpiresAt(new \DateTime('+6 hours'));
        } else {
            $apiToken = new ApiToken();
            $apiToken->setToken($token);
            $apiToken->setExpiresAt(new \DateTime('+6 hours'));
            $apiToken->setUser($user);

            $entityManager->persist($apiToken);
        }

        $entityManager->flush();

        return new JsonResponse(
            ['token' => $token],
            JsonResponse::HTTP_CREATED,
            [
                'Access-Control-Allow-Origin' => 'https://editor.swagger.io'
            ]
        );
    }

}
<?php


namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ApiController extends AbstractController
{
    /**
     * @param Request $request
     * @return Response
     * @Route ("/api/user", methods={"POST"})
     */
    public function saveUser(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $jsonContent = $request->getContent();
        $content = json_decode($jsonContent);

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

        return new Response(
            $jsonContent,
            Response::HTTP_CREATED,
            [
                'Content-Type' => 'application-json',
                'Access-Control-Allow-Origin' => 'https://editor.swagger.io'
            ]
        );
    }


    /**
     * @param Request $request
     * @return Response
     * @Route ("/api/user", methods={"PUT"})
     */
    public function updateUser(Request $request, UserPasswordEncoderInterface $passwordEncoder, UserRepository $userRepository)
    {
        $jsonContent = $request->getContent();
        $content = json_decode($jsonContent);

        $user = $userRepository->find($content->id);

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

        return new Response(
            $jsonContent,
            Response::HTTP_CREATED,
            [
                'Content-Type' => 'application-json',
                'Access-Control-Allow-Origin' => 'https://editor.swagger.io'
            ]
        );
    }

    /**
     * @return Response
     * @Route ("/api/user/{id}", methods={"OPTIONS"})
     */
    public function preflight()
    {
        return new Response(
            '{}',
            Response::HTTP_OK,
            [
                'Content-Type' => 'application-json',
                'Access-Control-Allow-Headers' => 'Content-Type',
                'Access-Control-Allow-Origin' => 'https://editor.swagger.io',
                'Access-Control-Allow-Methods' => 'PUT, DELETE'
            ]
        );
    }

    /**
     * @param UserRepository $user
     * @param SerializerInterface $serializer
     * @return Response
     * @Route ("/api/users", methods={"GET"})
     */
    public function getAllUsers(UserRepository $userRepository, SerializerInterface $serializer)
    {
        $users = $userRepository->findAll();

        $jsonUsers = $serializer->serialize($users, 'json');

        return new Response(
            $jsonUsers,
            Response::HTTP_OK,
            [
                'Content-Type' => 'application-json',
                'Access-Control-Allow-Origin' => 'https://editor.swagger.io'
            ]
        );
    }

    /**
     * @return Response
     * @Route ("/api/user/{id}", methods={"GET"})
     */
    public function getOneUser(UserRepository $userRepository,  SerializerInterface $serializer, $id)
    {
        $user = $userRepository->find($id);
        $jsonUser = $serializer->serialize($user, 'json');

        return new Response(
            $jsonUser,
            Response::HTTP_OK,
            [
                'Content-Type' => 'application-json',
                'Access-Control-Allow-Origin' => 'https://editor.swagger.io'
            ]
        );
    }

    /**
     * @Route ("/api/user/{id}", methods={"DELETE"})
     *
     */
    public function deleteOneUser(UserRepository $userRepository, SerializerInterface $serializer, $id)
    {
        $user = $userRepository->find($id);
        $jsonUser = $serializer->serialize($user, 'json');

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($user);
        $entityManager->flush();

        return new Response(
            $jsonUser,
            Response::HTTP_OK,
            [
                'Content-Type' => 'application-json',
                'Access-Control-Allow-Origin' => 'https://editor.swagger.io'
            ]
        );

    }

}
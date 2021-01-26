<?php


namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\User;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ApiController extends AbstractController
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * @param Request $request
     * @return Response
     * @Route ("/api/user",  methods={"POST"})
     */
    public function saveUser(Request $request): Response
    {
        $jsonContent = $request->getContent();
        $content = json_decode($jsonContent);

        $user = new User();
        $user->setEmail($content->email);
        $user->setRoles($content->roles);
        $user->setPassword($this->passwordEncoder->encodePassword(
            $user,
            $content->password
        ));

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        return new Response(
            $jsonContent,
            Response::HTTP_OK,
            ['Content-Type' => 'application-json']
        );
    }

    /**
     * @return Response
     * @Route ("/api/users",  methods={"GET"})
     */
    public function getUsers(SerializerInterface $serializer)
    {
        $user = $this->getDoctrine()->getRepository(User::class);
        $users = $user->findAll();

        $jsonUsers = $serializer->serialize($users, 'json');

        return new Response(
            $jsonUsers,
            Response::HTTP_OK,
            ['Content-Type' => 'application-json']
        );
    }

}
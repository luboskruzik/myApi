<?php


namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\User;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

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
        $user->setName($content->name);

        $user->setPassword($this->passwordEncoder->encodePassword(
            $user,
            $content->password
        ));
        $user->setRoles($content->role);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        return new Response($jsonContent);
    }

    /**
     * @return Response
     * @Route ("/api/user",  methods={"GET"})
     */
    public function getUsers()
    {
        $user = $this->getDoctrine()->getRepository(User::class);
        $users = $user->findAllEmailsRolesNames();
        $jsonUsers = json_encode($users);

        return new Response($jsonUsers);
    }

}
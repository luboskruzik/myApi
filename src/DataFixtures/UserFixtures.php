<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
	private $passwordEncoder;

	public function __construct(UserPasswordEncoderInterface $passwordEncoder)
	{
		$this->passwordEncoder = $passwordEncoder;
	}

    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user->setEmail('test@user.cz');
        $user->setRoles(['some_role', 'another_role']);
        $user->setPassword($this->passwordEncoder->encodePassword(
            $user,
            '1234'
        ));
        $manager->persist($user);

        $manager->flush();
    }
}

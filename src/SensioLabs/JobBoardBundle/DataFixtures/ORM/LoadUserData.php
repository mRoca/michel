<?php


namespace SensioLabs\JobBoardBundle\DataFixtures\ORM;


use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use SensioLabs\JobBoardBundle\Entity\User;

class LoadUserData extends AbstractFixture implements OrderedFixtureInterface
{
    const TEST_UUID_ADMIN = 'a8a1c2f5-995d-41a1-b0ae-fd9a1157bef7';
    const TEST_UUID_USER = 'a8a1c2f5-995d-41a1-b0ae-fd9a1157bef6';

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $admin = new User(self::TEST_UUID_ADMIN);
        $admin->setName('Admin Admin');
        $admin->setUsername('admin');
        $admin->setIsAdmin(true);
        $manager->persist($admin);

        $user = new User(self::TEST_UUID_USER);
        $user->setName('User User');
        $user->setUsername('user');
        $user->setIsAdmin(false);
        $manager->persist($user);

        $manager->flush();

        $this->addReference('admin', $user);
        $this->addReference('user', $user);
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 1;
    }
}

<?php

namespace App\Test;

use App\Entity\LuckyNumbers;
use App\Entity\User;
use \DateTime;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
   /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    protected function setUp()
    {
        parent::setUp();
        $this->client = static::createClient();

        $this->entityManager = $this->client->getContainer()->get('doctrine.orm.entity_manager');

        $this->entityManager->beginTransaction();
        $this->entityManager->getConnection()->setAutoCommit(false);
    }

    public function testUserDatabase()
    {
        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => 'test@test.test']);
        
        $this->assertNull($user);

        $user = New User;
        $user->setEmail('test@test.test');
        $user->setName('test');
        $user->setPassword('test_password');


        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => 'test@test.test']);

        $this->assertNotNull($user);

        $this->assertGreaterThan(0, $user->getId());
        $this->assertSame('test', $user->getName());
        $this->assertSame('test@test.test', $user->getEmail());
        $this->assertSame(['ROLE_USER'], $user->getRoles());
        $this->assertSame('test_password', $user->getPassword());
        
    }
    
    public function testLuckyNumberDatabase()
    {
        $time = new DateTime();
        
        $user = new User;
        $user->setEmail('test@test.test');
        $user->setName('test');
        $user->setPassword('test_password');
        
        $lu = New LuckyNumbers;
        $lu->setLuckyNumber(7);
        $lu->setCreateAt($time);
        $user->addLuckyNumber($lu);


        $this->entityManager->persist($user);
        $this->entityManager->persist($lu);
        $this->entityManager->flush();

        $lu = $this->entityManager
        ->getRepository(LuckyNumbers::class)
        ->findOneBy(['user' => $user]);
    
        $this->assertNotNull($lu, "s");

        $this->assertSame( 7, $lu->getLuckyNumber() , "a");
        $this->assertSame( $time, $lu->getCreateAt() , "b");
        $this->assertSame( $user, $lu->getUser(), "c" );}

    // test otwierania stron jako gosc
    /**
     * @dataProvider anonimusURL
     */
    public function testAnonimusAccess($url)
    {
        $crawler = $this->client->request('GET', $url);

        $this->assertTrue($this->client->getResponse()->isSuccessful());
   
    }

    public function anonimusURL()
    {
        return [
            ['/'],
            ['/login'],
            ['/register']
        ];
    }

    /**
     * @dataProvider anonimusDeniedURL
     */
    public function testUAnnonimusAccessDenied($url)
    {
        $crawler = $this->client->request('GET', $url);

        $this->assertTrue($this->client->getResponse()->isNotFound());
    }

    public function anonimusDeniedURL()
    {
        return [
            ['/home'],
            ['/home/history']
        ];
    }
}

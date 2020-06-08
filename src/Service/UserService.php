<?php

namespace App\Service;

use \Firebase\JWT\JWT;
use App\Entity\LuckyNumbers;
use App\Entity\User;
use App\Repository\UserRepository;
use DateTime;
use Swift_Mailer;
use Swift_Message;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserService extends AbstractController
{
    private $userRepository;
    private $mailer;

    function __construct(UserRepository $userRepository,  Swift_Mailer $mailer)
    {
        $this->userRepository = $userRepository;
        $this->mailer = $mailer;
    }

    public function addLuckyNumberToUser(User $user, LuckyNumbers $lu)
    {
        $enity = $this->getDoctrine()->getManager();

        $user->addLuckyNumber($lu);

        $enity->persist($user);
        $enity->persist($lu);
        $enity->flush();
        
    }

    public function createLuckyNumber(User $us)
    {
        $enity = $this->getDoctrine()->getManager();

        $lu = new LuckyNumbers;
        $lu->setLuckyNumber($this->luckyNumber());
        $lu->setCreateAt(new DateTime());
        $us->addLuckyNumber($lu);

        $enity->persist($lu);
        $enity->persist($us);
        $enity->flush();
        
        return $lu;
    }

    public function createUser( User $user )
    {
        if(($this->userRepository->findOneBy(['email' => $user->getEmail()])) == null){
            $enity = $this->getDoctrine()->getManager();
            
            $us = new User;
            $us->setRoles(['NOT_VERIFY']);
            $us->setEmail($user->getEmail());
            $us->setPassword($user->getPassword());
            $us->setName($user->getName());

            $enity->persist($us);
            $enity->flush();

            return true;
        }
        else{
            return false;
        }
    }

    public function getUserByEmail( String $email ){
        if( $us = $this->userRepository->findOneBy(['email' => $email]) ){
            return $us;
        }
        return null;
    }

    public function active($id){
        if(($us = $this->userRepository->findOneBy(['id' => $id])) != null)
        {
            if( $us->getRoles()[0] == "NOT_VERIFY" )
            {
                $enity = $this->getDoctrine()->getManager();

                $us->setRoles(['ROLE_USER']);

                $enity->persist($us);
                $enity->flush();

                return true;
            }
        }
        return false;
    }
    
    public function validMailSend( User $user){
        $token = $this->jwtCoder(['id' => $user->getId()]);
        $message = (new Swift_Message('Hello Email'))
        ->setFrom('noreply@example.com')
        ->setTo($user->getEmail())
        ->setBody(
            $this->renderView(
                'email/validation.html.twig',
                array('token' => $token,
                      'name' => $user->getName(),
                )
            ),
            'text/html'
        );

        $this->mailer->send($message);
    }

    public function jwtCoder($date)
    {
        return JWT::encode($date, $_SERVER['APP_SECRET'] ?? 'kromka');
    }

    public function jwtDecoder($date)
    {
        return (array) JWT::decode($date, $_SERVER['APP_SECRET'] ?? 'kromka', array('HS256'));
    }

    private function luckyNumber()
    {
        // Ambitny algorytm losujacy
        return rand(0,100);
    }
}

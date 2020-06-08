<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegisterFormType;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Security\Core\Security;

class DefaultController extends AbstractController
{
    /**
     * @var Security
     */
    private $security;
    private $authenticationUtils;
    private $passwordEncoder;
    private $userService;

    function __construct( 
                        AuthenticationUtils $authenticationUtils,
                        UserPasswordEncoderInterface $passwordEncoder, 
                        UserService $userService,
                        Security $security
                        )
    {
        $this->authenticationUtils = $authenticationUtils;
        $this->passwordEncoder = $passwordEncoder;
        $this->userService = $userService;
        $this->security = $security;
    }

    /**
     * @Route("/", name="index")
     */
    public function index(): ?Response
    {
        return $this->render('default/index.html.twig');
    }
    
    /**
     * @Route("/login", name="login")
     */
    public function login(): ?Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('home');
        }

        $error = $this->authenticationUtils->getLastAuthenticationError();
        $lastUsername = $this->authenticationUtils->getLastUsername();

        return $this->render('default/loginForm.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/register", name="register")
     */
    public function register(Request $request): ?Response
    {
        $us = new User();
        $form = $this->createForm(RegisterFormType::class, $us);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $us->setPassword($this->passwordEncoder->encodePassword($us, $form->get('password')->getData()));

            if ( $this->userService->createUser($us) )
            {
                $this->userService->validMailSend($this->userService->getUserByEmail($us->getEmail()));
                
                $this->addFlash('notice', 'Potwierdz maila.');
            }
            else 
                $this->addFlash('notice', 'Juz istnije takie konto.');
        }

        return $this->render('default/registerForm.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logout(): void
    {
        throw new \LogicException('This method can be blank.');
    }

    /**
     * @Route("/validate", name="validate")
     */
    public function validateToken(Request $request): ?Response
    {
        $tokenData = $this->userService->jwtDecoder( $request->query->get('token') );
    
        if( $this->userService->active($tokenData['id']) )
            return $this->render('inform/validCompleted.html.twig');
        else 
            return new Response("błąd tokena");
    }

    /**
     * @Route("/home", name="home")
     * @IsGranted("ROLE_USER", statusCode=404, message="Access denided")
     */
    public function home(): ?Response
    {
        $user = $this->userService->getUserByEmail( $this->security->getUser()->getUsername() );
        $newLuckyNumber = $this->userService->createLuckyNumber($user)->getLuckyNumber();
        
        return $this->render('user/home.html.twig', [
            'name' => $user->getName(),
            'luckyNumber' => $newLuckyNumber,
        ]);
    }

    /**
     * @Route("/home/history", name="homeHistory")
     * @IsGranted("ROLE_USER", statusCode=404, message="Access denided")
     */
    public function homeHistory(): ?Response
    {
        $user = $this->userService->getUserByEmail( $this->security->getUser()->getUsername() );
        
        return $this->render('user/homeHistory.html.twig', [
            'luckyNumbers' => $user->getLuckyNumber()->toArray(),
        ]);
    }
}

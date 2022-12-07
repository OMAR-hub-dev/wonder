<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/signup', name: 'signup')]
    public function signup(Request $request, EntityManagerInterface $em, UserPassWordHasherInterface $PassWordHasher): Response
    {
        $user= new User();
        $userFrom = $this->createForm(UserType::class, $user);
        $userFrom->handleRequest($request);
        if ($userFrom->isSubmitted() && $userFrom->isValid()){
            $user->setPassword($PassWordHasher->hashPassword($user, $user->getPassword()));
            $em->persist($user);
            $em->flush();
            
            $this->addFlash('success','Bienvenu sur Wonder !');

            return $this->redirectToRoute('login');

        }

        return $this->render('security/signup.html.twig', [
            'form' => $userFrom->createView(),
        ]);
    }

    
    #[Route('/login', name: 'login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if($this->getUser()){
            return $this->redirectToRoute('home');
        }
        // pour recuper la derniere erreur et la stocke en $error
        $error = $authenticationUtils->getLastAuthenticationError();
        $username = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'error' => $error,
            'username' => $username
        ]);
    }
    #[Route('/logout', name: 'logout')]
    public function logout()
    {
       
    }
}

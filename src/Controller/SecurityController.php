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
    public function login(): Response
    {
        return $this->render('security/index.html.twig', [
            'controller_name' => 'SecurityController',
        ]);
    }
    #[Route('/logout', name: 'logout')]
    public function logout()
    {
       
    }
}

<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
    #[Route('/user/{id}', name: 'user')]
    #[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
    public function userProfile(User $user): Response
    {
        $current_user = $this->getUser();
        if ($user === $current_user) {
            return $this->redirectToRoute('current_user'); 
        }
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }
    #[Route('/user', name: 'current_user')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function currentUserProfile(EntityManagerInterface $em,Request $request, UserPasswordHasherInterface $passwordHasher,): Response
    {
        $user = $this->getUser();
        $userForm = $this->createForm(UserType::class, $user);
        $userForm->remove('password');
        $userForm->add('newPassword',PasswordType::class,[
                                        'label' => 'Nouveau mot de passe',
                                        'required' => false
                                    ]);
        $userForm->handleRequest($request);
        if($userForm->isSubmitted() && $userForm->isValid()){
           $newPassword = $user->getNewPassword();
          
           if ($newPassword){
           
            $user->setPassword( $passwordHasher->hashPassword($user , $newPassword));
        
           }
        //    enregitrer la photo de profile en bdd
           $picture = $userForm->get('pictureFile')->getData();
           $folder = $this->getParameter('profile.folder');
           $ext = $picture->guessExtension();
           $filename = bin2hex(random_bytes(10)) . '.' . $ext;
           $picture->move($folder, $filename);
           $user->setPicture($this->getParameter('profile.folder.public_path') . '/' . $filename);
          
            $em->flush();
           $this->addFlash('success','Modification sauvegardées ! ');
        }

        return $this->render('user/index.html.twig', [
            'form' => $userForm->createView(),
        ]);
    }
   
}

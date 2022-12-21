<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Entity\ResetPassword;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SecurityController extends AbstractController
{
    #[Route('/signup', name: 'signup')]
    public function signup(Request $request, EntityManagerInterface $em, UserPassWordHasherInterface $PassWordHasher, MailerInterface $mailer): Response
    {
        $user= new User();
        $userFrom = $this->createForm(UserType::class, $user);
        $userFrom->handleRequest($request);
        if ($userFrom->isSubmitted() && $userFrom->isValid()){
            $user->setPassword($PassWordHasher->hashPassword($user, $user->getPassword()));
            $em->persist($user);
            $em->flush();
            // envoie de mail pour bienvenue
            $this->addFlash('success','Bienvenu sur Wonder !');
            $email = new TemplatedEmail();
            $email->to($user->getEmail());
            $email->subject('bienvenu sur Wonder');
            $email->htmlTemplate('@email_templates/welcome.html.twig');
            $email->context([
                'username' => $user->getfullName(),
            ]);
            $mailer->send($email);

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
    #[Route('/reset-password-request', name: 'reset-password-request')]
    public function resetPasswordRequest(Request $request,UserRepository $userRepository)
    {
       $emailForm = $this->createFormBuilder()->add('email',EmailType::class,[
        'constraints' =>[
            new NotBlank([
                'message' =>'Veuillez renseigner votre email'
            ])
        ]
       ])->getForm();
        
       $emailForm->handleRequest($request);
       if($emailForm->isSubmitted() && $emailForm->isValid()){
            $email= $emailForm->get('email')->getData();
            $user = $userRepository->findOneBy(['email'  => $email]);
            if($user){
               $resetPassword = new ResetPassword(); 
               $resetPassword->setUser($user);
               $resetPassword->setExpiredAt(new \DateTimeImmutable('+2 hours'));
               $token = substr(str_replace(['+','/','=','-'], '', base64_encode(random_bytes(30))),0 ,20);
               $resetPassword->setToken($token);
                dump($resetPassword);
            }
        $this->addFlash('success','Un email vous a été envoyé pour réinitialiser votre mot de passe ');

       }

        return $this->render('security/reset-password-request.html.twig', [
            'form' => $emailForm->createView()
        ]);
    }
}

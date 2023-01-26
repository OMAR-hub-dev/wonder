<?php

namespace App\Controller;

use DateTime;
use App\Entity\User;
use App\Form\UserType;
use App\Entity\ResetPassword;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ResetPasswordRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;

class SecurityController extends AbstractController
{
    #[Route('/signup', name: 'signup')]
    public function signup(Request $request, EntityManagerInterface $em, UserPassWordHasherInterface $PassWordHasher, MailerInterface $mailer): Response
    {
        $user= new User();
        $userFrom = $this->createForm(UserType::class, $user);
        $userFrom->handleRequest($request);
        if ($userFrom->isSubmitted() && $userFrom->isValid()){
            // enregistrement de la picture en bdd

            // permet de récupérer l'image envoyée par l'utilisateur depuis le formulaire. 
            $picture =  $userFrom->get('pictureFile')->getData();
            //  nous récupérons le paramètre de service que nous venons de définir dans la configuration. 
            $folder= $this->getParameter('profile.folder');
            $ext= $picture->guessExtension() ?? 'bin';
            // nous créons un nom de fichier aléatoire qui fait 10 octets que nous convertissons en hexadécimal. En hexadécimal (Base16), chaque caractère correspond à 4 bits donc 2 caractères correspondent à 1 octet. Le nom des fichiers sera donc de 20 caractères puis un point puis l'extension. 
            $fileName = bin2hex(random_bytes(10)). '.' .$ext;
            //  permet de déplacer l'image obtenue avec le formulaire depuis l'espace temporaire vers le dossier que nous avons défini et avec notre nom aléatoire. 
            $picture->move($folder,$fileName);
            // permet simplement d'enregistrer dans le champ picture l'URL vers l'image. Elle correspond au dossier défini dans le paramètre profile.folder.public_path (profiles) puis un / puis le nom aléatoire que nous avons défini. 
            $user->setPicture($this->getParameter('profile.folder.public_path'). '/' . $fileName);

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

    #[Route('/reset-password/{token}', name: 'reset-password')]
    public function resetPassword(string $token, ResetPasswordRepository $resetPasswordRepository, EntityManagerInterface $em, Request $request, UserPasswordHasherInterface $userPasswordHasher,RateLimiterFactory $passwordRecoveryLimiter) 
    {
        // pour bloquer les nombreuse tentative de reinintialisation de mot de passe 
        //le nom de la variable doit correspondre au nom qui est configuré dans rate_limiter.yaml + Limiter
        $limiter = $passwordRecoveryLimiter->create($request->getClientIp());
        if(false === $limiter->consume(1)->isAccepted()){
            $this->addFlash('error', 'Vous devez attendre 1 heure pour refaire une tentative');
            return $this->redirectToRoute('login');
        }

        $resetPassword = $resetPasswordRepository->findOneBy(['token' => $token]);
        if (!$resetPassword || $resetPassword->getExpiredAt() < new DateTime('now')){
            if($resetPassword){
                $em->remove($resetPassword);
                $em->flush();
            }
            $this->addFlash('error', 'votre demande est expirée veuillez refaire une demande.');
            return $this->redirectToRoute('login');
        }

        $resetPasswordFrom = $this->createFormBuilder()
                                    ->add('password', PasswordType::class,[
                                        'constraints'=>[
                                            new Length([
                                                'min'=>6,
                                                'minMessage'=>'Le mot de passe doit faire au moins 6 caractères.'
                                            ]),
                                            new NotBlank([
                                                'message'=>'Veuillez renseigner un mot de passe.'
                                            ])
                                        ]
                                    ])
                                    ->getForm();
        $resetPasswordFrom->handleRequest($request);
        if($resetPasswordFrom->isSubmitted() && $resetPasswordFrom->isValid()){
            $password = $resetPasswordFrom->get('password')->getData();
            $user = $resetPassword ->getUser();
            $hach = $userPasswordHasher->hashPassword($user,$password);
            $user->setPassword($hach);
            $em->remove($resetPassword);
            $em->flush();
            $this->addFlash('success', 'Votre mot de passe a été modifié ');
            return $this->redirectToRoute('login');
        }
     
        return $this->render('security/reset_password_form.html.twig',[
            'form' => $resetPasswordFrom->createView(),
        ]);
    }


    #[Route('/reset-password-request', name: 'reset-password-request')]
    public function resetPasswordRequest(Request $request,UserRepository $userRepository, ResetPasswordRepository $resetPasswordRepository, EntityManagerInterface $em, MailerInterface $mailer,RateLimiterFactory $passwordRecoveryLimiter)
    {
        // pour bloquer les nombreuse tentative de reinintialisation de mot de passe 
        //le nom de la variable doit correspondre au nom qui est configuré dans rate_limiter.yaml + Limiter
        $limiter = $passwordRecoveryLimiter->create($request->getClientIp());
        if(false === $limiter->consume(1)->isAccepted()){
            $this->addFlash('error', 'Vous devez attendre 1 heure pour refaire une tentative');
            return $this->redirectToRoute('login');
        }

       $emailForm = $this->createFormBuilder()->add('email',EmailType::class,[
        'constraints' =>[
            new NotBlank([
                'message' =>'Veuillez renseigner votre email'
            ])
        ]
       ])->getForm();
        
       $emailForm->handleRequest($request);
       if($emailForm->isSubmitted() && $emailForm->isValid()){
            $emailValue= $emailForm->get('email')->getData();
            $user = $userRepository->findOneBy(['email'  => $emailValue]);
            if($user){

               $oldResetPassword = $resetPasswordRepository->findOneBy(['user' => $user]);

               if($oldResetPassword){
                $em->remove($oldResetPassword);
                $em->flush();
               }
               $resetPassword = new ResetPassword(); 
               $resetPassword->setUser($user);
               $resetPassword->setExpiredAt(new \DateTimeImmutable('+2 hours'));
               $token = substr(str_replace(['+','/','=','-'], '', base64_encode(random_bytes(30))),0 ,20);
               $resetPassword->setToken(sha1($token));
               $em->persist($resetPassword);
               $em->flush();
               $email = new TemplatedEmail();
                $email->to($emailValue)
                ->subject('Demande de réinitialisation de mot de passe')
                ->htmlTemplate('@email_templates/reset_password.html.twig')
                ->context([
                    'token' => $token
                ]);
                $mailer->send($email);
               
                
            }
        $this->addFlash('success','Un email vous a été envoyé pour réinitialiser votre mot de passe ');
        return $this->redirectToRoute('home');

       }

        return $this->render('security/reset-password-request.html.twig', [
            'form' => $emailForm->createView()
        ]);
    }
}

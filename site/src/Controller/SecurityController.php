<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Form\RegistrationType;
use App\Form\ChangeSettings;
use Symfony\Component\HttpFoundation\Request;  //ajout du request
use Doctrine\Persistence\ObjectManager; //ajout du manager
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface; // ajout de l'encoder
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Doctrine\ORM\EntityManagerInterface;
use Monolog\Logger;
use App\Form\ResetPasswordType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use App\Repository\UserRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Form\NewPasswordType;

class SecurityController extends AbstractController
{
    /**
     * @Route("/inscription", name="security_inscription")
     */
    public function registration(Request $request, ObjectManager $manager,UserPasswordEncoderInterface $encoder)
    {
        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);
        
        $form->handleRequest($request); //analyse la request
        
        if($form->isSubmitted() && $form->isValid()) //si le form est envoyé:
        {
            
            $password = $encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($password);
            
            $manager->persist($user); //persiste l’info dans le temps
            $manager->flush(); //envoie les info à la BDD
            
            return $this->redirectToRoute('login');
        }
        
        return $this->render('security/index.html.twig', [ 'form' => $form->createView() ]);
        
    }
    /**
     * @Route("/login", name="login")
     */
    public function login()
    {
        
        return $this->render('security/login.html.twig');
    }
    
    /**
     * @Route("/logout", name="logout")
     */
    public function logout()
    {
        $this->logger = $this->getContainer()->get('monolog.logger');
        $this->logger->info('test');
    } 
    
    /**
     * @Route("/account", name="settings")
     * @Security("is_granted('ROLE_USER')")
     */
    public function account(Request $request, ObjectManager $manager, UserPasswordEncoderInterface $encoder)
    {
          $user = $this->getUser();
          $form = $this->createForm(ChangeSettings::class, $user);
        
        $form->handleRequest($request); //analyse la request
        $formAccount= $request->request->get('change_settings');
        
        if ($form->isSubmitted() && $form->isValid()) //si le form est envoyé:
        {
              $user->setUsername($formAccount['username']);
              $user->setEmail($formAccount['email']);
              $manager->persist($user); //persiste l’info dans le temps
              $manager->flush(); //envoie les info à la BDD
        }
        return $this->render('site/account.html.twig', [ 'form' => $form->createView() ]);
         
    }
    /**
     * @Route("/changeStatus", name="changeStatus")
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_VIEWER')")
     */
    public function changeStatus( EntityManagerInterface $manager)
    {
        $user=$this->getUser();
        $role = $user->getRoles();
        if($role[0]=='ROLE_VIEWER'){
            $user->setRoles( array('ROLE_ADMIN') );
        }   
        else{
            $user->setRoles( array('ROLE_VIEWER') );
        }
        // générer un nouveau token
        $token = new \Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken(
            $user,
            null,
            'main',
            $user->getRoles()
            );
        $this->container->get('security.context')->setToken($token);

        // faire un refresh du user a l'aide du user manager
        $userManager = $this->container->get('fos_user.user_manager');
        $userManager->refreshUser($user);
        return $this->redirectToRoute('home');
    }
    /**
     * @Route("/resetPassword", name="resetPassword")
     */
    public function resetPassword(Request $request, \Swift_Mailer $mailer,  UserRepository $userRepo,EntityManagerInterface $manager){
               
        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) //si le form est envoyé:
        {
            $mailToReset = $form["email"]->getData();
            //echo $mailToReset;
            $user = $userRepo->findOneByEmail($mailToReset);
            $token = md5(uniqid());
            $url = $this->generateUrl('app_reset_password',['token'=> $token],UrlGeneratorInterface::ABSOLUTE_URL);
            
            if(!$user){
                return $this->redirectToRoute('login');
                
            }
            
            try{
                $user->setResetToken($token);
                $manager->persist($user); //persiste l’info dans le temps
                $manager->flush(); //envoie les info à la BDD
            }catch(\Exception $e){
                $this->addFlash('warning', 'Une erreur est survenue: '.$e->getMessage());
                return $this->redirectToRoute('resetPassword');
            }
            
            $message = (new \Swift_Message('Reset password')) 
            
                ->setFrom('no-reply@noreply.com')
                ->setTo($mailToReset)
                ->setBody(
                    $this->renderView(
                        'emails/emails_reset.html.twig',
                        ['email'=>$user->getEmail(),'path'=>$url]
                        ),
                        'text/html'
                    )
            ;
            
            $mailer->send($message);
            
            return $this->redirectToRoute('login');
        }
        
        return $this->render('security/resetPassword.html.twig', [ 'ResetPasswordForm' => $form->createView() ]);
    }
    
    
    /**
     * @Route("/resetPassword/{token}", name="app_reset_password")
     */
    public function Reset_Password($token,Request $request,UserPasswordEncoderInterface $encoder,UserRepository $userRepo,EntityManagerInterface $manager){
        
        $user = $userRepo->findOneBy(['reset_token'=>$token]);
        $form = $this->createForm(NewPasswordType::class);
        $form->handleRequest($request);
        
        if(!$user){
            //wrong token
            return $this->redirectToRoute('login');
        }
        if ($form->isSubmitted() && $form->isValid()){
            $user->setResetToken(null);
            
            $password = $encoder->encodePassword($user, $user->getPassword()); //encode password
            $user->setPassword($password);
            
            $manager->persist($user); //persiste l’info dans le temps
            $manager->flush(); //envoie les info à la BDD
            return $this->redirectToRoute('login');
        }else{
            return $this->render('security/set_new_password.html.twig', [ 'ResetForm' => $form->createView() ]);
        }
    }
}

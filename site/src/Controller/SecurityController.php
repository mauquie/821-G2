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
        
    } 
    
    /**
     * @Route("/account", name="settings")
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
            $manager->persist($user);
            $manager->flush(); //envoie les info à la BDD            
        }   
        else{
            $user->setRoles( array('ROLE_VIEWER') );
            $manager->flush(); //envoie les info à la BDD   
        }
        return $this->redirectToRoute('home');
    }
}

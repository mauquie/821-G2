<?php
namespace App\Controller;

use App\Entity\User;
use App\Form\EditUserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\Persistence\ObjectManager; //ajout du manager
use App\Form\CreationUser;
use App\Form\ChangeSettings;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface; // ajout de l'encoder

/**
 * @Route("/admin", name="admin_")
 */
class AdminController extends AbstractController
{
    /**
     * @Route("/", name="adminHome")
     */
    public function index()
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }
    /**
     * @Route("/users", name="interface_users")
     */
    public function usersList(UserRepository $user)
    {
        $admin=0;
        return $this->render('admin/user.html.twig', [
            'users' => $user->findAll(),
            'admin' => $admin, // test if it's admin
        ]);
    }
    
    /**
     * @Route("/users/modifcation/{id}", name="set_users")
     */
    public function editUser(Request $request, User $user, EntityManagerInterface $manager)
    {
        
        $form = $this->createForm(EditUserType::class, $user);
        
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $manager->flush();
            
            return $this->redirectToRoute('admin_interface_users');
        }
        
        return $this->render('admin/editUser.html.twig', [ 'formUser' => $form->createView() ]);
    } 

    /**
     * @Route("/users/userCreation", name="creation_user")
     */
    public function createUser(Request $request, ObjectManager $manager, UserPasswordEncoderInterface $encoder)
    {
        $user = new User();
        $mdp = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCEFGHIJKLMNOPQRSTUVWXYZ0123456789'),1, 8);
        $form = $this->createForm(CreationUser::class, $user);
        
        $form->handleRequest($request); //analyse la request
        
        $user->setPassword($mdp);
        
        if ($form->isSubmitted() )  //si le form est envoyé:
            {
                
            $password = $encoder->encodePassword($user, $mdp);
            $user->setPassword($password);
             
            $manager->persist($user); //persiste l’info dans le temps
            $manager->flush(); //envoie les info à la BDD
            
            return $this->redirectToRoute('admin_interface_users');
            }
            
        return $this->render('admin/creationUser.html.twig', [ 'form' => $form->createView() ]);
    }
}

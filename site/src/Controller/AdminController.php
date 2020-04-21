<?php
namespace App\Controller;

use App\Entity\User;
use App\Form\EditUserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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
        return $this->render('admin/user.html.twig', [
            'users' => $user->findAll(),
        ]);
    }
    
    /**
     * @Route("/utilisateurs/modifier/{id}", name="set_users")
     */
    public function editUser(Request $request, User $user, EntityManagerInterface $entity_manager)
    {
        
        $form = $this->createForm(EditUserType::class, $user);
        
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $entity_manager->flush();
            
            return $this->redirectToRoute('admin_interface_users');
        }
        
        return $this->render('admin/editUser.html.twig', [ 'formUser' => $form->createView() ]);
    } 
}

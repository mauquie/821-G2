<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class SiteController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index()
    {
        $user = $this->getUser();
        if($user){   // if connected         
            if($user->getActivationToken() != null){
                return $this->redirectToRoute('logout');
            }
        }
        return $this->render('site/index.html.twig', [
            'controller_name' => 'SiteController',
        ]);
    }
    /**
     * @Route("/agenda", name="agenda")
     */
    public function agenda()
    {
        return $this->render('site/agenda.html.twig', [
            'controller_name' => 'SiteController',
        ]);
    }
    /**
     * @Route("/ajax", name="ajax")
     */
    public function ajaxAction(Request $request) {
        
        if ($request->isXmlHttpRequest()) {
            // Ajax request
            $jsonData = ['test 200'];
            return new JsonResponse($jsonData);            
            
        } else {
            // Normal request
            return $this->render('site/ajax.html.twig');
        }
    }
    
}

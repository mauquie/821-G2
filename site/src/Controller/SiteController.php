<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class SiteController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index()
    {
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
     * @Route("/promo", name="promo")
     */
    public function promo()
    {
        return $this->render('site/promo.html.twig', [
            'controller_name' => 'SiteController',
        ]);
    }
    /**
     * @Route("/account", name="promo")
     */
    public function account()
    {
        return $this->render('site/account.html.twig', [
            'controller_name' => 'SiteController',
        ]);
    }
}

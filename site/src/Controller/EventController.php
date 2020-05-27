<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\FileType; 
use Symfony\Component\Filesystem\Filesystem;
use \DateTime;

/**
 * @Route("/event")
 * @Security("is_granted('ROLE_USER')")
 */
class EventController extends AbstractController
{
    /**
     * @Route("/", name="event_index", methods={"GET"})
     */
    public function index(eventRepository $eventRepository): Response
    {
        return $this->render('event/index.html.twig', [
            'events' => $eventRepository->findAll(),
        ]);
    }
    
    /**
     * @Route("/calendar", name="event_calendar", methods={"GET"})
     */
    public function calendar(): Response
    {
        return $this->render('event/calendar.html.twig');
    }

    /**
     * @Route("/new", name="event_new", methods={"GET","POST"})
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_EDITOR')")
     */
    public function new(Request $request): Response
    {
        $event = new Event();
        $date = new DateTime();  
        //echo $date->format("Y-m-d H:i:s");
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);
        $user = $this->getUser();
        if ($form->isSubmitted() && $form->isValid()) {
            
            $event->setCreator($user->getUsername());
            $event->setDateCreation($date->format("Y-m-d H:i:s"));
            $event->setLastModification($date->format("Y-m-d H:i:s"));
            $event->setEditors(array ($user->getUsername()));
            $file = $event->getPhoto();
            
            if ($file!=null){
                $fileName = md5(uniqid()).'.'.$file->guessExtension();
                $file->move($this->getParameter('upload_directory'), $fileName);
                $event->setPhoto($fileName); 
            }
            else{
                $event->setPhoto("NULL");
            }
            
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($event);
            $entityManager->flush();

            return $this->redirectToRoute('event_index');
        }

        return $this->render('event/new.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="event_show", methods={"GET"})
     */
    public function show(Event $event): Response
    {
        return $this->render('event/show.html.twig', [
            'event' => $event,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="event_edit", methods={"GET","POST"})
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_EDITOR')")
     */
    public function edit(Request $request, Event $event): Response
    {
        $fileName = $event->getPhoto();
        
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);
        $date = new DateTime(); 
        $user = $this->getUser();

        if ($form->isSubmitted() && $form->isValid()) {
            $event->setLastModification($date->format("Y-m-d H:i:s"));
            $event->setEditors(array ($user->getUsername()));
            
            $StrArray = ['../public/uploads/',$fileName];  //path of image
            $pathStr = join("",$StrArray);
            
            if($fileName != "NULL" ){
                $filesystem = new Filesystem();
                $result = unlink($pathStr);
            }
            
            $file = $event->getPhoto();
            if ($file!=null){
                $fileName = md5(uniqid()).'.'.$file->guessExtension();
                $file->move($this->getParameter('upload_directory'), $fileName);
                $event->setPhoto($fileName);
            }
            else{
                $event->setPhoto("NULL");
            }
            
            
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('event_index');
        }

        return $this->render('event/edit.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="event_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Event $event): Response
    {
        if ($this->isCsrfTokenValid('delete'.$event->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            
            
            $fileName = $event->getPhoto();
            $StrArray = ['../public/uploads/',$fileName];  //path of image 
            $pathStr = join("",$StrArray);
            
            if($fileName != "NULL" ){
                $filesystem = new Filesystem();
                $result = unlink($pathStr);
            }
            $entityManager->remove($event);
            $entityManager->flush();
        }

        return $this->redirectToRoute('event_index');
    }
    
    
}

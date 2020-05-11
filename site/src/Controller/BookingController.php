<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Form\BookingType;
use App\Repository\BookingRepository;
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
 * @Route("/booking")
 */
class BookingController extends AbstractController
{
    /**
     * @Route("/", name="booking_index", methods={"GET"})
     */
    public function index(BookingRepository $bookingRepository): Response
    {
        return $this->render('booking/index.html.twig', [
            'bookings' => $bookingRepository->findAll(),
        ]);
    }
    
    /**
     * @Route("/calendar", name="booking_calendar", methods={"GET"})
     */
    public function calendar(): Response
    {
        return $this->render('booking/calendar.html.twig');
    }

    /**
     * @Route("/new", name="booking_new", methods={"GET","POST"})
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_EDITOR')")
     */
    public function new(Request $request): Response
    {
        $booking = new Booking();
        $date = new DateTime();  
        //echo $date->format("Y-m-d H:i:s");
        $form = $this->createForm(BookingType::class, $booking);
        $form->handleRequest($request);
        $user = $this->getUser();
        if ($form->isSubmitted() && $form->isValid()) {
            
            $booking->setCreator($user->getUsername());
            $booking->setDateCreation($date->format("Y-m-d H:i:s"));
            $booking->setLastModification($date->format("Y-m-d H:i:s"));
            $booking->setEditors(array ($user->getUsername()));
            $file = $booking->getPhoto();
            
            if ($file!=null){
                $fileName = md5(uniqid()).'.'.$file->guessExtension();
                $file->move($this->getParameter('upload_directory'), $fileName);
                $booking->setPhoto($fileName); 
            }
            else{
                $booking->setPhoto("NULL");
            }
            
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($booking);
            $entityManager->flush();

            return $this->redirectToRoute('booking_index');
        }

        return $this->render('booking/new.html.twig', [
            'booking' => $booking,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="booking_show", methods={"GET"})
     */
    public function show(Booking $booking): Response
    {
        return $this->render('booking/show.html.twig', [
            'booking' => $booking,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="booking_edit", methods={"GET","POST"})
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_EDITOR')")
     */
    public function edit(Request $request, Booking $booking): Response
    {
        $fileName = $booking->getPhoto();
        
        $form = $this->createForm(BookingType::class, $booking);
        $form->handleRequest($request);
        $date = new DateTime(); 
        $user = $this->getUser();

        if ($form->isSubmitted() && $form->isValid()) {
            $booking->setLastModification($date->format("Y-m-d H:i:s"));
            $booking->setEditors(array ($user->getUsername()));
            
            $StrArray = ['../public/uploads/',$fileName];  //path of image
            $pathStr = join("",$StrArray);
            
            if($fileName != "NULL" ){
                $filesystem = new Filesystem();
                $result = unlink($pathStr);
            }
            
            $file = $booking->getPhoto();
            if ($file!=null){
                $fileName = md5(uniqid()).'.'.$file->guessExtension();
                $file->move($this->getParameter('upload_directory'), $fileName);
                $booking->setPhoto($fileName);
            }
            else{
                $booking->setPhoto("NULL");
            }
            
            
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('booking_index');
        }

        return $this->render('booking/edit.html.twig', [
            'booking' => $booking,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="booking_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Booking $booking): Response
    {
        if ($this->isCsrfTokenValid('delete'.$booking->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            
            
            $fileName = $booking->getPhoto();
            $StrArray = ['../public/uploads/',$fileName];  //path of image 
            $pathStr = join("",$StrArray);
            
            if($fileName != "NULL" ){
                $filesystem = new Filesystem();
                $result = unlink($pathStr);
            }
            $entityManager->remove($booking);
            $entityManager->flush();
        }

        return $this->redirectToRoute('booking_index');
    }
    
    
}

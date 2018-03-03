<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Contact;
use AppBundle\Entity\Reservation;
use AppBundle\Form\ContactType;
use AppBundle\Form\ReservationType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Devis;
use AppBundle\Form\DevisType;

class DefaultController extends Controller
{


    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {

        $devis = new Devis();
        $contact = new Contact();
        $form =  $this->get('form.factory')->create( DevisType::class  ,$devis);
        $form_contact =  $this->get('form.factory')->create( ContactType::class  ,$contact);

/*        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            return $this->redirect($this->generateUrl ('reservation' ,  array('devis' => serialize($devis)  )));
        }*/

        $form_contact->handleRequest($request);
        $message_page = [];
        if ($form_contact->isSubmitted() && $form_contact->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($contact);
            $em->flush();
            $message_page["contact_success"] = "Votre message à bien été envoyé";
        }

        return $this->render('AppBundle:Default:index.html.twig' , array(  "message_page" => $message_page ,'form_contact' => $form_contact->createView(), 'form_devis' => $form->createView()));
    }

    /**
     * @Route("/moncompte", name="moncompte")
     */
    public function monCompteAction(Request $request)
    {
        return $this->render('AppBundle:Default:moncompte/index.html.twig' );
    }

    /**
     * @Route("/moncompte_register", name="moncompte_register")
     */
    public function monCompteRegisterAction(Request $request)
    {
        return $this->render('AppBundle:Default:moncompte/register.html.twig' );
    }

    /**
     * @Route("/moncompte_reset", name="moncompte_reset")
     */
    public function monCompteResetAction(Request $request)
    {
        return $this->render('AppBundle:Default:moncompte/reset.html.twig' );
    }

    /**
     * @Route("/moncompte_profile", name="moncompte_profile")
     */
    public function monCompteProfileAction(Request $request)
    {
        return $this->render('AppBundle:Default:moncompte/profile.html.twig' );
    }



    /**
     * @Route("/test", name="test")
     */
    public function testAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('AppBundle:Default:test.html.twig');
    }

    /**
     * @Route("/contact", name="contact")
     */
    public function  contactAction(Request $request)
    {
        $contact = new Contact();
        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($contact);
            $em->flush();
            return $this->render('AppBundle:Default:contact.html.twig' , array( "form" => $form->createView()  , "step" => "Success"));
        }

        // replace this example code with whatever you need
        return $this->render('AppBundle:Default:contact.html.twig' , array(  "form" => $form->createView()  ,  "step" => "Initial"));
    }
}

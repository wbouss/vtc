<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Contact;
use AppBundle\Entity\Reservation;
use AppBundle\Form\ContactType;
use AppBundle\Form\ReservationType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Service\ReservationService;
use AppBundle\Entity\Devis;
use AppBundle\Form\DevisType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\DateTime;

class ReservationController extends Controller
{


    public static $MailResp = "wbouss@gmail.com";
    public static $random = "Rosalba";


    /**
     * @Route("/errorsFormReservation"  , name="errorsFormReservation" ,    options={"expose"=true})
     */
    public function ErrorsFormReservationAction( Request $request){
        $depart = $request->query->get("depart");
        $arrive = $request->query->get("arrive");
        $date  = $request->query->get("date");
        $personne = $request->query->get("personne");
        $bagage = $request->query->get("bagage");

        $devis = new Devis();
        $devis->setDepart($depart);
        $devis->setArrive($arrive);
        $devis->setDatePrevu($date);
        $devis->setNbBagage($personne);
        $devis->setNbPersonne($bagage);

        $infoDrive =  $this->get("app.reservation")->getInfoGoogleApi($depart, $arrive );
        $erreurs  = $this->get("app.reservation")->getErrors($infoDrive , $devis , $this->getDoctrine()->getManager() );

        if( empty($erreurs))
            return new Response("OK");
        else
            return new Response(\GuzzleHttp\json_encode($erreurs));
    }


    /**
     * @Route("/reservation"  , name="reservation")
     */
    public function ReservationAction( Request $request )
    {
        $devis_post = $request->request->get("appbundle_devis");

        if( empty($devis_post)){
            $devis = new Devis();
            $infoDrive = [];
        }
        else{
            $devis = new Devis();
            $devis->setDepart($devis_post["depart"]);
            $devis->setArrive($devis_post["arrive"]);
            $devis->setDatePrevu($devis_post["datePrevu"]);
            $devis->setNbBagage($devis_post["nbBagage"]);
            $devis->setNbPersonne($devis_post["nbPersonne"]);

            $infoDrive =  $this->get("app.reservation")->getInfoGoogleApi($devis->getDepart(), $devis->getArrive());
            $form =  $this->get('form.factory')->create( DevisType::class  ,$devis);

            $erreurs  = $this->get("app.reservation")->getErrors($infoDrive , $devis , $this->getDoctrine()->getManager());
            if(!empty($erreurs))
                return $this->render('AppBundle:Reservation:index.html.twig' , array(  "devis" => $devis,"erreurs" => $erreurs  , "infodrive" => $infoDrive ,  'form_devis' => $form->createView()));
        }



        $form =  $this->get('form.factory')->create( DevisType::class  ,$devis);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            return $this->forward('AppBundle\Controller\ReservationController::EstimationAction' ,  array('devis' =>  $devis , 'infoDrive' => $infoDrive ));
        }


        return $this->render('AppBundle:Reservation:index.html.twig' , array(  "erreurs" => [] ,  "infodrive" => $infoDrive ,  'form_devis' => $form->createView()));

    }




    /**
     * @Route("/estimation", name="estimation")
     */
    public function EstimationAction(Request $request)
    {
        $devis = $request->get('devis');
        $infoDrive = $request->get('infoDrive');
        $em = $this->getDoctrine()->getManager();
        $p = $devis->getDatePrevu();

        $devis->setDate(new \DateTime());
        $devisTime = $devis->getDatePrevu();
        $devisTime = date_create_from_format("d/m/Y H:i" , $devisTime);

        $devis->setDatePrevu($devisTime);
        $devis->setPrix(number_format((float)$infoDrive[2], 2, '.', ''));

        $devis->setDuree($infoDrive[0][0]);
        $devis->setDistance($infoDrive[1][1]);

        $em->persist($devis);
        $em->flush();
        $devisKey = $this->get("app.reservation")->encrypt( $devis->getId() , ReservationController::$random  );
        return $this->render('AppBundle:Reservation:estimation.html.twig' , array( "devisKey" => $devisKey ,  "devis" => $devis , "infoDrive" => $infoDrive ));

    }



    /**
     * @Route("/reserver", name="reserver")
     */
    public function ReserverAction(Request $request)
    {
        $devisKey = $this->get("app.reservation")->decrypt($request->get('devisKey') , ReservationController::$random ) ;
        $infoDrive = $request->get('infoDrive');
        $em = $this->getDoctrine()->getManager();

        $repo = $em->getRepository("AppBundle:Devis");
        $devis = $repo->find($devisKey);

        // See your keys here: https://dashboard.stripe.com/account/apikeys
        \Stripe\Stripe::setApiKey("sk_test_7Pj74JxtAJF4cQfSSeostGWA");

        // Token is created using Stripe.js or Checkout!
        // Get the payment token submitted by the form:
        $token = $request->get('stripeToken');


        if (empty($token))
        {
            return $this->render('BurgerBundle:Default:index.html.twig');
        }


        // Charge the user's card:
        $charge = \Stripe\Charge::create(array(
            "amount" => $devis->getPrix() * 100,
            "currency" => "eur",
            "description" => "Commande",
            "source" => $token,
        ));

        $reservation = new Reservation();
        $reservation->setNbBagage($devis->getNbBagage());
        $reservation->setNbPersonne($devis->getNbPersonne());
        $reservation->setDatePrevu($devis->getDatePrevu());
        $reservation->setDepart($devis->getDepart());
        $reservation->setArrive($devis->getArrive());
        $reservation->setDuree($devis->getDuree() / 60 );
        $reservation->setDistance($devis->getDistance() /1000 );
        $reservation->setDate(new \DateTime());
        $reservation->setPrix($devis->getPrix());
        $reservation->setUser($this->getUser());
        $em->persist($reservation);
        $em->flush();

        $this->saveInvoicePdf($reservation);
        $this->saveBookingPdf($reservation);

        return $this->render('AppBundle:Reservation:reserver.html.twig' , array( "reservation" => $reservation , "infoDrive" => $infoDrive ));

    }


    public function saveInvoicePdf($reservation)
    {
        $pdf = $this->get("white_october.tcpdf")->create();
        $html = $this->renderView("AppBundle:Reservation:invoice.html.twig" ,  array("reservation" => $reservation ));
        $pdf->AddPage();
        $pdf->writeHTML($html, true, false, true, false, '');
        $path = __DIR__."/../../../web/bundles/app/factures/facture-".$reservation->getId().".pdf";
        $pdf->Output( $path, 'F');
        return $path;
    }

    public function saveBookingPdf($reservation)
    {
        $pdf = $this->get("white_october.tcpdf")->create();
        $html = $this->renderView("AppBundle:Reservation:booking.html.twig" ,  array("reservation" => $reservation ));
        $pdf->AddPage();
        $pdf->writeHTML($html, true, false, true, false, '');
        $path = __DIR__."/../../../web/bundles/app/bonReservation/bon-".$reservation->getId().".pdf";
        $pdf->Output( $path, 'F');
        return $path;
    }



    /**
     * @Route("/invoice", name="invoice")
     */
    public function invoiceAction(Request $request)
    {

        $reservation = new Reservation();
        $reservation->setNbBagage(1);
        $reservation->setNbPersonne(2);
        $reservation->setDatePrevu( new \DateTime());
        $reservation->setDepart("12 ter rue du capitaine bossut");
        $reservation->setArrive ("pantin");
        $reservation->setDuree(15000);
        $reservation->setDistance(1000);
        $reservation->setDate(new \DateTime());
        $reservation->setPrix(50);
        $reservation->setUser($this->getUser());

        $path_invoice = $this->saveInvoicePdf($reservation);
        $path_booking = $this->saveBookingPdf($reservation);
        return new Response("ok");

    }


    /**
     * @Route("/send", name="send")
     */
    public function sendAction(Request $request)
    {

        $reservation = new Reservation();
        $reservation->setNbBagage(1);
        $reservation->setNbPersonne(2);
        $reservation->setDatePrevu( new \DateTime());
        $reservation->setDepart("12 ter rue du capitaine bossut");
        $reservation->setArrive ("pantin");
        $reservation->setDuree(15000);
        $reservation->setDistance(1000);
        $reservation->setDate(new \DateTime());
        $reservation->setPrix(50);
        $reservation->setUser($this->getUser());
        return new Response("ok");

    }

    public function SendMail($reservation , $pj ){
        /*
        *  mail au fournisseur
        */
        $titre = "Nouvelle reservation";
        $from = "noreply-vtc@vtc.fr";


        $body = $this->get("templating")->render('AppBundle:Mail:newOrderCustomer.html.twig', array('reservation' => $reservation));

        $message = \Swift_Message::newInstance()
            ->setSubject($titre)
            ->setFrom($from)
            ->setTo(ReservationController::$MailResp)
            ->setBody($body)
            ->attach(Swift_Attachment::fromPath($pj))
            ->setContentType('text/html');
        $this->get('mailer')->send($message);
    }

}

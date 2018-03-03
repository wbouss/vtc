<?php

namespace AppBundle\Service;

use Symfony\Component\Security\Http;
use GuzzleHttp\Client;


class ReservationService
{
    private  $key = "AIzaSyCrEfQ8dj-0qJ9jAruwxWV06qKR8dfupXk";

    public function getInfoGoogleApi($origin , $destinations ){
        if( !ReservationService::isValidAdress($origin , $destinations ))
            return "KO";

        $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=".$origin."&destinations=".$destinations."&key=".$this->key;
        //$d = $this->get('app.reservation')->Distance();
        $client = new \GuzzleHttp\Client();
        $res = $client->request('GET', $url);
        $contents = $res->getBody()->getContents();
        $contents = \GuzzleHttp\json_decode($contents);

        if( !ReservationService::isValidRoute($contents))
            return "KO";

        $distance = $contents->rows[0]->elements[0]->distance;
        $duree = $contents->rows[0]->elements[0]->duration->value;
        $dureeArray = [ $duree , $duree / 60 , $duree / 3600  ];
        $distanceArray = [ $distance->text , $distance->value ];

        return [ $dureeArray , $distanceArray   ,$this->getPrice($duree , $distance->value )];

    }

    public function isValidAdress($origin , $destination ){
        $client = new \GuzzleHttp\Client();
        $urlOrigin = "https://maps.googleapis.com/maps/api/geocode/json?address=".$origin."&key=".$this->key;

        $res_origin = $client->request('GET', $urlOrigin);

        $contents_origin = $res_origin->getBody()->getContents();
        $contents_origin = \GuzzleHttp\json_decode($contents_origin);

        if( !$contents_origin->status == "OK")
            return false;

        $region_ok_origin = false;
        foreach ( $contents_origin->results[0]->address_components as $component ){
            if( $component->long_name == "Île-de-France" )
                $region_ok_origin = true;
        }



        $urlDestination = "https://maps.googleapis.com/maps/api/geocode/json?address=".$destination."&key=".$this->key;

        $res_destination = $client->request('GET', $urlDestination);

        $contents_destination = $res_destination->getBody()->getContents();
        $contents_destination = \GuzzleHttp\json_decode($contents_destination);

        if( !$contents_destination->status == "OK")
            return false;

        $region_ok_destination = false;
        foreach ( $contents_destination->results[0]->address_components as $component ){
            if( $component->long_name == "Île-de-France" )
                $region_ok_destination = true;
        }

        if(  $region_ok_origin && $region_ok_destination )
            return true;
        else
            return false;
    }

    public function getPrice( $duree , $distance){
        $variable_minute = 0.5;
        $variable_distance = 1;
        return (( $distance / 1000 )* $variable_distance)   + ( ( $duree /  60 ) * $variable_minute);
    }

    public function saveInvoice( $reservation , $service , $html   ){
        $pdf = $service->create();
        $html = $this->renderView("AppBundle:Reservation:invoice.html.twig");
        // Ajout page
        $pdf->AddPage();
        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Output(__DIR__."/../../../web/bundles/app/factures/facture.pdf", 'F');

    }

    public function isValidRoute( $contents ){
        if( $contents->rows[0]->elements[0]->status == "ZERO_RESULTS" )
            return false;
        return true;
    }


    public function getErrors($infoDrive , $devis){
        $erreurs = [];
        if( $infoDrive == "KO")
            $erreurs[] =  "Itinéraire impossible, veuillez choisir un autre itinéraire ";

        $currentDateOneHours = (new \DateTime())->getTimestamp() + 3600 ;
        $devisTimeStamp = $devis->getDatePrevu();
        $devisTimeStamp = date_create_from_format("d/m/Y H:i" , $devisTimeStamp)->getTimestamp();

        if(  $currentDateOneHours > $devisTimeStamp )
            $erreurs[] =  "Réserver 1 heure plus tard";

        return $erreurs;
    }



    public function encrypt($pure_string, $encryption_key) {
        $iv_size = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $encrypted_string = mcrypt_encrypt(MCRYPT_BLOWFISH, $encryption_key, utf8_encode($pure_string), MCRYPT_MODE_ECB, $iv);
        return $encrypted_string;
    }

    public function decrypt($encrypted_string, $encryption_key) {
        $iv_size = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $decrypted_string = mcrypt_decrypt(MCRYPT_BLOWFISH, $encryption_key, $encrypted_string, MCRYPT_MODE_ECB, $iv);
        return $decrypted_string;
    }


}

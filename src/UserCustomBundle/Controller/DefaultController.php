<?php

namespace UserCustomBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{

    /**
     * Edit the user.
     *
     * @param Request $request
     * @Route("/log", name="usercustom_log")
     * @return Response
     */
    public function logAction(Request $request) {
        return $this->render('UserCustomBundle:Default:success.html.twig');
    }


    /**
     * Edit the user.
     *
     * @param Request $request
     * @Route("/register_confirm", name="usercustom_register_confirm")
     * @return Response
     */
    public function registerConfirmAction(Request $request) {
        return $this->render('UserCustomBundle:Default:success.html.twig');
    }

}

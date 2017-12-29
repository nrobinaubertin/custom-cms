<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ContactController extends Controller
{
    public function indexAction()
    {
        return $this->render('@App/contact.html.twig', array(
            "breadcrumbs" => [
                ["/", "Accueil"],
                ["/contact", "contact"]
            ],
            "title" => "CONTACT",
        ));
    }

    public function sendAction(Request $request)
    {
        $message = (new \Swift_Message('Mail de contact'))
            ->setFrom($request->request->get('sender'))
            ->setTo('elzire.fr@gmail.com')
            ->setBody($request->request->get('message'), 'text/plain');

        $this->get('mailer')->send($message);
        return $this->render('@App/send.html.twig');
    }
}

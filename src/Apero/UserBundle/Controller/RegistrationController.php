<?php

namespace Apero\UserBundle\Controller;

use FOS\UserBundle\Controller\RegistrationController as BaseController;
use Symfony\Component\HttpFoundation\Request;

class RegistrationController extends BaseController
{
    public function registerAction(Request $request)
    {
        parent::registerAction();

        $message = \Swift_Message::newInstance()
                    ->setSubject("Nouvel Utilisateur")
                    ->setFrom('admin@perchut.org')
                    ->setTo('admin@perchut.org')
                    ->setBody($this->renderView('AperoUserBundle:Mail:new_user.html.twig', array('user' => $user) ), 'text/html')
        ;
        $this->get('mailer')->send($message);
    }
}


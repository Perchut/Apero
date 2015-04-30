<?php

namespace Apero\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class AmisController extends Controller
{
    public function indexAction()
    {
    	if (!$this->get('security.context')->isGranted('ROLE_VALIDATE'))
        {
            return $this->redirect($this->generateUrl('apero_user_role_non_validate'));
        }

    	$user_manager = $this->get('fos_user.user_manager');
    	$users = $user_manager->findUsers();

        return $this->render('AperoUserBundle:Amis:index.html.twig', array(
            'user' => $this->getUser(),
            'amis' => $users,
        ));
    }
   
}
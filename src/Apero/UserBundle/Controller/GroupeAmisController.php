<?php

namespace Apero\UserBundle\Controller;

use Apero\UserBundle\Entity\User;
use Apero\UserBundle\Entity\GroupeAmis;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class GroupeAmisController extends Controller
{
    public function indexAction()
    {
    	if (!$this->get('security.context')->isGranted('ROLE_ADMIN'))
        {
            return $this->redirect($this->generateUrl('apero_user_role_non_admin'));
        }

        $listGroupesAmis = $this
                        ->getDoctrine()
                        ->getManager()
                        ->getRepository('AperoUserBundle:GroupeAmis')
                        ->findAll()
        ;


        return $this->render('AperoUserBundle:GroupeAmis:index.html.twig', array(
            'listGroupesAmis' => $listGroupesAmis,
        ));
    }
   
}
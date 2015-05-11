<?php

namespace Apero\UserBundle\Controller;

use Apero\UserBundle\Entity\AmisGroupe;
use Apero\UserBundle\Entity\User;
use Apero\UserBundle\Entity\GroupeAmis;
use Apero\UserBundle\Form\GroupeAmisType;
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

        $em = $this->getDoctrine()->getManager();

        $groupeList = $em->getRepository('AperoUserBundle:GroupeAmis')->findbyName($this->getUser()->getUsername());

        if (null === $groupeList)
        {
            throw $this->createNotFoundException("Le groupe: ".$this->getUser()->getUsername()." n'existe pas.");
        }

        $groupe = $groupeList[0];

        $listeAmisGroupes = $em->getRepository('AperoUserBundle:AmisGroupe')->findbyGroupe($groupe->getId());

        return $this->render('AperoUserBundle:Amis:view.html.twig', array(
            'groupe' => $groupe,
            'listeAmisGroupes' => $listeAmisGroupes,
        ));
    }

    public function editAction(Request $request)
    {
        if (!$this->get('security.context')->isGranted('ROLE_VALIDATE'))
        {
            return $this->redirect($this->generateUrl('apero_user_role_non_validate'));
        }

        $em = $this->getDoctrine()->getManager();
        $groupeList = $em->getRepository('AperoUserBundle:GroupeAmis')->findbyName($this->getUser()->getUsername());

        if (null === $groupeList)
        {
            throw $this->createNotFoundException("Le groupe: ".$this->getUser()->getUsername()." n'existe pas.");
        }

        $groupe = $groupeList[0];

        $listeAmisGroupes = $em->getRepository('AperoUserBundle:AmisGroupe')->findbyGroupe($groupe->getId());
        $amis = "liste";

        if (count($listeAmisGroupes) != 0)
        {
            foreach ($listeAmisGroupes as $AmisGroupe)
            {
                $amis = $amis." ".$AmisGroupe->getUser()->getId();
            }
        }

        $um =$this->get('fos_user.user_manager');
        $allUsers = $um->findUsers();
        $total = count($allUsers);

        $listeUser = array();

        foreach ($allUsers as $user)
        {
            if ($user != $this->getUser())
            {
                $listeUser[$user->getId()] = $user->getUsername();
            }            
        }

        $formBuilder = $this->get('form.factory')->createBuilder(new GroupeAmisType(), $groupe);
        $formBuilder->remove('name');
        $formBuilder->add('amis', 'choice', array(
            'label' => 'Mes amis: ',
            'choices' => $listeUser,
            'multiple' => true,
            'expanded' => true,
            'mapped' => false,
        ));


        $formBuilder->add('Modifier', 'submit');

        $form = $formBuilder->getForm();


        if ($form->handleRequest($request)->isValid())
        {
            $data = $form->get('amis')->getData();
            $em->persist($groupe);

            $listeAmisID = array();
            foreach ($listeAmisGroupes as $AmisGroupe)
            {
                $listeAmisID[] = $AmisGroupe->getUser()->getID();
            }

            foreach ($listeAmisID as $AmisID)
            {
                if(!in_array($AmisID, $data))
                {
                    $amisGroupe = $em->getRepository('AperoUserBundle:AmisGroupe')->findbyGroupeAndUser($groupe->getId(), $AmisID);
                    $em->remove($amisGroupe[0]);
                }
            }

            foreach ($data as $userID)
            {
                $existFriend = $em->getRepository('AperoUserBundle:AmisGroupe')->findbyGroupeAndUser($groupe->getId(), $userID);
                if (count($existFriend) <= 0)
                {
                    $amisGroupe = new AmisGroupe();
                    $amisGroupe->setUser($um->findUserBy(array('id' => $userID)));
                    $amisGroupe->setGroupe($groupe);
                    $amisGroupe->setFavoris(false);
                    $em->persist($amisGroupe);
                }
            }

            $em->flush();


            $request->getSession()->getFlashBag()->add('notice', 'Amis bien modifiés.');

            return $this->redirect($this->generateUrl('apero_user_amis_homepage'));
        }

        return $this->render('AperoUserBundle:Amis:edit.html.twig', array(
          'form' => $form->createView(),
          'groupe' => $groupe,
          'total' => $total,
          'amis' => $amis,
        ));
    }
   
}
<?php

namespace Apero\UserBundle\Controller;

use Apero\UserBundle\Entity\AmisGroupe;
use Apero\UserBundle\Entity\User;
use Apero\UserBundle\Entity\GroupeAmis;
use Apero\UserBundle\Form\GroupeAmisType;
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

    public function addAction(Request $request)
    {
        if (!$this->get('security.context')->isGranted('ROLE_ADMIN'))
        {
            return $this->redirect($this->generateUrl('apero_user_role_non_admin'));
        }

        $groupeAmis = new GroupeAmis();
        $formBuilder = $this->get('form.factory')->createBuilder(new GroupeAmisType(), $groupeAmis);
        $formBuilder->add('Creer',   'submit');

        $form = $formBuilder->getForm();


        if ($form->handleRequest($request)->isValid())
        {
            $em = $this->getDoctrine()->getManager();
            $em->persist($groupeAmis);
            $em->flush();

            $request->getSession()->getFlashBag()->add('notice', 'Groupe bien enregistré.');

            return $this->redirect($this->generateUrl('apero_user_groupe_amis_homepage'));
        }

        return $this->render('AperoUserBundle:GroupeAmis:add.html.twig', array(
          'form' => $form->createView(),
        ));
    }

    public function viewAction($id)
    {
        if (!$this->get('security.context')->isGranted('ROLE_ADMIN'))
        {
            return $this->redirect($this->generateUrl('apero_user_role_non_admin'));
        }

        $em = $this->getDoctrine()->getManager();
        $groupe = $em->getRepository('AperoUserBundle:GroupeAmis')->find($id);

        if (null === $groupe)
        {
            throw $this->createNotFoundException("Le groupe d'id ".$id." n'existe pas.");
        }

        $listeAmisGroupes = $em->getRepository('AperoUserBundle:AmisGroupe')->findbyGroupe($groupe->getId());

        return $this->render('AperoUserBundle:GroupeAmis:view.html.twig', array(
            'groupe' => $groupe,
            'listeAmisGroupes' => $listeAmisGroupes,
        ));
    }

    public function editAction($id, Request $request)
    {
        if (!$this->get('security.context')->isGranted('ROLE_ADMIN'))
        {
            return $this->redirect($this->generateUrl('apero_user_role_non_admin'));
        }

        $em = $this->getDoctrine()->getManager();
        $groupe = $em->getRepository('AperoUserBundle:GroupeAmis')->find($id);

        if (null === $groupe)
        {
            throw $this->createNotFoundException("Le groupe d'id ".$id." n'existe pas.");
        }

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


            $request->getSession()->getFlashBag()->add('notice', 'Groupe bien modifié.');

            return $this->redirect($this->generateUrl('apero_user_groupe_amis_view', array('id' => $groupe->getId())));
        }

        return $this->render('AperoUserBundle:GroupeAmis:edit.html.twig', array(
          'form' => $form->createView(),
          'groupe' => $groupe,
          'total' => $total,
          'amis' => $amis,
        ));
    }

    public function deleteAction($id, Request $request)
    {
        if (!$this->get('security.context')->isGranted('ROLE_ADMIN'))
        {
            return $this->redirect($this->generateUrl('apero_user_role_non_admin'));
        }

        $em = $this->getDoctrine()->getManager();
        $groupe = $em->getRepository('AperoUserBundle:GroupeAmis')->find($id);

        if (null === $groupe)
        {
            throw $this->createNotFoundException("Le groupe d'id ".$id." n'existe pas.");
        }

        $form = $this->createFormBuilder()->getForm();

        if ($form->handleRequest($request)->isValid())
        {
            $em->remove($groupe);
            $em->flush();

            $request->getSession()->getFlashBag()->add('notice', "Le groupe a bien été supprimé.");

            return $this->redirect($this->generateUrl('apero_user_groupe_amis_homepage'));
        }

        return $this->render('AperoUserBundle:GroupeAmis:delete.html.twig', array(
          'groupe' => $groupe,
          'form' => $form->createView()
        ));
    }
   
}
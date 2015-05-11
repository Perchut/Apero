<?php

namespace Apero\EventBundle\Controller;

use Apero\EventBundle\Entity\Event;
use Apero\EventBundle\Entity\EventUser;
use Apero\EventBundle\Entity\Bar;
use Apero\UserBundle\Entity\User;
use Apero\EventBundle\Form\EventType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class EventController extends Controller
{
    public function indexAction()
    {
    	if (!$this->get('security.context')->isGranted('ROLE_VALIDATE'))
    	{
	    	return $this->redirect($this->generateUrl('apero_user_role_non_validate'));
	}

    	$em = $this->getdoctrine()->getManager();
    	$listEventsinvite = $em->getRepository('AperoEventBundle:Event')->getEventsWhereInvited($this->getUser());
    	$listEventsparticipant = $em->getRepository('AperoEventBundle:Event')->getEventsWhereParticipant($this->getUser());

        return $this->render('AperoEventBundle:Event:index.html.twig', array(
        	'listEventsinvite' => $listEventsinvite,
        	'listEventsparticipant' => $listEventsparticipant,
        ));
    }

    public function addAction(Request $request)
    {
    	if (!$this->get('security.context')->isGranted('ROLE_VALIDATE'))
    	{
	    	return $this->redirect($this->generateUrl('apero_user_role_non_validate'));
	    }

    	$event = new Event();
    	$invites = $this->getListeInvites($this->getUser());
		$formBuilder = $this->get('form.factory')->createBuilder(new EventType(), $event);
		$formBuilder->add('invites', 'choice', array(
			'label' => 'Invités: ',
			'choices' => $invites,
			'multiple' => true,
			'mapped' => false,
		));
		$formBuilder->add('Creer',   'submit');

		$form = $formBuilder->getForm();

    	if ($form->handleRequest($request)->isValid())
    	{
    		$em = $this->getdoctrine()->getManager();
    		$event->setCreatedBy($this->getUser());
    		$em->persist($event);

    		$eventUser = new EventUser();
    		$eventUser->setEvent($event);
    		$eventUser->setUser($this->getUser());
    		$eventUser->setInvite(true);
    		$eventUser->setParticipant(true);
    		$em->persist($eventUser);

    		$um =$this->get('fos_user.user_manager');
    		$invites = $form->get('invites')->getData();
            $invites_name = array();
    		foreach ($invites as $inviteID)
    		{
    			$invite = $um->findUserBy(array('id' => $inviteID));
                $invites_name[] = $invite->getUsername();
    			$eventUser = new EventUser();
    			$eventUser->setEvent($event);
    			$eventUser->setUser($invite);
    			$eventUser->setInvite(true);
    			$em->persist($eventUser);

                $message = \Swift_Message::newInstance()
                            ->setSubject("Invitation à un Evènement")
                            ->setFrom('admin@perchut.org')
                            ->setTo($invite->getEmail())
                            ->setBody($this->renderView('AperoEventBundle:Event:mail_invitation.html.twig', array('event' => $event, 'user' => $this->getUser())), 'text/html')
                ;
                $this->get('mailer')->send($message);
    		}
    		$em->flush();

    		$request->getSession()->getFlashBag()->add('notice', 'Evènement bien enregistré.');

            $message = \Swift_Message::newInstance()
                        ->setSubject("Création d'un Evènement")
                        ->setFrom('admin@perchut.org')
                        ->setTo($this->getUser()->getEmail())
                        ->setBody($this->renderView('AperoEventBundle:Event:mail_new.html.twig', array('event' => $event, 'invites' => $invites_name)), 'text/html')
            ;
            $this->get('mailer')->send($message);

    		return $this->redirect($this->generateUrl('apero_event_view', array('id' => $event->getId())));
	    }

	    return $this->render('AperoEventBundle:Event:add.html.twig', array(
	      'form' => $form->createView(),
	    ));
    }

    public function viewAction($id)
    {
    	if (!$this->get('security.context')->isGranted('ROLE_VALIDATE'))
    	{
	    	return $this->redirect($this->generateUrl('apero_user_role_non_validate'));
	    }

    	$em = $this->getDoctrine()->getManager();
	    $event = $em->getRepository('AperoEventBundle:Event')->find($id);

	    if (null === $event)
	    {
	    	throw $this->createNotFoundException("L'évènement' d'id ".$id." n'existe pas.");
	    }

	    $isParticipant = false;
        $isInvite = false;
	    foreach ($event->getEventUsers() as $eventUser)
	    {
	    	if($eventUser->getUser() == $this->getUser() && $eventUser->getParticipant() == true)
	    	{
	    		$isParticipant = true;
	    	}
            if($eventUser->getUser() == $this->getUser() && $eventUser->getInvite() == true)
            {
                $isInvite = true;
            }
	    }

	    return $this->render('AperoEventBundle:Event:view.html.twig', array(
	    	'event' => $event,
	    	'isParticipant' => $isParticipant,
            'isInvite' => $isInvite,
	    ));
    }

    public function editAction($id, Request $request)
    {
    	if (!$this->get('security.context')->isGranted('ROLE_VALIDATE'))
    	{
	    	return $this->redirect($this->generateUrl('apero_user_role_non_validate'));
	    }

    	$em = $this->getDoctrine()->getManager();
	    $event = $em->getRepository('AperoEventBundle:Event')->find($id);
	    if (null === $event)
	    {
	    	throw $this->createNotFoundException("L'évènement' d'id ".$id." n'existe pas.");
	    }
	    $listeInvites = $this->getListeInvites($this->getUser());
        $invites = $this->getInvites($event);
        $formBuilder = $this->get('form.factory')->createBuilder(new EventType(), $event);
		$formBuilder->add('invites', 'choice', array(
			'label' => 'Invités',
			'choices' => $listeInvites,
			'multiple' => true,
			'mapped' => false,
            'data' => array_keys($invites),
		));
		$formBuilder->add('Modifier',   'submit');

		$form = $formBuilder->getForm();


    	if ($form->handleRequest($request)->isValid())
    	{
            $invites = $form->get('invites')->getData();
    		$um =$this->get('fos_user.user_manager');
    		$eventUsers = $event->getEventUsers();
            $participantsID = array();
    		foreach ($eventUsers as $eventUser)
    		{
                if ($eventUser->getParticipant() == true)
                {
                    $user = $eventUser->getUser();
                    $participantsID[] = $user->getID();
                }
    			if ($eventUser->getUser() != $event->getCreatedBy())
                {
                    $event->removeEventUser($eventUser);
                    $em->remove($eventUser);
                }
    		}
            foreach ($participantsID as $participantID)
            {
                if(!in_array($participantID, $invites))
                {
                    $participant = $um->findUserBy(array('id' => $participantID));
                    $eu = $em->getRepository('AperoEventBundle:EventUser')->findbyEventandUser($event, $participant);
                    $event->removeEventUser($eu);
                    $em->remove($eu);
                }
            }
    		foreach ($invites as $inviteID)
    		{
    			$invite = $um->findUserBy(array('id' => $inviteID));
    			$newInvite[] = $invite;
    			foreach ($eventUsers as $eventUser)
    			{
    				if ($eventUser->getUser() == $invite)
    				{
    					$eventUser->setInvite(true);
    				}
    				else
    				{
    					$eventUser = new EventUser();
    					$eventUser->setEvent($event);
    					$eventUser->setUser($invite);
    					$eventUser->setInvite(true);
    				}
    				$em->persist($eventUser);
    			}
    		}
    		$em->persist($event);
    		$em->flush();

    		$request->getSession()->getFlashBag()->add('notice', 'Evènement bien modifié.');

    		return $this->redirect($this->generateUrl('apero_event_view', array('id' => $event->getId())));
	    }

	    return $this->render('AperoEventBundle:Event:edit.html.twig', array(
	      'form' => $form->createView(),
	      'event' => $event,
	    ));
    }

    public function deleteAction($id, Request $request)
    {
    	if (!$this->get('security.context')->isGranted('ROLE_VALIDATE'))
    	{
    		return $this->redirect($this->generateUrl('apero_user_role_non_validate'));
	    }

    	$em = $this->getDoctrine()->getManager();
	    $event = $em->getRepository('AperoEventBundle:Event')->find($id);

	    if (null === $event)
	    {
	    	throw $this->createNotFoundException("L'évènement' d'id ".$id." n'existe pas.");
	    }

	    $form = $this->createFormBuilder()->getForm();

	    if ($form->handleRequest($request)->isValid())
	    {
            $eventUsers = $event->getEventUsers();
            foreach ($eventUsers as $eventUser)
            {
                $em->remove($eventUser);
            }
	    	$em->remove($event);
	    	$em->flush();

	    	$request->getSession()->getFlashBag()->add('notice', "L'évènement a bien été supprimé.");

	    	return $this->redirect($this->generateUrl('apero_event_homepage'));
	    }

	    return $this->render('AperoEventBundle:Event:delete.html.twig', array(
	      'event' => $event,
	      'form'   => $form->createView()
	    ));
    }

    public function joinAction($id, Request $request)
    {
    	if (!$this->get('security.context')->isGranted('ROLE_VALIDATE'))
    	{
	    	return $this->redirect($this->generateUrl('apero_user_role_non_validate'));
	    }

    	$em = $this->getDoctrine()->getManager();
	    $event = $em->getRepository('AperoEventBundle:Event')->find($id);
        
	    if (null === $event)
	    {
	    	throw $this->createNotFoundException("L'évènement' d'id ".$id." n'existe pas.");
	    }

        $eventUsers = $event->getEventUsers();
        {
            foreach ($eventUsers as $eventUser)
            {
                if($eventUser->getUser() == $this->getUser())
                {
                    $eventUser->setParticipant(true);
                    $em->persist($eventUser);
                }
            }
        }	    
    	$em->flush();

    	$request->getSession()->getFlashBag()->add('notice', "Vous avez bien rejoint l'évènement.");

        $message = \Swift_Message::newInstance()
                    ->setSubject("Participation à un Evènement")
                    ->setFrom('admin@perchut.org')
                    ->setTo($event->getCreatedBy()->getEmail())
                    ->setBody($this->renderView('AperoEventBundle:Event:mail_join.html.twig', array('event' => $event, 'user' => $this->getUser())), 'text/html')
        ;
        $this->get('mailer')->send($message);

		return $this->redirect($this->generateUrl('apero_event_view', array('id' => $event->getId())));
    }

    public function leaveAction($id, Request $request)
    {
    	if (!$this->get('security.context')->isGranted('ROLE_VALIDATE'))
    	{
	    	return $this->redirect($this->generateUrl('apero_user_role_non_validate'));
	    }

    	$em = $this->getDoctrine()->getManager();
	    $event = $em->getRepository('AperoEventBundle:Event')->find($id);

	    if (null === $event)
	    {
	    	throw $this->createNotFoundException("L'évènement' d'id ".$id." n'existe pas.");
	    }

        $eventUsers = $event->getEventUsers();
        {
            foreach ($eventUsers as $eventUser)
            {
                if($eventUser->getUser() == $this->getUser())
                {
                    $eventUser->setParticipant(false);
                    $em->persist($eventUser);
                }
            }
        }   
    	$em->flush();

    	$request->getSession()->getFlashBag()->add('notice', "Vous avez bien quitté l'évènement.");

        $message = \Swift_Message::newInstance()
                    ->setSubject("Départ d'un Evènement")
                    ->setFrom('admin@perchut.org')
                    ->setTo($event->getCreatedBy()->getEmail())
                    ->setBody($this->renderView('AperoEventBundle:Event:mail_leave.html.twig', array('event' => $event, 'user' => $this->getUser())), 'text/html')
        ;
        $this->get('mailer')->send($message);

    	return $this->redirect($this->generateUrl('apero_event_view', array('id' => $event->getId())));
    }

    public function getListeInvites($currentUser)
    {
        $em = $this->getDoctrine()->getManager();
        $listgroupe = $em->getRepository('AperoUserBundle:GroupeAmis')->findbyName($currentUser->getUsername());
        $groupe = $listgroupe[0];

        $listeAmisGroupe = $em->getRepository('AperoUserBundle:AmisGroupe')->findbyGroupe($groupe->getId());

    	$invites = array();
    	foreach ($listeAmisGroupe as $AmisGroupe)
    	{
    		if($AmisGroupe->getUser() != $currentUser)
    		{
    			$invites[$AmisGroupe->getUser()->getId()] = $AmisGroupe->getUser()->getUsername();
    		}
    	}

    	return $invites;
    }

    public function getInvites($event)
    {
        $invites = array();
        $eventUsers = $event->getEventUsers();
        foreach ($eventUsers as $eventUser)
        {
            if ($eventUser->getInvite() == true)
            {
                $user = $eventUser->getUser();
                $invites[$user->getId()] = $user->getUsername();
            }
        }

        return $invites;
    }
}

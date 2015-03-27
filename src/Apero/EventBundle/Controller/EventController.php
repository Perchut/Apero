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
			'label' => 'Invités',
			'choices' => $invites,
			'multiple' => true,
			'mapped' =>false,
		));
		$formBuilder->add('Créer',   'submit');

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
    		foreach ($invites as $inviteID)
    		{
    			$invite = $um->findUserBy(array('id' => $inviteID));
    			$eventUser = new EventUser();
    			$eventUser->setEvent($event);
    			$eventUser->setUser($invite);
    			$eventUser->setInvite(true);
    			$em->persist($eventUser);
    		}
    		$em->flush();

    		$request->getSession()->getFlashBag()->add('notice', 'Evènement bien enregistré.');

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
	    foreach ($event->getEventUsers() as $eventUser)
	    {
	    	if($eventUser->getUser() == $this->getUser() && $eventUser->getParticipant() == true)
	    	{
	    		$isParticipant = true;
	    	}
	    }

	    return $this->render('AperoEventBundle:Event:view.html.twig', array(
	    	'event' => $event,
	    	'isParticipant' => $isParticipant,
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
	    $invites = $this->getListeInvites($this->getUser());
	    $formBuilder = $this->get('form.factory')->createBuilder(new EventType(), $event);
		$formBuilder->add('invites', 'choice', array(
			'label' => 'Invités',
			'choices' => $invites,
			'multiple' => true,
			'mapped' => false,
		));
		$formBuilder->add('Modifier',   'submit');

		$form = $formBuilder->getForm();


    	if ($form->handleRequest($request)->isValid())
    	{
    		$data = $form->getData();
    		$event = $data['event'];

    		$um =$this->get('fos_user.user_manager');
    		$eventUsers = $event->getEventUsers;
    		$oldInvite = array();
    		$newInvite = array();
    		foreach ($eventUsers as $eventUser)
    		{
    			$oldInvite[] = $eventUser->getUser();
    		}
    		foreach ($data['invites'] as $inviteID)
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
    		foreach ($oldInvite as $old)
    		{
    			if(!(in_array($old, $newInvite)))
    			{
    				foreach ($eventUsers as $eventUser)
    				{
    					if ($eventUser->getUser() == $old)
    					{
    						$event->removeEventUser($eventUser);
    					}
    				}
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

	    $event->addParticipant($this->getUser());
	    $em->persist($event);
    	$em->flush();

    	$request->getSession()->getFlashBag()->add('notice', "Vous avez bien rejoint l'évènement.");

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

	    $event->removeParticipant($this->getUser());
	    $em->persist($event);
    	$em->flush();

    	$request->getSession()->getFlashBag()->add('notice', "Vous avez bien quitté l'évènement.");

    	return $this->redirect($this->generateUrl('apero_event_view', array('id' => $event->getId())));
    }

    public function getListeInvites($currentUser)
    {
    	$userManager = $this->get('fos_user.user_manager');
    	$users = $userManager->findUsers();
    	$invites = array();
    	foreach ($users as $user)
    	{
    		if($user != $currentUser)
    		{
    			$invites[$user->getId()] = $user->getUsername();
    		}
    	}

    	return $invites;
    }
}

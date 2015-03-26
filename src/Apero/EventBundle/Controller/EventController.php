<?php

namespace Apero\EventBundle\Controller;

use Apero\EventBundle\Entity\Event;
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
    	$listEvents = $em->getRepository('AperoEventBundle:Event')->findAll();

        return $this->render('AperoEventBundle:Event:index.html.twig', array('listEvents' => $listEvents));
    }

    public function addAction(Request $request)
    {
    	if (!$this->get('security.context')->isGranted('ROLE_VALIDATE'))
    	{
	    	return $this->redirect($this->generateUrl('apero_user_role_non_validate'));
	    }

    	$event = new Event();
    	$event->setCreatedBy($this->getUser());
    	$event->addParticipant($this->getUser());
    	$event->setDate(new \Datetime());
    	$formBuilder = $this->get('form.factory')->createBuilder(new EventType(), $event);   	
    	$formBuilder->add('Créer',   'submit');

    	$form = $formBuilder->getForm();


    	if ($form->handleRequest($request)->isValid())
    	{
	    	$em = $this->getdoctrine()->getManager();
    		$em->persist($event);
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
	    foreach ($event->getParticipants() as $participant)
	    {
	    	if($participant == $this->getUser())
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
	    $formBuilder = $this->get('form.factory')->createBuilder(new EventType(), $event);	    
    	$formBuilder->add('Modifier',   'submit');

    	$form = $formBuilder->getForm();

    	if ($form->handleRequest($request)->isValid())
    	{
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
}

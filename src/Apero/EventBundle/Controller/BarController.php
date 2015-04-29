<?php

namespace Apero\EventBundle\Controller;

use Apero\EventBundle\Entity\Bar;
use Apero\EventBundle\Form\BarType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class BarController extends Controller
{
    public function indexAction()
    {
        if (!$this->get('security.context')->isGranted('ROLE_VALIDATE'))
        {
            return $this->redirect($this->generateUrl('apero_user_role_non_validate'));
        }

    	$listBars = $this
    					->getDoctrine()
    					->getManager()
    					->getRepository('AperoEventBundle:Bar')
    					->findAll()
    	;


        return $this->render('AperoEventBundle:Bar:index.html.twig', array('listBars' => $listBars));
    }

    public function addAction(Request $request)
    {
        if (!$this->get('security.context')->isGranted('ROLE_VALIDATE'))
        {
            return $this->redirect($this->generateUrl('apero_user_role_non_validate'));
        }

    	$bar = new Bar();
    	$formBuilder = $this->get('form.factory')->createBuilder(new BarType(), $bar);
    	$formBuilder->add('Creer',   'submit');

    	$form = $formBuilder->getForm();


    	if ($form->handleRequest($request)->isValid())
    	{
    		$em = $this->getDoctrine()->getManager();
    		$em->persist($bar);
    		$em->flush();

    		$request->getSession()->getFlashBag()->add('notice', 'Bar bien enregistré.');
            $message = \Swift_Message::newInstance()
			->setSubject("Création d'un Bar")
			->setFrom('admin@perchut.org')
			->setTo($this->getUser()->getEmail())
			->setBody($this->renderView('AperoEventBundle:Bar:mail_new.html.twig', array('bar' => $bar, 'user' => $this->getUser())), 'text/html')
		;
		$this->get('mailer')->send($message);

    		return $this->redirect($this->generateUrl('apero_bar_view', array('id' => $bar->getId())));
	    }

	    return $this->render('AperoEventBundle:Bar:add.html.twig', array(
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
	    $bar = $em->getRepository('AperoEventBundle:Bar')->find($id);

	    if (null === $bar)
	    {
            throw $this->createNotFoundException("Le bar d'id ".$id." n'existe pas.");
	    }

	    return $this->render('AperoEventBundle:Bar:view.html.twig', array(
	    	'bar' => $bar
	    ));
    }

    public function editAction($id, Request $request)
    {
        if (!$this->get('security.context')->isGranted('ROLE_VALIDATE'))
        {
            return $this->redirect($this->generateUrl('apero_user_role_non_validate'));
        }

    	$em = $this->getDoctrine()->getManager();
	    $bar = $em->getRepository('AperoEventBundle:Bar')->find($id);

	    if (null === $bar)
	    {
	    	throw $this->createNotFoundException("Le bar d'id ".$id." n'existe pas.");
	    }

	    $formBuilder = $this->get('form.factory')->createBuilder(new BarType(), $bar);
    	$formBuilder->add('Modifier',   'submit');

    	$form = $formBuilder->getForm();

    	if ($form->handleRequest($request)->isValid())
    	{
    		$em->persist($bar);
    		$em->flush();

    		$request->getSession()->getFlashBag()->add('notice', 'Bar bien modifié.');

    		return $this->redirect($this->generateUrl('apero_bar_view', array('id' => $bar->getId())));
	    }

	    return $this->render('AperoEventBundle:Bar:edit.html.twig', array(
	      'form' => $form->createView(),
	      'bar' => $bar,
	    ));
    }

    public function deleteAction($id, Request $request)
    {
        if (!$this->get('security.context')->isGranted('ROLE_ADMIN'))
        {
            return $this->redirect($this->generateUrl('apero_user_role_non_admin'));
        }

    	$em = $this->getDoctrine()->getManager();
	    $bar = $em->getRepository('AperoEventBundle:Bar')->find($id);

	    if (null === $bar)
	    {
	    	throw $this->createNotFoundException("Le bar d'id ".$id." n'existe pas.");
	    }

	    $form = $this->createFormBuilder()->getForm();

	    if ($form->handleRequest($request)->isValid())
	    {
	    	$em->remove($bar);
	    	$em->flush();

	    	$request->getSession()->getFlashBag()->add('notice', "Le bar a bien été supprimé.");

	    	return $this->redirect($this->generateUrl('apero_bar_homepage'));
	    }

	    return $this->render('AperoEventBundle:Bar:delete.html.twig', array(
	      'bar' => $bar,
	      'form' => $form->createView()
	    ));
    }
}

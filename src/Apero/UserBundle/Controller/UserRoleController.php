<?php

namespace Apero\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class UserRoleController extends Controller
{
    public function indexAction()
    {
    	$user_manager = $this->get('fos_user.user_manager');
    	$users = $user_manager->findUsers();

        return $this->render('AperoUserBundle:UserRole:index.html.twig', array('users' => $users));
    }

    public function changeAction($username, Request $request)
    {
    	$user_manager = $this->get('fos_user.user_manager');
    	$user = $user_manager->findUserByUsername($username);

    	if (null === $user)
	    {
	    	throw new NotFoundHttpException("Le user: ".$username." n'existe pas.");
	    }

	    $defaultData = array();
	    $builder = $this->createFormBuilder($defaultData);
	    $builder->add('role', 'choice', array(
	    	'choices' => array('ROLE_USER' => 'Utilisateur non validé', 'ROLE_VALIDATE' => 'Utilisateur validé', 'ROLE_ADMIN' => 'Administrateur'),
	    ));
	    $builder->add('Modifier', 'submit');
	    $form = $builder->getForm();

	    $form->handleRequest($request);

	    if($form->isValid())
	    {
	    	$data = $form->getData();
	    	$newRole = array();
	    	$newRole[] = $data['role'];

	    	$user->setRoles($newRole);
	    	$user_manager->updateUser($user);

	    	$em = $this->getDoctrine()->getManager();
    		$em->flush();

    		return $this->redirect($this->generateUrl('apero_user_role_homepage'));
	    }

	    return $this->render('AperoUserBundle:UserRole:change.html.twig', array(
	      'form' => $form->createView(),
	      'user' => $user,
	    ));
    }
}
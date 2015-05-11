<?php

namespace Apero\UserBundle\Controller;

use Apero\UserBundle\Entity\GroupeAmis;
use FOS\UserBundle\Controller\RegistrationController as BaseController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\UserEvent;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\FilterUserResponseEvent;

class RegistrationController extends BaseController
{
    public function registerAction(Request $request)
    {
        /** @var $formFactory \FOS\UserBundle\Form\Factory\FactoryInterface */
        $formFactory = $this->container->get('fos_user.registration.form.factory');
        /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
        $userManager = $this->container->get('fos_user.user_manager');
        /** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
        $dispatcher = $this->container->get('event_dispatcher');
    
        $user = $userManager->createUser();
        $user->setEnabled(true);
    
        $dispatcher->dispatch(FOSUserEvents::REGISTRATION_INITIALIZE, new UserEvent($user, $request));
    
        $form = $formFactory->createForm();
        $form->setData($user);
    
        if ('POST' === $request->getMethod()) {
            $form->bind($request);
    
            if ($form->isValid()) {
                $event = new FormEvent($form, $request);
                $dispatcher->dispatch(FOSUserEvents::REGISTRATION_SUCCESS, $event);
    
                $userManager->updateUser($user);                
    
                if (null === $response = $event->getResponse()) {
                    $url = $this->container->get('router')->generate('fos_user_registration_confirmed');
                    $response = new RedirectResponse($url);
                }
    
                $dispatcher->dispatch(FOSUserEvents::REGISTRATION_COMPLETED, new FilterUserResponseEvent($user, $request, $response));

                //Ajout de la création du groupe pour les amis et du mail à l'admin.
                $groupe = new GroupeAmis();
                $groupe->setName($user->getUsername());
                $em = $this->getDoctrine()->getManager();
                $em->persist($groupe);
                $em->flush();

                $message = \Swift_Message::newInstance()
                            ->setSubject("Nouvel Utilisateur")
                            ->setFrom('admin@perchut.org')
                            ->setTo('admin@perchut.org')
                            ->setBody($this->renderView('AperoUserBundle:Mail:new_user.html.twig', array('user' => $user) ), 'text/html')
                ;
                $this->get('mailer')->send($message);
    
                return $response;
            }
        }
    
        return $this->container->get('templating')->renderResponse('FOSUserBundle:Registration:register.html.twig', array(
                'form' => $form->createView(),
        ));

        
    }
}


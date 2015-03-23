<?php

namespace Apero\EventBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class EventController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('AperoEventBundle:Event:index.html.twig', array('name' => $name));
    }
}

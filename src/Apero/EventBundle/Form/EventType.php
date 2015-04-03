<?php

namespace Apero\EventBundle\Form;

use Symfony\Component\Form\AbstractType;
use Apero\EventBundle\Entity\Event;
use Apero\EventBundle\Entity\Bar;
use Apero\EventBundle\Entity\EventUser;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EventType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array(
                'label' => 'Nom: ',
            ))
            ->add('date', 'datetime', array(
                'label' => 'Date :',
                'date_widget' => "single_text",
                'time_widget' => "single_text",
            ))
            ->add('bar',  'entity', array(
                'class' => 'AperoEventBundle:Bar',
                'property' => 'nom',
                'label' => 'Bar: '
            ))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Apero\EventBundle\Entity\Event'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'apero_eventbundle_event';
    }
}

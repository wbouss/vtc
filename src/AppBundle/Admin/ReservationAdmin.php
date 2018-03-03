<?php

namespace AppBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class ReservationAdmin extends AbstractAdmin
{
    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add('depart')
            ->add('arrive')
            ->add('date')
            ->add('nbPersonne')
            ->add('nbBagage')
            ->add('datePrevu')
            ->add('prix')
            ->add('duree')
            ->add('distance')

        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id')
            ->add('depart')
            ->add('arrive')
            ->add('date')
            ->add('nbPersonne')
            ->add('nbBagage')
            ->add('datePrevu')
            ->add('prix')
            ->add('duree')
            ->add('distance')

            ->add('_action', null, array(
                'actions' => array(
                    'show' => array(),
                    'edit' => array(),
                    'delete' => array(),
                ),
            ))
        ;
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('id')
            ->add('depart')
            ->add('arrive')
            ->add('date')
            ->add('nbPersonne')
            ->add('nbBagage')
            ->add('datePrevu')
            ->add('prix')
            ->add('duree')
            ->add('distance')

        ;
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('id')
            ->add('depart')
            ->add('arrive')
            ->add('date')
            ->add('nbPersonne')
            ->add('nbBagage')
            ->add('datePrevu')
            ->add('prix')
            ->add('duree')
            ->add('distance')
            ->add('user.firstname' )
            ->add('user.lastname' )


        ;
    }
}

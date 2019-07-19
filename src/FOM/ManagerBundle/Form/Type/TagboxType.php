<?php

namespace FOM\ManagerBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class TagboxType extends AbstractType
{
    public function getName()
    {
        return 'tagbox';
    }

    public function getBlockPrefix()
    {
        return 'tagbox';
    }

    public function getParent()
    {
        return CheckboxType::class;
    }
}

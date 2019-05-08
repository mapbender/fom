<?php

namespace FOM\ManagerBundle\Form\Type;

use Symfony\Component\Form\AbstractType;

class TagboxType extends AbstractType
{
    public function getName()
    {
        return 'tagbox';
    }

    public function getParent()
    {
        return 'checkbox';
    }
}

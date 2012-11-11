<?php

namespace FOM\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Acl\Model\AclProviderInterface;

use FOM\UserBundle\Form\DataTransformer\ACEDataTransformer;

class ACEType extends AbstractType
{
    protected $securityContext;
    protected $aclProvider;

    public function __construct(SecurityContext $securityContext,
        AclProviderInterface $aclProvider)
    {
        $this->securityContext = $securityContext;
        $this->aclProvider = $aclProvider;
    }

    public function getName()
    {
        return 'ace';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new ACEDataTransformer();
        $builder->prependNormTransformer($transformer);

        $builder->add('sid', 'text', array(
            'read_only' => true,
            'label' => 'Security identity'));

        if(array_key_exists('data', $options)) {
            print_r($options['data']);die;
        }
        foreach($options['available_permissions'] as $key => $value) {
            $builder->add('mask_' . $key, 'checkbox', array(
                'required' => false,
                //'read_only' => $options['read_only'],
                'property_path' => '[permissions][mask_' . $key .']',
                'label' => $value));
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'available_permissions' => array()));
    }
}

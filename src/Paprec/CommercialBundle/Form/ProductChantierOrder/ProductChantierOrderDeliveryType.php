<?php

namespace Paprec\CommercialBundle\Form\ProductChantierOrder;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductChantierOrderDeliveryType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('installationDate', DateType::class, array(
                'widget' => 'single_text',
                'html5' => false
            ))
            ->add('removalDate', DateType::class, array(
                'widget' => 'single_text',
                'html5' => false
            ))
            ->add('domainType', ChoiceType::class, array(
                'choices' => array(
                    'Matériel présent sur le domaine privé' => 'private',
                    'Matériel présent sur le domaine public' => 'public'
                ),
                'choice_attr' => function () {
                    return ['class' => 'input__radio'];
                },
                'expanded' => true
            ))
            ->add('accessConditions', TextareaType::class, array(
                'required' => false
            ));

    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Paprec\CommercialBundle\Entity\ProductChantierOrder',
            'validation_groups' => array('delivery'),

        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'paprec_commercialbundle_productchantierorderdelivery';
    }


}

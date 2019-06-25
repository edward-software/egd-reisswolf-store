<?php

namespace Paprec\CommercialBundle\Form\ProductD3EOrder;

use Paprec\CatalogBundle\Repository\ProductD3ERepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductD3EOrderLineAddType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('quantity', IntegerType::class, array(
                "required" => true
            ))
            ->add('productD3E', EntityType::class, array(
                'class' => 'PaprecCatalogBundle:ProductD3E',
                'query_builder' => function (ProductD3ERepository $er) {
                    return $er->createQueryBuilder('p')
                        ->where('p.deleted is NULL')
                        ->andWhere('p.isPackage = true')
                        ->andWhere('p.isDisplayed = true')
                        ->orderBy('p.name', 'ASC');
                },
                'choice_label' => 'name',
                'placeholder' => '',
                'empty_data' => null,
            ));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Paprec\CommercialBundle\Entity\ProductD3EOrderLine'
        ));
    }
}

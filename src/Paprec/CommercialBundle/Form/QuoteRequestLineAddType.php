<?php

namespace Paprec\CommercialBundle\Form;

use Paprec\CatalogBundle\Repository\ProductRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuoteRequestLineAddType extends AbstractType
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
            ->add('product', EntityType::class, array(
                'class' => 'PaprecCatalogBundle:Product',
                'query_builder' => function (ProductRepository $er) {
                    return $er->createQueryBuilder('p')
                        ->where('p.deleted is NULL');
                },
                'choice_label' => 'id',
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
            'data_class' => 'Paprec\CommercialBundle\Entity\QuoteRequestLine',
        ));
    }
}

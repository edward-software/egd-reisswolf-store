<?php

namespace Paprec\CommercialBundle\Form\ProductChantierOrder;

use Paprec\CatalogBundle\Repository\CategoryRepository;
use Paprec\CatalogBundle\Repository\ProductChantierRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductChantierOrderLineAddType extends AbstractType
{

    private $selectedProductId;

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->selectedProductId = $options['selectedProductId'];
        $builder
            ->add('quantity', IntegerType::class, array(
                "required" => true
            ))
            ->add('productChantier', EntityType::class, array(
                'class' => 'PaprecCatalogBundle:ProductChantier',
                'query_builder' => function (ProductChantierRepository $er) {
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
            'data_class' => 'Paprec\CommercialBundle\Entity\ProductChantierOrderLine',
            'selectedProductId' => null
        ));
    }
}

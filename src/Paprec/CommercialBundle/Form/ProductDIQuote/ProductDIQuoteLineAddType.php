<?php

namespace Paprec\CommercialBundle\Form\ProductDIQuote;

use Paprec\CatalogBundle\Repository\CategoryRepository;
use Paprec\CatalogBundle\Repository\ProductDIRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductDIQuoteLineAddType extends AbstractType
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
            ->add('productDI', EntityType::class, array(
                'class' => 'PaprecCatalogBundle:ProductDI',
                'query_builder' => function (ProductDIRepository $er) {
                    return $er->createQueryBuilder('p')
                        ->leftJoin('PaprecCatalogBundle:ProductDICategory', 'pc', \Doctrine\ORM\Query\Expr\Join::WITH, 'p.id = pc.category')
                        ->distinct()
                        ->where('p.deleted is NULL')
                        ->orderBy('p.name', 'ASC');
                },
                'choice_label' => 'name',
                'placeholder' => '',
                'empty_data' => null,
            ))
            ->add('category', EntityType::class, array(
                'class' => 'PaprecCatalogBundle:Category',
                'query_builder' => function (CategoryRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->innerJoin('PaprecCatalogBundle:ProductDICategory', 'pc', \Doctrine\ORM\Query\Expr\Join::WITH, 'c.id = pc.category')
                        //->join('pc.productDI', 'p')
                        ->where('c.division = \'DI\'')
                        ->andWhere('c.deleted is NULL')
                        ->andWhere('pc.productDI = :selectedProductId')
                        ->distinct()
                        ->orderBy('c.name', 'ASC')
                        ->setParameter('selectedProductId', $this->selectedProductId);
                },
                'placeholder' => '',
                'empty_data' => null
            ));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Paprec\CommercialBundle\Entity\ProductDIQuoteLine',
            'selectedProductId' => null
        ));
    }
}

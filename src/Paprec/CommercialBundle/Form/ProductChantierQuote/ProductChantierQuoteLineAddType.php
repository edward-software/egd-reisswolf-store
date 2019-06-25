<?php

namespace Paprec\CommercialBundle\Form\ProductChantierQuote;

use Paprec\CatalogBundle\Repository\CategoryRepository;
use Paprec\CatalogBundle\Repository\ProductChantierRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductChantierQuoteLineAddType extends AbstractType
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
                        ->leftJoin('PaprecCatalogBundle:ProductChantierCategory', 'pc', \Doctrine\ORM\Query\Expr\Join::WITH, 'p.id = pc.category')
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
                        ->innerJoin('PaprecCatalogBundle:ProductChantierCategory', 'pc', \Doctrine\ORM\Query\Expr\Join::WITH, 'c.id = pc.category')
                        ->where('c.division = \'CHANTIER\'')
                        ->andWhere('c.deleted is NULL')
                        ->andWhere('pc.productChantier = :selectedProductId')
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
            'data_class' => 'Paprec\CommercialBundle\Entity\ProductChantierQuoteLine',
            'selectedProductId' => null
        ));
    }
}

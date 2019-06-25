<?php

namespace Paprec\CommercialBundle\Form\ProductD3EQuote;

use Paprec\CatalogBundle\Repository\ProductD3ERepository;
use Paprec\CatalogBundle\Repository\TypeRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductD3EQuoteLineAddType extends AbstractType
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
            ->add('optDestruction', CheckboxType::class)
            ->add('optHandling', CheckboxType::class)
            ->add('optSerialNumberStmt', CheckboxType::class)
            ->add('productD3E', EntityType::class, array(
                'class' => 'PaprecCatalogBundle:ProductD3E',
                'query_builder' => function (ProductD3ERepository $er) {
                    return $er->createQueryBuilder('p')
                        ->leftJoin('PaprecCatalogBundle:ProductD3EType', 'pt', \Doctrine\ORM\Query\Expr\Join::WITH, 'p.id = pt.type')
                        ->distinct()
                        ->where('p.deleted is NULL')
                        ->andWhere('p.isPackage = false')
                        ->orderBy('p.name', 'ASC');
                },
                'choice_label' => 'name',
                'placeholder' => '',
                'empty_data' => null,
            ))
            ->add('type', EntityType::class, array(
                'class' => 'PaprecCatalogBundle:Type',
                'query_builder' => function (TypeRepository $er) {
                    return $er->createQueryBuilder('t')
                        ->innerJoin('PaprecCatalogBundle:ProductD3EType', 'pt', \Doctrine\ORM\Query\Expr\Join::WITH, 't.id = pt.type')
                        ->where('t.deleted is NULL')
                        ->andWhere('pt.productD3E = :selectedProductId')
                        ->distinct()
                        ->orderBy('t.name', 'ASC')
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
            'data_class' => 'Paprec\CommercialBundle\Entity\ProductD3EQuoteLine',
            'selectedProductId' => null

        ));
    }
}

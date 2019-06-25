<?php

namespace Paprec\HomeBundle\Form;

use Doctrine\ORM\EntityRepository;
use Ivory\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TicketShortType extends AbstractType
{
    var $options;

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->options = $options;

        $builder
            ->add('subject', TextType::class, array(
                'required' => true
            ))
            ->add('level', ChoiceType::class, array(
                'required' => true,
                'choices' => $this->options['levels']
            ))
            ->add('status', ChoiceType::class, array(
                'required' => true,
                'choices' => $this->options['status']
            ))
            ->add('description', CKEditorType::class, array(
                'config_name' => 'basic_config',
                'required' => true
            ))
            ->add('ticketCategory', EntityType::class, array(
                    'class' => 'Paprec\HomeBundle\Entity\TicketCategory',
                    'query_builder' => function (EntityRepository $er) {

                        $qb = $er->createQueryBuilder('tc');
                        $qb
                            ->where('tc.deleted IS NULL')
                            ->andWhere('tc.workspace = :workspace')
                            ->setParameter('workspace', $this->options['workspace'])
                            ->orderBy('tc.name', 'ASC')
                        ;

                        return $qb;
                    },
                    'choice_label' => 'name',
                    'placeholder' => 'Choose a category',
                    'required' => true
                )
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Paprec\HomeBundle\Entity\Ticket',
            'status' => null,
            'levels' => null,
            'workspace' => null
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'paprec_homebundle_ticket';
    }


}

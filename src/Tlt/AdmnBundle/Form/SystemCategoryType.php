<?php

namespace Tlt\AdmnBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class SystemCategoryType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('department','Symfony\Bridge\Doctrine\Form\Type\EntityType',array(
                'class' => 'Tlt\AdmnBundle\Entity\Department',
                'choice_label' => 'name',
                'label'		=> 'Departamentul'
            ))
            ->add('name',TextType::class,array(
                'attr' => array('max_length' => 255),
                'label' => 'Denumirea Categoriei'
            ))

/*            ->add('department')
            ->add('name')
*/
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Tlt\AdmnBundle\Entity\SystemCategory'
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'tlt_admnbundle_systemcategory';
    }
}
<?php
namespace Tlt\AdmnBundle\Form\Type;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tlt\AdmnBundle\Form\EventListener\CategoryListener;
use Tlt\ProfileBundle\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class SystemType extends AbstractType
{
    /*
introdus azi 04.07.2018
*/
    private $user;

    /**
     * @var ObjectManager
     */
    private $em;

/*    public function __construct(ObjectManager $em, User $user = null)
    {
        $this->em   = $em;
        $this->user	=	$user;
    }
*/
    /*   sfarsit introdus azi 04.07.2018
       */
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
	    $user = $options['user'];
	    $this->user = $user;
	    $em = $options['em'];
	    $this->em = $em;

		$builder
			->add('id', HiddenType::class)
			->add('name',TextType::class,array(
				'max_length' => 255,
				'label' => 'Denumire:'
				))
            ->add('criticality',TextType::class,array(
                    'max_length' => 64,
                    'label' => 'Criticalitate:'
                ))
			->add('department','Symfony\Bridge\Doctrine\Form\Type\EntityType',array(
				'class' => 'Tlt\AdmnBundle\Entity\Department',
				'choice_label' => 'name',
                'placeholder'	=> '-- Alegeti o optiune --',
				));
/*
                 $builder->add('category','entity',array(
                'class' => 'Tlt\AdmnBundle\Entity\SystemCategory',
                'label'=>'Categorie sistem',
                'property' => 'name',
                'query_builder' => function(EntityRepository $er) use ($options){
                    return $er->createQueryBuilder('categ')
                        ->where('categ.department = :department')
                        ->setParameter('department', $options['department']->getId());}
            ));
*/

//               if (array_key_exists('category', $options) && $options['category'])
                      $builder->addEventSubscriber(new CategoryListener( $this->em, $this->user ));

			$builder->add('salveaza', SubmitType::class)
			->add('reseteaza', ResetType::class, array());
	}
	
	public function configureOptions(OptionsResolver $resolver)
	{
/*		$resolver->setDefaults(array(
			'data_class' => 'Tlt\AdmnBundle\Entity\System',
		)); */

        $resolver->setDefaults(array(
            'data_class' => 'Tlt\AdmnBundle\Entity\System',
            'department' => null,
            'category'=>null,
            'em'=>false,
            'user'=>false
        ));
    }
	
	public function getBlockPrefix()
	{
		return 'system';
	}
}
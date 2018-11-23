<?php
namespace Tlt\AdmnBundle\Form\EventListener;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\PropertyAccess\PropertyAccess;

use Tlt\ProfileBundle\Entity\User;

class CategoryListener implements EventSubscriberInterface
{
    /**
     * @var ObjectManager
     */
    private $em;

    /**
     * @var User
     */
    private $user;

    /**
     * @var bool
     */
    private $showAll;


    /**
     * @param ObjectManager $em
     * @param User $user
     * @param bool $showAll
     */

    public function __construct(ObjectManager $em, User $user = null, $showAll = true)
    {
        $this->em   = $em;
        $this->user	=	$user;
        $this->showAll = $showAll;
    }

    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA  => 'preSetData',
            FormEvents::PRE_SUBMIT    => 'preSubmit'
        );
    }

    private function addCategoryForm($form, $department_id = null, $category_id = null)
    {
        $userBranches		=	$this->user->getBranchesIds();
        $userDepartments	=	$this->user->getDepartmentsIds();

        $formOptions = array(
            'class'         => 'TltAdmnBundle:SystemCategory',
            'label'         => 'Categorie Sistem',
            'attr'          => array(
                'class' => 'systemcategory_selector',
            )
        );

        if ($this->showAll)
        {
            $formOptions['required'] = false;
            $formOptions['placeholder'] = '-- Alegeti o optiune --';
           // $formOptions['property'] = 'name';
        }

        //print_r($department_id);
//      echo"<script> alert('aici addcategory in CategoryListener');</script>" ;

        $formOptions['query_builder'] = function (EntityRepository $repository) use ($department_id, $userBranches, $userDepartments) {
            $qb = $repository->createQueryBuilder('categ')
                ->where('categ.department = :department')
                ->setParameter('department', $department_id)
                ->orderby('categ.name', 'ASC');

            // if ($department_id)
/*            $qb->andWhere('sv.department = :department')
                ->setParameter('department', $department_id);
*/
            return $qb;
        };

        if (strlen($category_id)>0) {
            $category = $this->em
                ->getRepository('TltAdmnBundle:SystemCategory')
                ->find($category_id);

            if ($category != null)
                $formOptions['data'] = $category;
        } else {
            $formOptions['data'] = null;
        }

        $form->add('category', EntityType::class, $formOptions);
    }

    public function preSetData(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();

        if (null === $data) {
            return;
        }

        $accessor	= PropertyAccess::createPropertyAccessor();
        $department_id	=	($accessor->getValue($data, 'department')) ? $accessor->getValue($data, 'department')->getId() : null;
        $category_id	=	($accessor->getValue($data, 'category')) ? $accessor->getValue($data, 'category')->getId() : null;
// print_r($department_id);
//        echo"<script> alert('aici PRESETDATA in CategoryListener');</script>" ;
        $this->addCategoryForm($form, $department_id, $category_id);
    }

    public function preSubmit(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();

        $department_id = array_key_exists('department', $data) ? $data['department'] : null;
        $category_id = array_key_exists('category', $data) ? $data['category'] : null;
//        print_r($department_id);
//        die();
//        echo"<script> alert('aici presubmit in CategoryListener');</script>" ;
        $this->addCategoryForm($form, $department_id, $category_id);
    }
}
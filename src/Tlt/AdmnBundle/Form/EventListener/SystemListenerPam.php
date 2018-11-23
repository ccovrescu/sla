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

class SystemListenerPam implements EventSubscriberInterface
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
//        echo "<script>alert('SistemListenerPam Constructor!');</script>";
    }

    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA  => 'preSetData',
            FormEvents::PRE_SUBMIT    => 'preSubmit'
        );
    }
	
    private function addSystemForm($form, $department_id = null, $service_id = null, $system_id=null, $owner_id=null)
    {
        //echo "<script>alert('addSystemForm !!');</script>";

        $userBranches		=	$this->user->getBranchesIds();
		$userDepartments	=	$this->user->getDepartmentsIds();

        $formOptions = array(
            'class'         => 'TltAdmnBundle:System',
            'label'         => 'Sistemul',
            'attr'          => array(
                'class' => 'system_selector',
            )
        );

        if ($this->showAll)
        {
            $formOptions['required'] = false;
            $formOptions['placeholder'] = '-- Toate --';
        }

        $formOptions['query_builder'] = function (EntityRepository $repository) use ($department_id, $userBranches, $userDepartments, $service_id, $owner_id) {
                $qb = $repository->createQueryBuilder('sys')
                    ->innerJoin('sys.equipments', 'e')
                    ->innerJoin('e.service', 'srv')
                    ->andWhere('e.isActive=true')
                    ->andWhere('e.inPam=true')
                    ->andWhere('e.owner = :owner_id')
                    ->setParameter('owner_id', $owner_id)
                    ->orderBy('sys.name', 'ASC');

            if ($service_id) {
                $qb->andWhere('e.service = :service_id')
                    ->setParameter('service_id', $service_id);
            } elseif ($department_id) {
                $qb->andWhere('sys.department = :department')
                    ->setParameter('department', $department_id);
            }

            /*
                    $qb = $repository->createQueryBuilder('sys')
                    ->innerJoin('sys.mappings', 'mp')
                    ->innerJoin('mp.equipment', 'e')
                    ->innerJoin('e.service', 'srv')
                    ->andWhere('e.isActive=true')
                    ->andWhere('e.owner = :owner_id')
                    ->setParameter('owner_id', $owner_id)
                    ->orderBy('sys.name', 'ASC');

            if ($service_id) {
                $qb->andWhere('e.service = :service_id')
                    ->setParameter('service_id', $service_id);
            } elseif ($department_id) {
                $qb->andWhere('sys.department = :department')
                    ->setParameter('department', $department_id);
            }

             */

            return $qb;
            };

        if (strlen($system_id)>0) {
            $system = $this->em
                ->getRepository('TltAdmnBundle:System')
                ->find($system_id);

            if ($system != null)
                $formOptions['data'] = $system;
        } else {
            $formOptions['data'] = null;
        }

        $form->add('system', EntityType::class, $formOptions);
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
        $service_id	=	($accessor->getValue($data, 'service')) ? $accessor->getValue($data, 'service')->getId() : null;
        $system_id	=	($accessor->getValue($data, 'system')) ? $accessor->getValue($data, 'system')->getId() : null;
        $owner_id	=	($accessor->getValue($data, 'system')) ? $accessor->getValue($data, 'system')->getId() : null;
//        echo "<script>alert('preSETDATA !!');</script>";
//        echo "<script>alert($department_id);</script>";
        $this->addSystemForm($form, $department_id, $service_id, $system_id, $owner_id);
    }
	
    public function preSubmit(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();

        $department_id = array_key_exists('department', $data) ? $data['department'] : null;
        $service_id = array_key_exists('service', $data) ? $data['service'] : null;
        $system_id= array_key_exists('system', $data) ? $data['system'] : null;
        $owner_id= array_key_exists('owner', $data) ? $data['owner'] : null;
//        echo "<script>alert('presubmit !!');</script>";
//        echo "<script>alert($department_id);</script>";
        $this->addSystemForm($form, $department_id, $service_id, $system_id, $owner_id);

    }
}
<?php

namespace Openpp\PushNotificationBundle\Entity;

use Doctrine\Common\Persistence\ObjectManager;
use Openpp\PushNotificationBundle\Model\DeviceManager as BaseManager;
use Openpp\PushNotificationBundle\Model\DeviceInterface;
use Openpp\PushNotificationBundle\Model\ApplicationInterface;
use Application\Openpp\MapBundle\Entity\Circle;

class DeviceManager extends BaseManager
{
    protected $objectManager;
    protected $repository;
    protected $class;

    /**
     * Constructor
     *
     * @param ObjectManager $om
     * @param string $class
     */
    public function __construct(ObjectManager $om, $class)
    {
        $this->objectManager = $om;
        $this->repository = $om->getRepository($class);

        $metadata = $om->getClassMetadata($class);
        $this->class = $metadata->getName();
    }

    /**
     * {@inheritDoc}
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * {@inheritDoc}
     */
    public function delete(DeviceInterface $device)
    {
        $this->objectManager->remove($device);
        $this->objectManager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function save(DeviceInterface $device, $andFlush = true)
    {
        $this->objectManager->persist($device);
        if ($andFlush) {
            $this->objectManager->flush();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function findDeviceBy(array $criteria)
    {
        return $this->repository->findOneBy($criteria);
    }

    /**
     * 
     * @param ApplicationInterface $application
     * @param Circle $circle
     */
    public function findDeviceInAreaCircle(ApplicationInterface $application, Circle $circle)
    {
        /* @var $qb \Doctrine\ORM\QueryBuilder */
        $qb = $this->repository->createQueryBuilder('d');
        $qb
            ->where($qb->expr()->eq('d.application', ':application'))
            ->andWhere('d.location = ST_Intersection(d.location, ST_Buffer(:center, :radius))')
            ->setParameter('application', $application)
            ->setParameter('center', $circle->getCenter())
            ->setParameter('radius', $circle->getRadius())
        ;

        return $qb->getQuery()->getResult();
    }
}
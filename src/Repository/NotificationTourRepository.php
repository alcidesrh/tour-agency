<?php

namespace App\Repository;

use App\Entity\NotificationTour;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class NotificationTourRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, NotificationTour::class);
    }

    public function getNotificationsAlert($days, $tour, $ls = null)
    {
        $dql = $ls ?'SELECT n FROM App:NotificationTour n LEFT JOIN n.notification n2 WHERE (n.done IS NULL OR n.done = 0) AND n.days >= :days AND n.tour = :tour ORDER BY n2.orderList DESC ':
            'SELECT n FROM App:NotificationTour n LEFT JOIN n.notification n2 WHERE (n.done IS NULL OR n.done = 0) AND n.days >= :days AND n.htcTour = :tour ORDER BY n2.orderList DESC';
        return $this->getEntityManager()
            ->createQuery($dql)->setParameters(['days' => $days, 'tour' => $tour])->getResult();

    }
}

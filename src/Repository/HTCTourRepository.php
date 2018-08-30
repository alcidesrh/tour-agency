<?php

namespace App\Repository;

use App\Entity\HTCTour;
use App\Utils\Util;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Bridge\Doctrine\RegistryInterface;

class HTCTourRepository extends ServiceEntityRepository
{
    public function __construct( RegistryInterface $registry )
    {
        parent::__construct( $registry, HTCTour::class );
    }

    public function findByCode( $value )
    {
        return $this->createQueryBuilder( 'tour' )->select( 'tour' )->where( 'tour.code LIKE :value' )->setParameter( 'value', "%$value%" )->getQuery()->getResult();
    }

    public function list( \DateTime $date = null, $days = 7, $nocanceled = null )
    {
        $date = $date ?? new \DateTime();
        $date->setTime( 0, 0, 0 );

        $query = $this->createQueryBuilder( 'tour' )->select( 'tour' )->where( 'tour.startDate >= :param' )->setParameter( 'param', $date );

        if ( $days ) {
            $date2 = clone $date;
            $date2->add( new \DateInterval( "P" . $days . "D" ) );
            $date2->setTime( 23, 59, 59 );
            $query->andWhere( 'tour.startDate <= :date2' )->setParameter( 'date2', $date2 );
        }
        if($nocanceled)
            $query->andWhere( 'tour.canceled IS NULL OR tour.canceled = 0' );
        return $query->orderBy( 'tour.startDate', 'ASC' )->getQuery()->getResult();
    }

    public function findCodeContact( $value )
    {
        $query = $this->getEntityManager()->createQuery( 'SELECT t.id, t.name, t.code FROM App:HTCTour t WHERE t.code LIKE :value OR t IN 
                        (SELECT IDENTITY(b.htcTour) FROM App:Booking b WHERE b.name LIKE :value)' )->setParameter( 'value', "%$value%" );
        return $query->getResult();
    }

    public function findByIds( $ids )
    {
        $query = $this->getEntityManager()->createQuery( 'SELECT t FROM App:HTCTour t WHERE t.id IN ('.Util::array_string_values($ids).') ORDER BY t.startDate ASC' );
        return $query->getResult();
    }

    public function advanceSearch( \DateTime $dateStart = null, \DateTime $dateEnd = null, $template = null, $notificationsId = [], $done = null, $days )
    {
        $query = $this->createQueryBuilder( 'tour' )->select( 'tour' );
        if ( !$dateStart )
            $dateStart = new \DateTime();
            $query->where( 'tour.startDate >= :dateStart' )->setParameter( 'dateStart', $dateStart );
        if ( $dateEnd )
            $query->andWhere( 'tour.startDate <= :dateEnd' )->setParameter( 'dateEnd', $dateEnd );
        else {
            $date = $dateStart ?? new \DateTime();
            $date2 = clone $date;
            $date2->setTime( 0, 0, 0 );
            $date2 = clone $date;
            $date2->add( new \DateInterval( "P" . $days . "D" ) );
            $date2->setTime( 23, 59, 59 );
            $query->andWhere( 'tour.startDate <= :date2' )->setParameter( 'date2', $date2 );
        }
        if ( $template )
            $query->andWhere( 'tour.template = :template' )->setParameter( 'template', $template );

        $tours = $query->orderBy( 'tour.startDate', 'ASC' )->getQuery()->getResult();

        if ( !empty( $notificationsId ) ) {
            $result = [];
            foreach ( $tours as $tour ) {
                $cont = 0;
                if ( $notifications = $tour->getNotifications() )
                    foreach ( $notifications as $notification ) {
                        if ( in_array( $notification->getNotification()->getId(), $notificationsId ) ) {
                            if ( $done ) {
                                if ( $notification->getDone() )
                                    $cont++;
                            } else if ( !$notification->getDone() )
                                $cont++;
                        }
                    } else return [];
                if ( $cont == count( $notificationsId ) )
                    $result[] = $tour;
            }
            return $result;
        }
        return $tours;
    }

    public function findByDate( \DateTime $date )
    {
        $date2 = clone $date;
        $date2->setTime( 23, 59, 59 );
        return $this->createQueryBuilder( 'tour' )->select( 'tour' )
            ->where( 'tour.startDate >= :dateStart' )->setParameter( 'dateStart', $date )
            ->andWhere( 'tour.startDate <= :dateEnd' )->setParameter( 'dateEnd', $date2 )->orderBy( 'tour.startDate', 'ASC' )->getQuery()->getResult();
    }

    public function findNotificationByCode( $tour, $code )
    {
        return $this->getEntityManager()->createQuery( 'SELECT n FROM App:NotificationTour n 
LEFT JOIN n.htcTour t
LEFT JOIN n.notification n2
WHERE n2.code = :code AND t = :tour' )->setParameter( 'code', $code )->setParameter( 'tour', $tour )->getOneOrNullResult();
    }
}

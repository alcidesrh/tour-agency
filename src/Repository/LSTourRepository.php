<?php

namespace App\Repository;


use App\Entity\LSTour;
use App\Utils\Util;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Symfony\Bridge\Doctrine\RegistryInterface;

class LSTourRepository extends ServiceEntityRepository
{
    public function __construct( RegistryInterface $registry )
    {
        parent::__construct( $registry, LSTour::class );
    }


    public function findByCode( $value )
    {
        return $this->createQueryBuilder( 'tour' )->select( 'tour' )->where( 'tour.code LIKE :value' )->setParameter( 'value', "%$value%" )->getQuery()->getResult();
    }

    public function getTourForShedule()
    {
        $ls = $this->getEntityManager()->createQuery( "SELECT ls FROM App:LSTour ls
        WHERE (ls.canceled IS NULL OR ls.canceled = 0)" )->getResult();
        $htc = $this->getEntityManager()->createQuery( "SELECT htc FROM App:HTCTour htc
        WHERE (htc.canceled IS NULL OR htc.canceled = 0)" )->getResult();
        return array_merge( $ls, $htc );
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
        $query = $this->getEntityManager()->createQuery( 'SELECT t.id, t.name, t.code FROM App:LSTour t WHERE t.code LIKE :value OR t IN 
                        (SELECT IDENTITY(b.tour) FROM App:Booking b WHERE b.name LIKE :value)' )->setParameter( 'value', "%$value%" );
        return $query->getResult();
    }

    public function advanceSearch( \DateTime $dateStart = null, \DateTime $dateEnd = null, $template = null, $notificationsId = [], $done = null, $destinationsId = null, $destinationAccommodation = null, $days )
    {
        $query = $this->createQueryBuilder( 'tour' )->select( 'tour' );

        if ( !$dateStart )
            $dateStart = new \DateTime();
            $query->where( 'tour.startDate >= :dateStart' )->setParameter( 'dateStart', $dateStart );
        if ( $dateEnd )
            $query->andWhere( 'tour.startDate <= :dateEnd' )->setParameter( 'dateEnd', $dateEnd ); else {
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

        if($tours = $query->orderBy( 'tour.startDate', 'ASC' )->getQuery()->getResult()){
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
            if ( !empty( $destinationsId ) ) {
                if ( $destinationAccommodation ){
                    $t = $this->getEntityManager()->createQuery( 'SELECT t FROM App:LSTour t
LEFT JOIN t.bookings b 
LEFT JOIN b.accommodations a
LEFT JOIN a.accommodation a1
LEFT JOIN a.destination d WHERE a1.name IS NOT NULL AND d.id IN (' . Util::array_string_values( $destinationsId ) . ') AND t.id IN (' . Util::array_string_values( $tours ) . ') ORDER BY t.startDate ASC' )->getResult();
                    $t2 = $this->getEntityManager()->createQuery( 'SELECT t FROM App:LSTour t
LEFT JOIN t.bookings b   
LEFT JOIN b.accommodationsExt a2
LEFT JOIN a2.accommodation a22
LEFT JOIN a2.destination d2 WHERE a22.name IS NOT NULL AND d2.id IN (' . Util::array_string_values( $destinationsId ) . ') AND t.id IN (' . Util::array_string_values( $tours ) . ') AND t.id NOT IN (' . Util::array_string_values( !empty($t)?$t:[0] ) . ') ORDER BY t.startDate ASC' )->getResult();
                    return Util::tourOrder(array_merge($t, $t2));
                } else{
                    $t = $this->getEntityManager()->createQuery( 'SELECT t FROM App:LSTour t
LEFT JOIN t.bookings b 
LEFT JOIN b.accommodations a
LEFT JOIN a.accommodation a1
LEFT JOIN a.destination d WHERE a1.name IS NULL AND d.id IN (' . Util::array_string_values( $destinationsId ) . ') AND t.id IN (' . Util::array_string_values( $tours ) . ') ORDER BY t.startDate ASC' )->getResult();
                    $t2 = $this->getEntityManager()->createQuery( 'SELECT t FROM App:LSTour t
LEFT JOIN t.bookings b   
LEFT JOIN b.accommodationsExt a2
LEFT JOIN a2.accommodation a22
LEFT JOIN a2.destination d2 WHERE a22.name IS NULL AND d2.id IN (' . Util::array_string_values( $destinationsId ) . ') AND t.id IN (' . Util::array_string_values( $tours ) . ')AND t.id NOT IN (' . Util::array_string_values( !empty($t)?$t:[0] ) . ') ORDER BY t.startDate ASC' )->getResult();
                    return Util::tourOrder(array_merge($t, $t2));

                }
            }
        }
        return $tours;
    }

    public function findByIds( $ids )
    {
        $query = $this->getEntityManager()->createQuery( 'SELECT t FROM App:LSTour t WHERE t.id IN (' . Util::array_string_values( $ids ) . ') ORDER BY t.startDate ASC' );
        return $query->getResult();
    }

    public function countAccommodationsCompleted( $tour )
    {
        $accommodations = $this->getEntityManager()->createQuery( 'SELECT COUNT(DISTINCT a.id) FROM App:LSTour t 
LEFT JOIN t.destinations d
LEFT JOIN d.destination d2
LEFT JOIN t.bookings b
LEFT JOIN b.accommodations a
LEFT JOIN a.accommodation a2 WHERE t = :tour AND a.destination = d2  AND a.accommodation IS NOT NULL AND a2.name IS NOT NULL AND a2.name != :empty' )->setParameter( 'tour', $tour )->setParameter( 'empty', "" )->getResult();

        $accommodationsExt = $this->getEntityManager()->createQuery( 'SELECT COUNT(DISTINCT a.id) FROM App:LSTour t
LEFT JOIN t.bookings b
LEFT JOIN b.accommodationsExt a
LEFT JOIN a.accommodation a2  WHERE t = :tour  AND a.accommodation IS NOT NULL AND a2.name IS NOT NULL AND a2.name != :empty' )->setParameter( 'tour', $tour )->setParameter( 'empty', "" )->getResult();
        $total = 0;
        if(isset($accommodations[0][1]))
            $total = $accommodations[0][1];
        if(isset($accommodationsExt[0][1]))
            $total += $accommodationsExt[0][1];
        return $total;
    }

    public function countAccommodationsExt( $tour )
    {

        $accommodationsExt = $this->getEntityManager()->createQuery( 'SELECT COUNT(DISTINCT a.id) FROM App:LSTour t
LEFT JOIN t.bookings b
LEFT JOIN b.accommodationsExt a  WHERE t = :tour' )->setParameter( 'tour', $tour )->getResult();

        return isset($accommodationsExt[0][1])?$accommodationsExt[0][1]:0;
    }

    public function findNotificationsToCheck( $tour, $justTransfer = null )
    {
        $dql = !$justTransfer?'SELECT n FROM App:NotificationTour n 
LEFT JOIN n.tour t 
LEFT JOIN n.notification n2 WHERE t = :tour AND (n2.code = \'a\' OR n2.code = \'at\')':
            'SELECT n FROM App:NotificationTour n 
LEFT JOIN n.tour t 
LEFT JOIN n.notification n2 WHERE t = :tour AND n2.code = \'at\'';
        return $this->getEntityManager()->createQuery( $dql )->setParameter( 'tour', $tour )->getResult();
    }

    public function countTransferComplete( $tour )
    {
        return $this->getEntityManager()->createQuery( 'SELECT COUNT(t) FROM App:TransferAirport t 
LEFT JOIN t.bookingIn bi 
LEFT JOIN t.bookingOut bo
 LEFT JOIN bi.tour biTour
 LEFT JOIN bo.tour boTour WHERE (biTour = :tour OR boTour = :tour) AND t.flightData IS NOT NULL AND t.flightData != \'\' AND t.accommodation IS NOT NULL AND t.sent IS NOT NULL AND t.sent !=0' )->setParameter( 'tour', $tour )->getResult();
    }

    public function findNotificationByCode( $tour, $code )
    {
        return $this->getEntityManager()->createQuery( 'SELECT n FROM App:NotificationTour n 
LEFT JOIN n.tour t
LEFT JOIN n.notification n2
WHERE n2.code = :code AND t = :tour' )->setParameter( 'code', $code )->setParameter( 'tour', $tour )->getOneOrNullResult();
    }

}

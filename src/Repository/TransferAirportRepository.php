<?php

namespace App\Repository;


use App\Entity\TransferAirport;
use App\Utils\Util;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class TransferAirportRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TransferAirport::class);
    }

    public function findIds($ids)
    {

        return $this->getEntityManager()->createQuery("SELECT t FROM App:TransferAirport t WHERE t.id IN(".$ids.")")
            ->getResult();
    }

    public function findCodeContact( $value )
    {
        $trasnfers = $this->getEntityManager()->createQuery( 'SELECT t FROM App:TransferAirport t 
LEFT JOIN t.bookingIn b 
LEFT JOIN t.bookingOut b2
LEFT JOIN b.tour bTour
LEFT JOIN b.htcTour bHtcTour
LEFT JOIN b2.tour b2Tour
LEFT JOIN b2.htcTour b2HtcTour
LEFT JOIN t.tour tour WHERE b.name LIKE :value OR b2.name LIKE :value OR tour.code LIKE :value OR bTour.code LIKE :value OR bHtcTour.code LIKE :value OR b2Tour.code LIKE :value OR b2HtcTour.code LIKE :value' )->setParameter( 'value', "%$value%" )->getResult();
        return $trasnfers;
    }

    public function list(\DateTime $date = null, $days = 7)
    {
        $date = $date ?? new \DateTime();
        $date->setTime(0,0,0);

            $date2 = clone $date;
            $date2->add( new \DateInterval( "P" . $days . "D" ) );
            $date2->setTime( 23, 59, 59 );

        $query = $this->createQueryBuilder('transfer')
            ->select('transfer')
            ->where('transfer.date >= :param')->setParameter('param', $date);
        if( $days )
            $query->andWhere('transfer.date <= :date2')->setParameter('date2', $date2);
        return $query->orderBy('transfer.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function advanceSearch( \DateTime $dateStart = null, \DateTime $dateEnd = null, $type = 'all', $notifications = [], $done = null, $company = null, $drive = null, $receptive = null, $days )
    {
        $query = $this->createQueryBuilder( 'transfer' )->select( 'transfer' );

        if ( !$dateStart )
            $dateStart = new \DateTime();

        $query->where( 'transfer.date >= :dateStart' )->setParameter( 'dateStart', $dateStart );
        if ( $dateEnd )
            $query->andWhere( 'transfer.date <= :dateEnd' )->setParameter( 'dateEnd', $dateEnd );
        else {
            $date = $dateStart ?? new \DateTime();
            $date2 = clone $date;
            $date2->setTime( 0, 0, 0 );
            $date2->add( new \DateInterval( "P" . $days . "D" ) );
            $date2->setTime( 23, 59, 59 );
            $query->andWhere( 'transfer.date <= :date2' )->setParameter( 'date2', $date2 );
        }
        if ( $type != 'all'){
            if($type == 'in')
                $query->andWhere( 'transfer.inTranfer = 1' );
            else
                $query->andWhere( 'transfer.inTranfer IS NULL OR transfer.inTranfer = 0' );
        }
        if($company)
            $query->andWhere( 'transfer.company = :company' )->setParameter('company', $company);
        if($drive)
            $query->andWhere( 'transfer.drive = :drive' )->setParameter('drive', $drive);
        if($receptive)
            $query->andWhere( 'transfer.numberPerson >= :min AND transfer.numberPerson <= :max AND (transfer.drive IS NULL OR transfer.drive = 0)' )->setParameter('min', $receptive->getPaxMin())->setParameter('max', $receptive->getPaxMax());



        if ( !empty( $notifications ) ) {
            if(in_array('email', $notifications)){
                if($done)
                    $query->andWhere( 'transfer.sent = 1' );
                else
                    $query->andWhere( 'transfer.sent IS NULL OR transfer.sent = 0' );
            }
            if(in_array('home', $notifications)){
                if($done)
                    $query->andWhere( 'transfer.accommodation IS NOT NULL OR transfer.accommodation != 0' );
                else
                    $query->andWhere( 'transfer.accommodation IS NULL OR transfer.accommodation = 0' );
            }
            if(in_array('flight', $notifications)){
                if($done)
                     $query->andWhere( 'transfer.flightData IS NOT NULL OR transfer.flightData != 0' );
                    else
                     $query->andWhere( "transfer.flightData IS NULL OR transfer.flightData = ''" );

                }
        }
        return $query->orderBy( 'transfer.date', 'ASC' )->getQuery()->getResult();
    }

    public function findByIds( $ids )
    {
        $query = $this->getEntityManager()->createQuery( 'SELECT t FROM App:TransferAirport t WHERE t.id IN ('.Util::array_string_values($ids).') ORDER BY t.date ASC' );
        return $query->getResult();
    }

    public function findTransferNotification()
    {
        return $this->getEntityManager()->createQuery( "SELECT transfer FROM App:TransferAirport transfer  WHERE (transfer.flightData IS NULL OR transfer.flightData = '' OR transfer.drive IS NULL OR transfer.sent IS NULL OR transfer.sent = 0) AND transfer.date IS NOT NULL" )->getResult();

    }

}

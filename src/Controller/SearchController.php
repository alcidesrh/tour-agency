<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use App\Entity\Accommodation;
use App\Entity\AccommodationBooking;
use App\Entity\DestinationTour;
use App\Entity\File;
use App\Entity\Booking;
use App\Entity\LSTour;
use App\Entity\LSTourTemplate;
use App\Entity\NotificationTemplate;
use App\Entity\NotificationTour;
use App\Entity\TransferAirport;
use App\Utils\Util;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api")
 * @author Alcides Rodríguez <alcdesrh@gmail.com>
 */
class SearchController extends AbstractController
{

    /**
     * @Route("/search-code-contact/{htc}", name="search-code-contact")
     */
    public function searhCodeContact( $htc = false ): Response
    {
        $data = Util::decodeBody();

        if ( $htc )
            return new JsonResponse( $this->getDoctrine()->getRepository( 'App:HTCTour' )->findCodeContact( $data[ 'value' ] ) ); else
            return new JsonResponse( $this->getDoctrine()->getRepository( 'App:LSTour' )->findCodeContact( $data[ 'value' ] ) );
    }
    /**
     * @Route("/search-code-contact-transfer", name="search-code-contact-transfer")
     */
    public function searchCodeContactTransfers(): Response
    {
        $data = Util::decodeBody();
        return new JsonResponse( $this->getDoctrine()->getRepository( 'App:TransferAirport' )->findCodeContact( $data[ 'value' ] )  );
    }

    /**
     * @Route("/notifications-status/{ls}", name="notifications-status")
     */
    public function getNotificationStatusItems( $ls = null ): Response
    {

        $result = [];
        $now = new \DateTime();
        $em = $this->getDoctrine()->getManager();
        $repo = $ls ? 'App:LSTour' : 'App:HTCTour';
        if ( $tours = $em->getRepository( $repo )->list( null, null, true ) )
            foreach ( $tours as $tour ) {
                $daysLeft = $tour->getStartDate()->diff( $now )->format( "%a" );
                if ( $notifications = $em->getRepository( 'App:NotificationTour' )->getNotificationsAlert( $daysLeft, $tour, $tour instanceof LSTour ?? null ) )
                    foreach ( $notifications as $notification ) {
                        $id = $notification->getNotification()->getId();
                        if(!isset($result[ $id.'-id' ])){
                            $result[ $id.'-id' ] = [];
                            $result[ $id.'-id' ][ 'cant' ] = null;
                            $result[ $id.'-id' ][ 'icon' ] = null;
                            $result[ $id.'-id' ][ 'order' ] = null;
                            $result[ $id.'-id' ][ 'tours' ] = [];
                        }
                        $result[ $id.'-id' ][ 'cant' ] = ++$result[ $id.'-id' ][ 'cant' ] ?? 1;
                        if ( !isset( $result[ $id.'-id' ][ 'icon' ] ) )
                            $result[ $id.'-id' ][ 'icon' ] = $notification->getNotification()->getIcon()->getName();
                        if ( !isset( $result[ $id.'-id' ][ 'order' ] ) )
                            $result[ $id.'-id' ][ 'order' ] = $notification->getNotification()->getOrderList();
                        $result[ $id.'-id' ][ 'tours' ][] = [ 'id' => $tour->getId(), 'name' => $tour->getName(), 'code' => $tour->getCode() ];
                    }
            }
        return new JsonResponse( Util::notificationOrder($result) );
    }


    /**
     * @Route("/notifications-transfer-status", name="notifications-transfer-status")
     */
    public function getNotificationTransferStatusItems( ): Response
    {
        $result = [];
        $now = new \DateTime();
        $em = $this->getDoctrine()->getManager();
        $notifications = [];
        foreach ( $em->getRepository('App:Notification')->findBy( [], [ 'id' => 'ASC' ] ) as $notification ) {
            if ( in_array( 'transfer', $notification->getTypes() ) ) {
                $notifications[] = $notification;
            }
        }
        if ( $transfers = $em->getRepository( 'App:TransferAirport' )->findTransferNotification() )
            foreach ( $transfers as $transfer ) {
                $daysLeft = $transfer->getDate()->diff( $now )->format( "%a" );
                foreach ( $notifications as $notification ) {
                    if ( $daysLeft > $notification->getDays() )
                        continue;

                    $id = $notification->getId();
                    if(!isset($result[ $id . '-id' ])){
                        $result[ $id . '-id' ] = [];
                        $result[ $id . '-id' ][ 'cant' ] = null;
                        $result[ $id . '-id' ][ 'transfers' ] = [];
                        $result[ $id . '-id' ][ 'icon' ] = null;
                    }
                    switch ( $notification->getCode() ) {
                        case 'fd':
                            {
                                if ( !$transfer->getFlightData() ) {
                                    $result[ $id . '-id' ][ 'icon' ] = $notification->getIcon()->getName();
                                    $result[ $id . '-id' ][ 'cant' ] = ++$result[ $id . '-id' ][ 'cant' ] ?? 1;
                                    $result[ $id . '-id' ][ 'transfers' ][] = [ 'id' => $transfer->getId(), 'name' => $transfer->getInTranfer() ? $transfer->getBookingIn()->getName() : $transfer->getBookingOut()->getName(), 'type' => $transfer->getInTranfer()?'IN':'OUT' ];
                                }
                                break;
                            }
                        case 'ait':
                            {
                                if ( !$transfer->getAccommodation() ) {
                                    $result[ $id . '-id' ][ 'icon' ] = $notification->getIcon()->getName();
                                    $result[ $id . '-id' ][ 'cant' ] = ++$result[ $id . '-id' ][ 'cant' ] ?? 1;
                                    $result[ $id . '-id' ][ 'transfers' ][] = [ 'id' => $transfer->getId(), 'name' => $transfer->getInTranfer() ? $transfer->getBookingIn()->getName() : $transfer->getBookingOut()->getName(), 'type' => $transfer->getInTranfer()?'IN':'OUT' ];
                                }
                                break;
                            }
                        case 'sd':
                            {
                                if ( !$transfer->getSent() ) {
                                    $result[ $id . '-id' ][ 'icon' ] = $notification->getIcon()->getName();
                                    $result[ $id . '-id' ][ 'cant' ] = ++$result[ $id . '-id' ][ 'cant' ] ?? 1;
                                    $result[ $id . '-id' ][ 'transfers' ][] = [ 'id' => $transfer->getId(), 'name' => $transfer->getInTranfer() ? $transfer->getBookingIn()->getName() : $transfer->getBookingOut()->getName(), 'type' => $transfer->getInTranfer()?'IN':'OUT' ];
                                }
                                break;
                            }
                        default:
                            break;

                    }
                }
            }
        return new JsonResponse( $result );
    }

    /**
     * @Route("/advance-search/{htc}", name="advance-search")
     */
    public function advanceSearch( $htc = false ): Response
    {
        $data = Util::decodeBody();

        if ( $htc ) {
            return new JsonResponse( $this->getDoctrine()->getRepository( 'App:HTCTour' )->advanceSearch( isset( $data[ 'dateStart' ] ) ? new \DateTime( $data[ 'dateStart' ] ) : null, isset( $data[ 'dateEnd' ] ) ? new \DateTime( $data[ 'dateEnd' ] ) : null, isset( $data[ 'template' ] ) ? $this->getDoctrine()->getManager()->getRepository( 'App:HTCTourTemplate' )->find( $data[ 'template' ] ) : null, isset( $data[ 'notifications' ] ) ? Util::array_string_values( $data[ 'notifications' ] ) : [], isset( $data[ 'notificationStatus' ] ) ? $data[ 'notificationStatus' ] : false ) );
        }
    }

}

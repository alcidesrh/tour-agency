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
use App\Entity\Booking;
use App\Entity\Extention;
use App\Entity\File;
use App\Entity\HTCTour;
use App\Entity\HTCTourTemplate;
use App\Entity\LSTour;
use App\Entity\LSTourTemplate;
use App\Entity\NotificationTemplate;
use App\Entity\NotificationTour;
use App\Entity\TransferAirport;
use App\Utils\Util;
use Doctrine\ORM\EntityManager;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api")
 * @author Alcides Rodríguez <alcdesrh@gmail.com>
 */
class TourController extends AbstractController
{

    /**
     * @Route("/create-ls-tour-template", name="create_ls_tour_template")
     */
    public function createLsTourTemplate(): Response
    {
        $data = Util::decodeBody();
        if ( is_array( $data ) ) {
            $em = $this->getDoctrine()->getManager();
            if ( isset( $data[ 'id' ] ) ) {
                $tour = $em->getRepository( 'App:LSTourTemplate' )->find( $data[ 'id' ] );
                foreach ( $tour->getNotifications() as $notification )
                    $em->remove( $notification );
                foreach ( $tour->getDestinations() as $destination )
                    $em->remove( $destination );
                $em->flush();

            } else
                $tour = new LSTourTemplate();
            $tour->setName( $data[ 'name' ] );
            $tour->setCode( $data[ 'code' ] );
            $tour->setColor( $data[ 'color' ] );
            $tour->setType( $data[ 'type' ] );
            if ( isset( $data[ 'description' ] ) )
                $tour->setDescription( $data[ 'description' ] );
            $tour->setDays( $data[ 'days' ] );
            $tour->setDay( $data[ 'day' ] );

            $em->persist( $tour );

            if ( isset( $data[ 'notifications' ] ) ) {
                $repo = $em->getRepository( 'App:Notification' );
                foreach ( $data[ 'notifications' ] as $value ) {
                    if ( $notification = $repo->findOneBy( [ 'code' => $value[ 'code' ] ] ) ) {
                        $notificationTemplate = new NotificationTemplate( $notification, $value[ 'days' ] ?? null );
                        $notificationTemplate->setTour( $tour );
                        $em->persist( $notificationTemplate );
                    }
                }
            }

            if ( isset( $data[ 'destinations' ] ) ) {
                $repo = $em->getRepository( 'App:Destination' );
                foreach ( $data[ 'destinations' ] as $value ) {
                    if ( $destination = $repo->find( $value[ 'id' ] ) ) {
                        $destinationTour = new DestinationTour( $destination, $value[ 'days' ] ?? null );
                        $destinationTour->setTourTemplate( $tour );
                        $em->persist( $destinationTour );
                    }
                }
            }

            $em->flush();
        }
        return new JsonResponse( [ 'created' ] );
    }

    /**
     * @Route("/create-htc-tour-template", name="create_htc_tour_template")
     */
    public function createHtcTourTemplate(): Response
    {
        $data = Util::decodeBody();
        if ( is_array( $data ) ) {
            $em = $this->getDoctrine()->getManager();
            if ( isset( $data[ 'id' ] ) ) {
                $tour = $em->getRepository( 'App:HTCTourTemplate' )->find( $data[ 'id' ] );
                $toRemove = array_merge( $tour->getNotifications()->toArray() );

            } else
                $tour = new HTCTourTemplate();
            $tour->setName( $data[ 'name' ] );
            $tour->setColor( $data[ 'color' ] );
            if ( isset( $data[ 'type' ] ) )
                $tour->setType( $data[ 'type' ] );
            if ( isset( $data[ 'description' ] ) )
                $tour->setDescription( $data[ 'description' ] );

            $tour->setCode( $data[ 'code' ] );

            $em->persist( $tour );

            if ( isset( $data[ 'notifications' ] ) ) {
                $repo = $em->getRepository( 'App:Notification' );
                foreach ( $data[ 'notifications' ] as $value ) {
                    $notification = $repo->findOneBy( [ 'code' => $value[ 'code' ] ] );
                    if ( $notification ) {
                        $notificationTemplate = new NotificationTemplate( $notification, $value[ 'days' ] ?? null );
                        $notificationTemplate->setTourTemplate( $tour );
                        $em->persist( $notificationTemplate );
                    }
                }
            }
            $tour->setStartPlace( $data[ 'startPlace' ] );
            $tour->setFinishPlace( $data[ 'finishPlace' ] );
            if ( isset( $data[ 'startTime' ] ) && $data[ 'startTime' ] )
                $tour->setStartTime( new \DateTime( $data[ 'startTime' ] ) );
            if ( isset( $data[ 'endTime' ] ) && $data[ 'endTime' ])
                $tour->setEndTime( new \DateTime( $data[ 'endTime' ] ) );

            if ( isset( $toRemove ) )
                foreach ( $toRemove as $item )
                    $em->remove( $item );

            $em->flush();
        }
        return new JsonResponse( [ 'created' ] );
    }

    /**
     * @Route("/create-ls-tour", name="create_ls_tour")
     */
    public function createLsTour(): Response
    {
        $data = Util::decodeBody();
        if ( is_array( $data ) ) {
            $em = $this->getDoctrine()->getManager();
            $tour = new LSTour();
            $template = $em->getRepository( 'App:LSTourTemplate' )->find( $data[ 'template' ] );
            $tour->setTemplate( $template );

            $tour->setName( $data[ 'name' ] );
            $tour->setColor( $data[ 'color' ] );
            $tour->setType( $data[ 'type' ] );
            if ( isset( $data[ 'startDate' ] ) )
                $tour->setStartDate( new \DateTime( $data[ 'startDate' ] ) );
            if ( isset( $data[ 'endDate' ] ) )
                $tour->setEndDate( new \DateTime( $data[ 'endDate' ] ) );
            if ( isset( $data[ 'description' ] ) )
                $tour->setDescription( $data[ 'description' ] );
            if ( isset( $data[ 'days' ] ) )
                $tour->setDays( $data[ 'days' ] );
            if ( isset( $data[ 'day' ] ) )
                $tour->setDayWeek( $data[ 'day' ] );
            if ( isset( $data[ 'agent' ] ) && $data[ 'agent' ] )
                $tour->setAgent( $em->find( 'App:Agent', $data[ 'agent' ] ) );

            $codeLS = $em->getRepository( 'App:CodeTour' )->findAll()[ 0 ];
            $date = str_replace( '-', '', $tour->getStartDate()->format( "d-m-y" ) );
            $private = ( $data[ 'type' ] == 'private' && $template && $template->getType() == 'group' ) ? '-P' : '';
            $agent = ( $tour->getAgent() ) ? '-A' : '';
            $templateCode = ( $template ) ? $template->getCode() : $em->getRepository( 'App:CodeTour' )->findOneBy( [] )->getName();
            $code = $codeLS . $date . $templateCode . $private . $agent;
            if ( $items = $em->getRepository( 'App:LSTour' )->findByCode( $code ) ) {
                $last = $items[ count( $items ) - 1 ];
                $code .= is_numeric( substr( $last->getCode(), -1 ) ) ? '-' . ( ( (int) substr( $last->getCode(), -1 ) ) + 1 ) : '-' . intval( count( $items ) + 1 );
            }
            $tour->setCode( $code );

            $tour->setOffice(isset($data['office']) ?$em->getRepository('App:TourOffice')->find($data['office']):null);

            $em->persist( $tour );

            if ( isset( $data[ 'attachments' ] ) && is_array( $data[ 'attachments' ] ) ) {
                foreach ( $data[ 'attachments' ] as $attachment ) {
                    if ( isset( $attachment[ 'id' ] ) )
                        continue;
                    if ( $file = $em->getRepository( 'App:File' )->findOneBy( [ 'path' => $attachment[ 'path' ] ] ) ) {
                        $file->setTemporal( false );
                        $file->setTour( $tour );
                        $em->persist( $file );
                    }
                }
            }

            if ( isset( $data[ 'bookings' ] ) ) {
                foreach ( $data[ 'bookings' ] as $booking ) {
                    $transferInAcc = null;
                    $transferOutAcc = null;
                    $newBooking = new Booking();
                    $newBooking->setName( $booking[ 'name' ] );
                    $newBooking->setNumberPerson( $booking[ 'persons' ] );
                    $newBooking->setLp( $booking[ 'lp' ] );
                    if ( isset( $booking[ 'sleepRequirement' ] ) )
                        $newBooking->setSleepingRequirement( $booking[ 'sleepRequirement' ] );

                    if ( isset( $booking[ 'accommodations' ] ) ) {
                        $repo = $em->getRepository( 'App:Destination' );
                        foreach ( $booking[ 'accommodations' ] as $values ) {
                            $acc = new AccommodationBooking();
                            $acc2 = new Accommodation( $values[ 'accommodation' ]);
                            $em->persist( $acc2 );
                            $acc->setAccommodation( $acc2 );
                            $acc->setDays( $values[ 'days' ] );
                            $acc->setOrderNumber( $values[ 'order' ] );
                            $acc->setDestination( $repo->find( $values[ 'destination' ] ) );
                            $acc->setDateIn( new \DateTime( $values[ 'dateIn' ] ) );
                            if ( isset( $values[ 'before' ] ) ){
                                if($values[ 'before' ]){
                                    if(!$transferInAcc){
                                        $transferInAcc = $acc;
                                    }
                                    else{
                                        if($transferInAcc->getDateIn() > $acc->getDateIn())
                                            $transferInAcc = $acc;
                                    }

                                }else{
                                    if(!$transferOutAcc){
                                        $transferOutAcc = $acc;
                                    }
                                    else{
                                        if($transferOutAcc->getDateIn() < $acc->getDateIn())
                                            $transferOutAcc = $acc;
                                    }
                                }
                                $acc->setBookingExt( $newBooking );
                            }
                                 else
                                $acc->setBooking( $newBooking );
                            $em->persist( $acc );
                        }
                    }

                    $airportTransfer = new TransferAirport();
                    if ( $company = $em->getRepository( 'App:Company' )->findOneBy( [ 'companyLS' => $codeLS ] ) )
                        $airportTransfer->setCompany( $company );
                    $airportTransfer->setInTranfer( true );
                    if($transferInAcc){
                        $airportTransfer->setAccommodation( $transferInAcc );
                    }
                    else if ( isset( $booking[ 'accommodationIn' ] ) ) {
                        $acc = new Accommodation( $booking[ 'accommodationIn' ] );
                        $em->persist( $acc );
                        $accBooking = new AccommodationBooking();
                        $accBooking->setAccommodation( $acc );
                        $em->persist( $accBooking );
                        $airportTransfer->setAccommodation( $accBooking );
                    }
                    if ( isset( $booking[ 'flyData' ] ) )
                        $airportTransfer->setFlightData( $booking[ 'flyData' ] );
                    if ( isset( $booking[ 'startDate' ] ) ) {
                        $date = $booking[ 'startDate' ];
                        if ( isset( $booking[ 'inTime' ] ) )
                            $date .= ' ' . $booking[ 'inTime' ];
                        $airportTransfer->setDate( new \DateTime( $date ) );
                    }
                    if ( isset( $booking[ 'driverIn' ] ) )
                        $airportTransfer->setDrive( $em->getRepository( 'App:Driver' )->find( $booking[ 'driverIn' ] ) );
                    $airportTransfer->setNumberPerson( $newBooking->getNumberPerson() );
                    $em->persist( $airportTransfer );
                    $newBooking->setAirPortTransferIn( $airportTransfer );

                    $airportTransfer = new TransferAirport();
                    if ( $company = $em->getRepository( 'App:Company' )->findOneBy( [ 'companyLS' => $codeLS ] ) )
                        $airportTransfer->setCompany( $company );
                    $airportTransfer->setInTranfer( false );
                    if($transferOutAcc){
                        $airportTransfer->setAccommodation( $transferOutAcc );
                    }
                    else if ( isset( $booking[ 'accommodationOut' ] ) ) {
                        $acc = new Accommodation( $booking[ 'accommodationOut' ] );
                        $em->persist( $acc );
                        $accBooking = new AccommodationBooking();
                        $accBooking->setAccommodation( $acc );
                        $em->persist( $accBooking );
                        $airportTransfer->setAccommodation( $accBooking );
                    }
                    if ( isset( $booking[ 'flyDataOut' ] ) )
                        $airportTransfer->setFlightData( $booking[ 'flyDataOut' ] );
                    if ( isset( $booking[ 'endDate' ] ) ) {
                        $date = $booking[ 'endDate' ];
                        if ( isset( $booking[ 'outTime' ] ) )
                            $date .= ' ' . $booking[ 'outTime' ];
                        $airportTransfer->setDate( new \DateTime( $date ) );
                    }

                    if ( isset( $booking[ 'driverOut' ] ) )
                        $airportTransfer->setDrive( $em->getRepository( 'App:Driver' )->find( $booking[ 'driverOut' ] ) );
                    $airportTransfer->setNumberPerson( $newBooking->getNumberPerson() );
                    $em->persist( $airportTransfer );
                    $newBooking->setAirPortTransferOut( $airportTransfer );

                    if ( isset( $booking[ 'extentions' ] ) ) {
                        foreach ( $booking[ 'extentions' ] as $value ) {
                            if ( isset( $value[ 'id' ] ) ) {
                                $extentions[] = $value[ 'id' ];
                                $extention = $em->getRepository( 'App:Extention' )->find( $value[ 'id' ] );
                            } else
                                $extention = new Extention();
                            $extention->setType( $value[ 'type' ] );
                            $extention->setBooking( $newBooking );
                            $extention->setDate( new \DateTime( $value[ 'date' ] ) );
                            if ( isset( $value[ 'description' ] ) )
                                $extention->setDescription( $value[ 'description' ] ); else {
                                if ( $tour2 = $em->getRepository( 'App:HTCTour' )->findOneBy( [ 'code' => $value[ 'tour' ] ] ) )
                                    $extention->setHtcTour( $tour2 ); else $extention->setTour( $em->getRepository( 'App:LSTour' )->findOneBy( [ 'code' => $value[ 'tour' ] ] ) );
                            }
                            $em->persist( $extention );
                        }
                    }

                    $newBooking->setCanceled( $booking[ 'canceled' ] ?? null );
                    $newBooking->setTour( $tour );
                    $em->persist( $newBooking );
                }
            }

            if ( isset( $data[ 'destinations' ] ) ) {
                $repo = $em->getRepository( 'App:Destination' );
                foreach ( $data[ 'destinations' ] as $value ) {
                    $destinationTour = new DestinationTour( $repo->find( $value[ 'destination' ] ), $value[ 'days' ] ?? null );
                    $destinationTour->setTour( $tour );
                    $em->persist( $destinationTour );
                }
            }

            if ( isset( $data[ 'notifications' ] ) ) {
                $repo = $em->getRepository( 'App:Notification' );
                foreach ( $data[ 'notifications' ] as $value ) {
                    $notificationTour = new NotificationTour( $repo->find( $value[ 'notification' ] ), $value[ 'days' ] ?? null );
                    $notificationTour->setTour( $tour );
                    if ( isset( $value[ 'done' ] ) )
                        $notificationTour->setDone( $value[ 'done' ] );
                    $em->persist( $notificationTour );
                }
            }
            $em->flush();
            $em->refresh( $tour );
            self::checkTourNotifications( $tour, $em );
        }
        return new JsonResponse( [ 'id' => $tour->getId()] );
    }

    /**
     * @Route("/edit-ls-tour", name="edit_ls_tour")
     */
    public function editLsTour(): Response
    {
        $data = Util::decodeBody();
        if ( is_array( $data ) ) {
            $em = $this->getDoctrine()->getManager();
            $tour = $em->getRepository( 'App:LSTour' )->find( $data[ 'id' ] );
            $toRemove = [];
            $template = $tour->getTemplate();

            if(!isset($data['ext'])){
                $codeLS = $em->getRepository( 'App:CodeTour' )->findAll()[ 0 ];
                $date = new \DateTime( $data[ 'startDate' ] );
                $date = str_replace( '-', '', $date->format( "d-m-y" ) );
                $private = ( $data[ 'type' ] == 'private' && $template && $template->getType() == 'group' ) ? '-P' : '';
                $agent = ( $tour->getAgent() ) ? '-A' : '';
                $templateCode = ( $template ) ? $template->getCode() : $em->getRepository( 'App:CodeTour' )->findOneBy( [] )->getName();
                $code = $codeLS . $date . $templateCode . $private . $agent;
                if(strpos($tour->getCode(), 'Ext') === 0)
                    $code = "Ext-$code";
                if ( $items = $em->getRepository( 'App:LSTour' )->findByCode( $code ) ) {
                    $last = $items[ count( $items ) - 1 ];
                    if ( $tour->getCode() != $code && strpos( $tour->getCode(), $code ) === false ) {
                        $code .= is_numeric( substr( $last->getCode(), -1 ) ) ? '-' . ( ( (int) substr( $last->getCode(), -1 ) ) + 1 ) : '-' . intval( count( $items ) + 1 );
                        $tour->setCode( $code );
                    }
                } else if ( $tour->getCode() != $code )
                    $tour->setCode( $code );
            }


            $tour->setName( $data[ 'name' ] );
            $tour->setColor( $data[ 'color' ] );
            $tour->setType( $data[ 'type' ] );
            if ( isset( $data[ 'startDate' ] ) ) {
                $date = new \DateTime( $data[ 'startDate' ] );
                if ( $date != $tour->getStartDate() ) {
                    $tour->setStartDate( $date );
                    $tour->setGuide( null );
                }
            }
            if ( isset( $data[ 'endDate' ] ) )
                $tour->setEndDate( new \DateTime( $data[ 'endDate' ] ) );
            if ( isset( $data[ 'description' ] ) )
                $tour->setDescription( $data[ 'description' ] );
            if ( isset( $data[ 'days' ] ) )
                $tour->setDays( $data[ 'days' ] );
            if ( isset( $data[ 'day' ] ) )
                $tour->setDayWeek( $data[ 'day' ] );

            if(isset($data['office'])){
                if(is_array($data['office']) && isset($data['office']['id'])){
                    $office = $data['office']['id'];
                }
                else
                    $office = $data['office'];
                $tour->setOffice($em->getRepository('App:TourOffice')->find($office));
            }

            $em->persist( $tour );

            if ( isset( $data[ 'attachments' ] ) && is_array( $data[ 'attachments' ] ) ) {
                foreach ( $data[ 'attachments' ] as $attachment ) {
                    if ( isset( $attachment[ 'id' ] ) )
                        continue;
                    if ( $file = $em->getRepository( 'App:File' )->findOneBy( [ 'path' => $attachment[ 'path' ] ] ) ) {
                        $file->setTemporal( false );
                        $file->setTour( $tour );
                        $em->persist( $file );
                    }
                }
            }

            if ( isset( $data[ 'bookings' ] ) ) {
                $bookingsIds = Util::filter_by_ids( $tour->getBookings()->toArray() ?? [] );
                $bookings = [];
                $company = $em->getRepository( 'App:Company' )->findOneBy( [ 'companyLS' => $codeLS ] );
                foreach ( $data[ 'bookings' ] as $booking ) {
                    $transferInAcc = null;
                    $transferOutAcc = null;
                    if ( isset( $booking[ 'id' ] ) ) {
                        $bookings[] = $booking[ 'id' ];
                        $newBooking = $em->getRepository( 'App:Booking' )->find( $booking[ 'id' ] );
                        $airportTransfer = $newBooking->getAirPortTransferIn();
                        $airportTransferOut = $newBooking->getAirPortTransferOut();
                    } else {
                        $newBooking = new Booking();
                        $airportTransfer = new TransferAirport();
                        $airportTransferOut = new TransferAirport();
                    }
                    $newBooking->setName( $booking[ 'name' ] );
                    $newBooking->setNumberPerson( $booking[ 'persons' ] );
                    $newBooking->setLp( $booking[ 'lp' ] );
                    $newBooking->setSleepingRequirement( $booking[ 'sleepRequirement' ] ?? "" );


                    $accommodationsIds = Util::filter_by_ids( array_merge( $newBooking->getAccommodations()->toArray() ?? [], $newBooking->getAccommodationExt()->toArray() ?? [] ) );
                    $accommodations = [];
                    if ( isset( $booking[ 'accommodations' ] ) ) {
                        $repo = $em->getRepository( 'App:Destination' );
                        foreach ( $booking[ 'accommodations' ] as $value ) {
                            if ( isset( $value[ 'id' ] ) ) {
                                $accommodations[] = $value[ 'id' ];
                                $acc = $em->getRepository( 'App:AccommodationBooking' )->find( $value[ 'id' ] );
                                $acc2 = $acc->getAccommodation();
                                    $acc2 = $acc2 ?? new Accommodation();
                                    $acc2->setName( $value[ 'accommodation' ] ?? null);
                                    $acc->setAccommodation( $acc2 );
                                    $em->persist( $acc2 );

                            } else {
                                $acc = new AccommodationBooking();
                                    $acc2 = new Accommodation();
                                    $acc->setAccommodation( $acc2 );
                                    $acc2->setName( $value[ 'accommodation' ] != ""? $value[ 'accommodation' ]: null);
                                    $em->persist( $acc2 );
                            }


                            $acc->setDays( $value[ 'days' ] );
                            $acc->setOrderNumber( $value[ 'order' ] ?? null );
                            $acc->setDestination( $repo->find( $value[ 'destination' ] ) );
                            $acc->setDateIn( new \DateTime( $value[ 'dateIn' ] ) );;
                            if ( isset( $value[ 'before' ] ) ){
                                if( $value[ 'before' ] ){
                                    if(!$transferInAcc){
                                        $transferInAcc = $acc;
                                    }
                                    else{
                                        if($transferInAcc->getDateIn() > $acc->getDateIn())
                                            $transferInAcc = $acc;
                                    }

                                }else{
                                    if(!$transferOutAcc){
                                        $transferOutAcc = $acc;
                                    }
                                    else{
                                        if($transferOutAcc->getDateIn() < $acc->getDateIn())
                                            $transferOutAcc = $acc;
                                    }
                                }
                                $acc->setBookingExt( $newBooking );
                            } else
                                $acc->setBooking( $newBooking );
                            $em->persist( $acc );
                        }
                    }
                    foreach ( $accommodationsIds as $accommodationId ) {
                        if ( !in_array( $accommodationId, $accommodations ) ) {
                            $toRemove[] = $em->getRepository( 'App:AccommodationBooking' )->find( $accommodationId );
                        }
                    }

                    if($airportTransfer){
                        $airportTransfer->setCompany( $company );
                        $airportTransfer->setInTranfer( true );
                        if($transferInAcc){
                            $airportTransfer->setAccommodation( $transferInAcc );
                        }
                        else if ( isset( $booking[ 'accommodationIn' ] ) ) {
                            if ( !( $acc = $airportTransfer->getAccommodation() ) ) {
                                if ( $booking[ 'accommodationIn' ] != "" ) {
                                    $acc = new AccommodationBooking();
                                    $acc2 = new Accommodation( $booking[ 'accommodationIn' ] );
                                    $em->persist( $acc2 );
                                    $acc->setAccommodation( $acc2 );
                                    $airportTransfer->setAccommodation( $acc );
                                    $em->persist( $acc );
                                }
                            } else if ( !( $acc = $airportTransfer->getAccommodation()->getAccommodation() ) ) {
                                if ( $booking[ 'accommodationIn' ] != "" ) {
                                    $acc2 = new Accommodation( $booking[ 'accommodationIn' ] );
                                    $em->persist( $acc2 );
                                    $acc->setAccommodation( $acc2 );
                                    $airportTransfer->setAccommodation( $acc );
                                    $em->persist( $acc );
                                } else {
                                    $em->remove( $airportTransfer->getAccommodation() );
                                    $em->flush();
                                }
                            } else {
                                $acc = $airportTransfer->getAccommodation()->getAccommodation();
                                if ( $booking[ 'accommodationIn' ] == "" ) {
                                    $em->remove( $airportTransfer->getAccommodation() );
                                    $em->flush();
                                } else {
                                    $acc->setName( $booking[ 'accommodationIn' ] );
                                    $em->persist( $acc );
                                }

                            }
                        }
                        $airportTransfer->setFlightData( $booking[ 'flyData' ] ?? null );
                        if ( isset( $booking[ 'startDate' ] ) ) {
                            $date = $booking[ 'startDate' ];
                            if ( isset( $booking[ 'inTime' ] ) )
                                $date .= ' ' . $booking[ 'inTime' ];
                            $airportTransfer->setDate( new \DateTime( $date ) );
                        } else $airportTransfer->setDate( null );
                        if ( isset( $booking[ 'driverIn' ] ) )
                            $airportTransfer->setDrive( $em->getRepository( 'App:Driver' )->find( $booking[ 'driverIn' ] ) );
                        $airportTransfer->setNumberPerson( $newBooking->getNumberPerson() );
                        $em->persist( $airportTransfer );
                        $newBooking->setAirPortTransferIn( $airportTransfer );
                    }

                    if($airportTransferOut){
                        $airportTransferOut->setCompany( $company );
                        $airportTransferOut->setInTranfer( false );
                        if($transferOutAcc){
                            $airportTransferOut->setAccommodation( $transferOutAcc );
                        }
                        else if ( isset( $booking[ 'accommodationOut' ] ) ) {
                            if ( !( $acc = $airportTransferOut->getAccommodation() ) ) {
                                if ( $booking[ 'accommodationOut' ] != "" ) {
                                    $acc = new AccommodationBooking();
                                    $acc2 = new Accommodation( $booking[ 'accommodationOut' ] );
                                    $em->persist( $acc2 );
                                    $acc->setAccommodation( $acc2 );
                                    $airportTransferOut->setAccommodation( $acc );
                                    $em->persist( $acc );
                                }
                            } else if ( !( $acc = $airportTransferOut->getAccommodation()->getAccommodation() ) ) {
                                if ( $booking[ 'accommodationOut' ] != "" ) {
                                    $acc2 = new Accommodation( $booking[ 'accommodationOut' ] );
                                    $em->persist( $acc2 );
                                    $acc->setAccommodation( $acc2 );
                                    $airportTransferOut->setAccommodation( $acc );
                                    $em->persist( $acc );
                                } else {
                                    $em->remove( $airportTransferOut->getAccommodation() );
                                    $em->flush();
                                }
                            } else {
                                $acc = $airportTransferOut->getAccommodation()->getAccommodation();
                                if ( $booking[ 'accommodationOut' ] == "" ) {
                                    $em->remove( $airportTransferOut->getAccommodation() );
                                    $em->flush();
                                } else {
                                    $acc->setName( $booking[ 'accommodationOut' ] );
                                    $em->persist( $acc );
                                }
                            }


                        }
                        $airportTransferOut->setFlightData( $booking[ 'flyDataOut' ] ?? null );
                        if ( isset( $booking[ 'endDate' ] ) ) {
                            $date = $booking[ 'endDate' ];
                            if ( isset( $booking[ 'outTime' ] ) )
                                $date .= ' ' . $booking[ 'outTime' ];
                            $airportTransferOut->setDate( new \DateTime( $date ) );
                        } else $airportTransferOut->setDate( null );

                        if ( isset( $booking[ 'driverOut' ] ) )
                            $airportTransferOut->setDrive( $em->getRepository( 'App:Driver' )->find( $booking[ 'driverOut' ] ) );
                        $airportTransferOut->setNumberPerson( $newBooking->getNumberPerson() );
                        $em->persist( $airportTransferOut );
                        $newBooking->setAirPortTransferOut( $airportTransferOut );
                    }



                    $extentionIds = Util::filter_by_ids( $newBooking->getExtentions()->toArray() ?? [] );
                    $extentions = [];
                    if ( isset( $booking[ 'extentions' ] ) ) {
                        foreach ( $booking[ 'extentions' ] as $value ) {
                            if ( isset( $value[ 'id' ] ) ) {
                                $extentions[] = $value[ 'id' ];
                                $extention = $em->getRepository( 'App:Extention' )->find( $value[ 'id' ] );
                            } else
                                $extention = new Extention();
                            $extention->setType( $value[ 'type' ] );
                            $extention->setBooking( $newBooking );

                            $extention->setDate( new \DateTime( $value[ 'date' ] ) );
                            if ( isset( $value[ 'description' ] ) )
                                $extention->setDescription( $value[ 'description' ] ); else {
                                $b = new Booking($newBooking);
                                if ( $tour2 = $em->getRepository( 'App:HTCTour' )->findOneBy( [ 'code' => $value[ 'tour' ] ] ) ){
                                    $extention->setHtcTour( $tour2 );
                                    $b->setHtcTour($tour2);
                                }
                                else if($tour2 = $em->getRepository( 'App:LSTour' )->findOneBy( [ 'code' => $value[ 'tour' ] ] )){
                                    $extention->setTour( $tour2 );
                                    $b->setTour($tour2);
                                }
                               if(!isset($value['id'])){
                                   $tour2->addBooking($b);
                                   $em->persist($b);
                                   $em->persist($tour2);
                               }
                            }
                            $em->persist( $extention );
                        }
                    }
                    foreach ( $extentionIds as $extention ) {
                        if ( !in_array( $extention, $extentions ) ) {
                            $toRemove[] = $em->getRepository( 'App:Extention' )->find( $extention );
                        }
                    }

                    $newBooking->setCanceled( $booking[ 'canceled' ] ?? null );
                    $newBooking->setTour( $tour );
                    $em->persist( $newBooking );
                }

                foreach ( $bookingsIds as $booking ) {
                    if ( !in_array( $booking, $bookings ) ) {
                        $toRemove[] = $em->getRepository( 'App:Booking' )->find( $booking );
                    }
                }
            }


            $destinationsIds = Util::filter_by_ids( $tour->getDestinations()->toArray() ?? [] );
            $destinations = [];
            if ( isset( $data[ 'destinations' ] ) ) {
                $repo = $em->getRepository( 'App:Destination' );
                foreach ( $data[ 'destinations' ] as $value ) {
                    if ( !isset( $value[ 'id' ] ) ) {
                        $destinationTour = new DestinationTour( $repo->find( $value[ 'destination' ] ), $value[ 'days' ] );
                        $destinationTour->setTour( $tour );
                    } else {
                        $destinations[] = $value[ 'id' ];
                        $destinationTour = $em->getRepository( 'App:DestinationTour' )->find( $value[ 'id' ] );
                        $destinationTour->setDays( $value[ 'days' ] );
                    }
                    $em->persist( $destinationTour );
                }
            }
            foreach ( $destinationsIds as $destinationsId ) {
                if ( !in_array( $destinationsId, $destinations ) ) {
                    $toRemove[] = $em->getRepository( 'App:DestinationTour' )->find( $destinationsId );
                }
            }

            $notificationsIds = Util::filter_by_ids( $tour->getNotifications()->toArray() ?? [] );
            $notifications = [];
            if ( isset( $data[ 'notifications' ] ) ) {
                $repo = $em->getRepository( 'App:Notification' );
                foreach ( $data[ 'notifications' ] as $value ) {
                    if ( !in_array( $value[ 'id' ], $notificationsIds ) ) {
                        $notificationTour = new NotificationTour( $repo->find( $value[ 'notification' ] ), $value[ 'days' ] ?? null );
                        $notificationTour->setTour( $tour );
                    } else {
                        $notifications[] = $value[ 'id' ];
                        $notificationTour = $em->getRepository( 'App:NotificationTour' )->find( $value[ 'id' ] );
                        $notificationTour->setDays( $value[ 'days' ] );
                    }
                    $notificationTour->setDone( $value[ 'done' ] ?? null );
                    $em->persist( $notificationTour );
                }
            }
            foreach ( $notificationsIds as $notificationsId ) {
                if ( !in_array( $notificationsId, $notifications ) ) {
                    $toRemove[] = $em->getRepository( 'App:NotificationTour' )->find( $notificationsId );
                }
            }

            if ( isset( $toRemove ) )
                foreach ( $toRemove as $item )
                    $em->remove( $item );
            $em->flush();
            $em->refresh($tour);

            self::checkTourNotifications( $tour, $em );
        }
        return new JsonResponse( [ 'updated' ] );
    }

    /**
     * @Route("/create-ext-tour", name="create_ext_tour")
     */
    public function createExtTour(): Response
    {
        $data = Util::decodeBody();

        if ( is_array( $data ) ) {
            $em = $this->getDoctrine()->getManager();
            $booking = $em->find('App:Booking', $data['booking']);

            $extention = new Extention();
            $extention->setType( 'extra_tour' );
            $extention->setBooking( $booking );
            $extention->setDate(new \DateTime( $data[ 'startDate' ] ) );

            $newBooking = new Booking();
            $newBooking->setName( $booking->getName() );
            $newBooking->setNumberPerson( $booking->getNumberPerson() );
            $newBooking->setLp( $booking->getLp() );
            $newBooking->setSleepingRequirement( $booking->getSleepingRequirement() );

            if($data['tour_type'] == 'ls'){
                $fromTour = $em->find('App:LSTour', $data['tour']);
                $tour = new LSTour();
                $extention->setTour($tour);
                $newBooking->setTour( $tour );
                $tour->setName('Ext-'.$fromTour->getCode());
                $tour->setCode('Ext-'.$fromTour->getCode());
            }
            else{
                $fromTour = $em->find('App:HTCTour', $data['tour']);
                $tour = new HTCTour();
                $extention->setHtcTour($tour);
                $newBooking->setHtcTour( $tour );
            }
            $em->persist( $extention );
            $em->persist( $newBooking );
            $tour->setColor( $fromTour->getColor() );
            $tour->setType( 'tailor_made' );
            if ( isset( $data[ 'startDate' ] ) )
                $tour->setStartDate( new \DateTime( $data[ 'startDate' ] ) );
            if ( isset( $data[ 'endDate' ] ) )
                $tour->setEndDate( new \DateTime( $data[ 'endDate' ] ) );

//            $codeLS = $em->getRepository( 'App:CodeTour' )->findAll()[ 0 ];
//            $date = str_replace( '-', '', $tour->getStartDate()->format( "d-m-y" ) );
//            $code = 'Ext-' . $codeLS . $date;
//            if ( $items = $em->getRepository( 'App:LSTour' )->findByCode( $code ) ) {
//                $last = $items[ count( $items ) - 1 ];
//                $code .= is_numeric( substr( $last->getCode(), -1 ) ) ? '-' . ( ( (int) substr( $last->getCode(), -1 ) ) + 1 ) : '-' . intval( count( $items ) + 1 );
//            }
//            $tour->setCode( $code );

            $em->persist( $tour );

            $em->flush();
            $em->refresh( $tour );
            return new JsonResponse( [ 'id' => $tour->getId() ] );
        }
        return new JsonResponse( [ 'created' ] );
    }

    /**
     * @Route("/create-htc-tour", name="create_htc_tour")
     */
    public function createHtcTour(): Response
    {
        $data = Util::decodeBody();
        if ( is_array( $data ) ) {
            $em = $this->getDoctrine()->getManager();
            $tour = new HTCTour();
            $template = $em->getRepository( 'App:HTCTourTemplate' )->find( $data[ 'template' ] );
            $tour->setTemplate( $template );

            $tour->setName( $data[ 'name' ] );
            $tour->setColor( $data[ 'color' ] );
            $tour->setType( $data[ 'type' ] );
            $tour->setStartPlace( $data[ 'startPlace' ] );
            $tour->setFinishPlace( $data[ 'finishPlace' ] );
            $tour->setStartTime( new \DateTime( $data[ 'startTime' ] ) );
            $tour->setEndTime( new \DateTime( $data[ 'endTime' ] ) );
            if ( isset( $data[ 'startDate' ] ) )
                $tour->setStartDate( new \DateTime( $data[ 'startDate' ] . ' ' . $data[ 'startTime' ] ) );
            if ( isset( $data[ 'endDate' ] ) )
                $tour->setEndDate( new \DateTime( $data[ 'endDate' ] . ' ' . $data[ 'endTime' ] ) );
            if ( isset( $data[ 'description' ] ) )
                $tour->setDescription( $data[ 'description' ] );
            if ( isset( $data[ 'agent' ] ) && $data[ 'agent' ] )
                $tour->setAgent( $em->find( 'App:Agent', $data[ 'agent' ] ) );

            $codeHTC = $em->getRepository( 'App:CodeTourOneDay' )->findAll()[ 0 ];
            $date = str_replace( '-', '', $tour->getStartDate()->format( "d-m-y" ) );
            $private = ( $data[ 'type' ] == 'private' && $template && $template->getType() == 'group' ) ? '-P' : '';
            $agent = ( $tour->getAgent() ) ? '-A' : '';
            $templateCode = ( $template ) ? $template->getCode() : $em->getRepository( 'App:CodeTourOneDay' )->findOneBy( [] )->getName();
            $code = $codeHTC . $date . $templateCode . $private . $agent;
            if ( $items = $em->getRepository( 'App:HTCTour' )->findByCode( $code ) ) {
                $last = $items[ count( $items ) - 1 ];
                $code .= is_numeric( substr( $last->getCode(), -1 ) ) ? '-' . ( ( (int) substr( $last->getCode(), -1 ) ) + 1 ) : '-' . intval( count( $items ) + 1 );

            }
            $tour->setCode( $code );

            $tour->setOffice(isset($data['office']) ?$em->getRepository('App:TourOffice')->find($data['office']):null);

            $em->persist( $tour );

            if ( isset( $data[ 'attachments' ] ) && is_array( $data[ 'attachments' ] ) ) {
                foreach ( $data[ 'attachments' ] as $attachment ) {
                    if ( isset( $attachment[ 'id' ] ) )
                        continue;
                    if ( $file = $em->getRepository( 'App:File' )->findOneBy( [ 'path' => $attachment[ 'path' ] ] ) ) {
                        $file->setTemporal( false );
                        $file->setTourHtc( $tour );
                        $em->persist( $file );
                    }
                }
            }

            if ( isset( $data[ 'bookings' ] ) ) {
                foreach ( $data[ 'bookings' ] as $booking ) {
                    $newBooking = new Booking();
                    $newBooking->setName( $booking[ 'name' ] );
                    $newBooking->setNumberPerson( $booking[ 'persons' ] );
                    $newBooking->setLp( $booking[ 'lp' ] );
                    $newBooking->setHtcTour( $tour );
                    $newBooking->setCanceled( $booking[ 'canceled' ] ?? null );
                    $em->persist( $newBooking );
                }
            }

            if ( isset( $data[ 'notifications' ] ) ) {
                $repo = $em->getRepository( 'App:Notification' );
                foreach ( $data[ 'notifications' ] as $value ) {
                    $notificationTour = new NotificationTour( $repo->find( $value[ 'notification' ] ), $value[ 'days' ] ?? null );
                    $notificationTour->setHtcTour( $tour );
                    if ( isset( $value[ 'done' ] ) )
                        $notificationTour->setDone( $value[ 'done' ] );
                    $em->persist( $notificationTour );
                }
            }
            $em->flush();
        }
        return new JsonResponse( [ 'created' ] );
    }

    /**
     * @Route("/edit-htc-tour", name="edit_htc_tour")
     */
    public function editHtcTour(): Response
    {
        $data = Util::decodeBody();
        if ( is_array( $data ) ) {
            $em = $this->getDoctrine()->getManager();
            $tour = $em->getRepository( 'App:HTCTour' )->find( $data[ 'id' ] );
            $toRemove = [];
            $template = $tour->getTemplate();

            $tour->setName( $data[ 'name' ] );
            $tour->setColor( $data[ 'color' ] );
            $tour->setType( $data[ 'type' ] );
            $tour->setStartPlace( $data[ 'startPlace' ] );
            $tour->setFinishPlace( $data[ 'finishPlace' ] );
            $tour->setStartTime( new \DateTime( $data[ 'startTime' ] ) );
            $tour->setEndTime( new \DateTime( $data[ 'endTime' ] ) );
            if ( isset( $data[ 'startDate' ] ) ) {
                $date = new \DateTime( $data[ 'startDate' ] . ' ' . $data[ 'startTime' ] );
                if ( $date != $tour->getStartDate() ) {
                    $tour->setStartDate( $date );
                }
            }
            if ( isset( $data[ 'endDate' ] ) )
                $tour->setEndDate( new \DateTime( $data[ 'endDate' ] . ' ' . $data[ 'endTime' ] ) );
            if ( isset( $data[ 'description' ] ) )
                $tour->setDescription( $data[ 'description' ] );

            $codeHTC = $em->getRepository( 'App:CodeTourOneDay' )->findAll()[ 0 ];
            $date = str_replace( '-', '', $tour->getStartDate()->format( "d-m-y" ) );
            $private = ( $data[ 'type' ] == 'private' && $template && $template->getType() == 'group' ) ? '-P' : '';
            $agent = ( $tour->getAgent() ) ? '-A' : '';
            $templateCode = ( $template ) ? $template->getCode() : $em->getRepository( 'App:CodeTourOneDay' )->findOneBy( [] )->getName();
            $code = $codeHTC . $date . $templateCode . $private . $agent;
            if ( $items = $em->getRepository( 'App:HTCTour' )->findByCode( $code ) ) {
                $last = $items[ count( $items ) - 1 ];
                if ( $tour->getCode() != $code && strpos( $tour->getCode(), $code ) === false ) {
                    $code .= is_numeric( substr( $last->getCode(), -1 ) ) ? '-' . ( ( (int) substr( $last->getCode(), -1 ) ) + 1 ) : '-' . intval( count( $items ) + 1 );
                    $tour->setCode( $code );
                }
            } else if ( $tour->getCode() != $code )
                $tour->setCode( $code );

            if(isset($data['office'])){
                if(is_array($data['office']) && isset($data['office']['id'])){
                    $office = $data['office']['id'];
                }
                else
                    $office = $data['office'];
                $tour->setOffice($em->getRepository('App:TourOffice')->find($office));
            }

            $em->persist( $tour );

            if ( isset( $data[ 'attachments' ] ) && is_array( $data[ 'attachments' ] ) ) {
                foreach ( $data[ 'attachments' ] as $attachment ) {
                    if ( isset( $attachment[ 'id' ] ) )
                        continue;
                    if ( $file = $em->getRepository( 'App:File' )->findOneBy( [ 'path' => $attachment[ 'path' ] ] ) ) {
                        $file->setTemporal( false );
                        $file->setTourHtc( $tour );
                        $em->persist( $file );
                    }
                }
            }
            $bookingsIds = Util::filter_by_ids( $tour->getBookings()->toArray() ?? [] );
            $bookings = [];
            if ( isset( $data[ 'bookings' ] ) ) {
                foreach ( $data[ 'bookings' ] as $booking ) {
                    if ( isset( $booking[ 'id' ] ) ) {
                        $bookings[] = $booking[ 'id' ];
                        $newBooking = $em->getRepository( 'App:Booking' )->find( $booking[ 'id' ] );
                    } else {
                        $newBooking = new Booking();
                    }
                    $newBooking->setName( $booking[ 'name' ] );
                    $newBooking->setNumberPerson( $booking[ 'persons' ] );
                    $newBooking->setLp( $booking[ 'lp' ] );
                    $newBooking->setHtcTour( $tour );
                    $newBooking->setCanceled( $booking[ 'canceled' ] ?? null );
                    $em->persist( $newBooking );
                }
            }
            foreach ( $bookingsIds as $booking ) {
                if ( !in_array( $booking, $bookings ) ) {
                    $toRemove[] = $em->getRepository( 'App:Booking' )->find( $booking );
                }
            }

            $notificationsIds = Util::filter_by_ids( $tour->getNotifications()->toArray() ?? [] );
            $notifications = [];
            if ( isset( $data[ 'notifications' ] ) ) {
                $repo = $em->getRepository( 'App:Notification' );
                foreach ( $data[ 'notifications' ] as $value ) {
                    if ( !in_array( $value[ 'id' ], $notificationsIds ) ) {
                        $notificationTour = new NotificationTour( $repo->find( $value[ 'notification' ] ), $value[ 'days' ] ?? null );
                        $notificationTour->setHtcTour( $tour );
                    } else {
                        $notifications[] = $value[ 'id' ];
                        $notificationTour = $em->getRepository( 'App:NotificationTour' )->find( $value[ 'id' ] );
                        $notificationTour->setDays( $value[ 'days' ] ?? null );
                    }
                    if ( isset( $value[ 'done' ] ) )
                        $notificationTour->setDone( $value[ 'done' ] );
                    $em->persist( $notificationTour );
                }
            }
            foreach ( $notificationsIds as $notificationsId ) {
                if ( !in_array( $notificationsId, $notifications ) ) {
                    $toRemove[] = $em->getRepository( 'App:NotificationTour' )->find( $notificationsId );
                }
            }

            if ( isset( $toRemove ) )
                foreach ( $toRemove as $item )
                    $em->remove( $item );
            $em->flush();

        }
        return new JsonResponse( [ 'created' ] );
    }

    /**
     * @Route("/list-ls-tour", name="list_ls_tour")
     */
    public function listLsTour(): Response
    {
        $result = [];
        $data = Util::decodeBody();
        if ( isset( $data[ 'search' ]) ) {
            $tours = $this->getDoctrine()->getRepository( 'App:LSTour' )->advanceSearch( isset( $data[ 'dateStart' ] ) ? new \DateTime( $data[ 'dateStart' ] ) : null, isset( $data[ 'dateEnd' ] ) ? new \DateTime( $data[ 'dateEnd' ] ) : null, isset( $data[ 'template' ] ) ? $this->getDoctrine()->getManager()->getRepository( 'App:LSTourTemplate' )->find( $data[ 'template' ] ) : null, isset( $data[ 'notifications' ] ) ? $data[ 'notifications' ] : [], isset( $data[ 'notificationStatus' ] ) ? $data[ 'notificationStatus' ] : false, isset( $data[ 'destination' ] ) ? $data[ 'destination' ] : [], isset( $data[ 'destinationAccommodation' ] ) ? $data[ 'destinationAccommodation' ] : false, $this->getDoctrine()->getManager()->getRepository( 'App:CodeTour' )->findOneBy( [] )->getDaysShow() );
        } else
            $tours = $this->getDoctrine()->getRepository( 'App:LSTour' )->list( null, $this->getDoctrine()->getManager()->getRepository( 'App:CodeTour' )->findOneBy( [] )->getDaysShow() );
        foreach ( $tours as $tour ) {
            $result[ $tour->getStartDate()->format( 'd-m-Y' ) ][] = $tour;
        }
        return new JsonResponse( $result );
    }

    /**
     * @Route("/list-htc-tour", name="list_htc_tour")
     */
    public function listHtcTour(): Response
    {
        $result = [];
        $data = Util::decodeBody();
        if ( isset( $data[ 'search' ] ) ) {
            $tours = $this->getDoctrine()->getRepository( 'App:HTCTour' )->advanceSearch( isset( $data[ 'dateStart' ] ) ? new \DateTime( $data[ 'dateStart' ] ) : null, isset( $data[ 'dateEnd' ] ) ? new \DateTime( $data[ 'dateEnd' ] ) : null, isset( $data[ 'template' ] ) ? $this->getDoctrine()->getManager()->getRepository( 'App:HTCTourTemplate' )->find( $data[ 'template' ] ) : null, isset( $data[ 'notifications' ] ) ? $data[ 'notifications' ] : [], isset( $data[ 'notificationStatus' ] ) ? $data[ 'notificationStatus' ] : false, $this->getDoctrine()->getManager()->getRepository( 'App:CodeTourOneDay' )->findOneBy( [] )->getDaysShow() );
        } else
            $tours = $this->getDoctrine()->getRepository( 'App:HTCTour' )->list( null, $this->getDoctrine()->getManager()->getRepository( 'App:CodeTourOneDay' )->findOneBy( [] )->getDaysShow() );
        foreach ( $tours as $tour ) {
            $result[ $tour->getStartDate()->format( 'd-m-Y' ) ][] = $tour;
        }
        return new JsonResponse( $result );
    }

    /**
     * @Route("/retrieve-ls-tour/{id}", name="retrieve_ls_tour")
     */
    public function getLsTour( LSTour $tour ): Response
    {
        return new JsonResponse( $tour->getTour() );
    }

    /**
     * @Route("/retrieve-htc-tour/{id}", name="retrieve_htc_tour")
     */
    public function getHtcTour( HTCTour $tour ): Response
    {
        return new JsonResponse( $tour->getTour() );
    }

    /**
     * @Route("/remove-ls-tour/{id}", name="remove_ls_tour")
     */
    public function removeLsTour( LSTour $tour ): Response
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove( $tour );
        $em->flush();
        return new JsonResponse( [ 'removed' ] );
    }

    /**
     * @Route("/remove-htc-tour/{id}", name="remove_htc_tour")
     */
    public function removeHtcTour( HTCTour $tour ): Response
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove( $tour );
        $em->flush();
        return new JsonResponse( [ 'removed' ] );
    }

    /**
     * @Route("/cancel-ls-tour/{id}", name="cancel_ls_tour")
     */
    public function cancelLsTour( LSTour $tour ): Response
    {
        $em = $this->getDoctrine()->getManager();
        $tour->setCanceled();
        $em->persist($tour);
        $em->flush();
        return new JsonResponse( [ $tour->isCanceled() ] );
    }

    /**
     * @Route("/cancel-htc-tour/{id}", name="cancel_htc_tour")
     */
    public function cancelHtcTour( HTCTour $tour ): Response
    {
        $em = $this->getDoctrine()->getManager();
        $tour->setCanceled();

        $em->flush();
        return new JsonResponse( $tour->isCanceled() );
    }

    /**
     * @Route("/tours-for-shedule", name="tours-for-shedule")
     */
    public function getTourForShedule(): Response
    {
        $em = $this->getDoctrine()->getManager();
        $tours = $em->getRepository( 'App:LSTour' )->getTourForShedule();
        $return = [];
        foreach ( $tours as $tour )
            $return[] = $tour->getTourForShedule();
        return new JsonResponse( $return );
    }

    /**
     * @Route("/export-excel/{htc}", name="export-excel")
     */
    public function exportExcel( $htc = null ): Response
    {
        $data = Util::decodeBody();
        $em = $this->getDoctrine()->getManager();
        if ( $htc )
            $tours = $em->getRepository( 'App:HTCTour' )->findByIds( $data[ 'ids' ] ); else
            $tours = $em->getRepository( 'App:LSTour' )->findByIds( $data[ 'ids' ] );

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue( 'A1', 'Tour Code' )->setCellValue( 'B1', 'Guide' )->setCellValue( 'C1', 'Contact Name' )->setCellValue( 'D1', 'Pax. N' )->setCellValue( 'E1', 'LP' );

        if ( !$htc ) {
            $sheet->setCellValue( 'F1', 'Start Date' )->setCellValue( 'G1', 'End Date' );
            $code = $em->getRepository( 'App:CodeTour' )->findOneBy( [] )->getName();
        } else {
            $sheet->setCellValue( 'F1', 'Date' );
            $code = $em->getRepository( 'App:CodeTourOneDay' )->findOneBy( [] )->getName();
        }


        for ( $i = 0, $c = 0, $r = 2, $cols = [ 'A', 'B', 'C', 'D', 'E', 'F', 'G' ]; $i < count( $tours ); $i++ ) {
            $tour = $tours[ $i ];
            $canceled = $tour->getCanceled();
            if ( $canceled ) {
                $sheet->getStyle( $cols[ $c ] . $r )->getFont()->getColor()->setARGB( \PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED );
                $sheet->getStyle( $cols[ $c + 1 ] . $r )->getFont()->getColor()->setARGB( \PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED );
            }
            $sheet->setCellValue( $cols[ $c++ ] . $r, $tour->getCode() )->setCellValue( $cols[ $c++ ] . $r, $tour->getGuide() ? $tour->getGuide()->getName() : 'No Guide' );

            $q = 0;
            $bookings = $tour->getBookings()->toArray();
            if ( !empty( $bookings ) ) {
                for ( $r2 = $r; $q < count( $bookings ); $q++ ) {
                    $booking = $bookings[ $q ];
                    if ( $booking->getCanceled() || $canceled ) {
                        $sheet->getStyle( $cols[ $c ] . $r2 )->getFont()->getColor()->setARGB( \PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED );
                        $sheet->getStyle( $cols[ $c + 1 ] . $r2 )->getFont()->getColor()->setARGB( \PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED );
                        $sheet->getStyle( $cols[ $c + 2 ] . $r2 )->getFont()->getColor()->setARGB( \PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED );
                    }
                    $sheet->setCellValue( $cols[ $c++ ] . $r2, $booking->getName() )->setCellValue( $cols[ $c++ ] . $r2, $booking->getNumberPerson() )->setCellValue( $cols[ $c++ ] . $r2++, $booking->getLp() );
                    if ( $q + 1 < count( $bookings ) )
                        $c -= 3;
                }
            } else $c += 3;
            if ( $canceled )
                $sheet->getStyle( $cols[ $c ] . $r )->getFont()->getColor()->setARGB( \PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED );
            $sheet->setCellValue( $cols[ $c++ ] . intval( $r ), $tour->getStartDate()->format( 'd-m-Y' ) );
            if ( !$htc ) {
                if ( $canceled )
                    $sheet->getStyle( $cols[ $c ] . $r )->getFont()->getColor()->setARGB( \PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED );
                $sheet->setCellValue( $cols[ $c ] . intval( $r ), $tour->getEndDate() ? $tour->getEndDate()->format( 'd-m-Y' ) : 'No End Date' );
            }
            $c = 0;
            $r += $q ? $q : 1;
        }
        $writer = new Xlsx( $spreadsheet );
        $file = new File();
        $name = "$code Tour " . $tours[ 0 ]->getStartDate()->format( 'd-m-Y' );
        if ( count( $tours ) > 1 )
            $name .= ' to ' . $tours[ count( $tours ) - 1 ]->getStartDate()->format( 'd-m-Y' );
        $file->setName( $name . '.xlsx' );
        $file->setTemporal( true );
        $file->setPath( date_timestamp_get( new \DateTime() ) );
        $em->persist( $file );
        $em->flush();
        $writer->save( File::FILE_PATH . $file->getPath() );
        return new JsonResponse( $file->getId() );
    }

    /**
     * @Route("/remove-guide", name="remove-guide")
     */
    public function removeGuide(): Response
    {
        $data = Util::decodeBody();
        $em = $this->getDoctrine()->getManager();
        $repo = ( $data[ 'type' ] ) ? $em->getRepository( 'App:LSTour' ) : $em->getRepository( 'App:HTCTour' );
        $tour = $repo->find( $data[ 'id' ] );
        $tour->setGuide( null );
        $em->persist( $tour );
        if ( $notification = $repo->findNotificationByCode( $tour, 'ag' ) )
            $notification->setDone( null );
        $em->flush();
        return new JsonResponse( [ 'removed' ] );
    }

    /**
     * @Route("/assing-guide", name="assing-guide")
     */
    public function assingGuide(): Response
    {
        $data = Util::decodeBody();
        $em = $this->getDoctrine()->getManager();
        $repo = ( $data[ 'ls' ] ) ? $em->getRepository( 'App:LSTour' ) : $em->getRepository( 'App:HTCTour' );
        $tour = $repo->find( $data[ 'tour' ] );
        $guide = $em->getRepository( 'App:Guide' )->find( $data[ 'guide' ] );
        $tour->setGuide( $guide );
        $em->persist( $tour );
        if ( $notification = $repo->findNotificationByCode( $tour, 'ag' ) )
            $notification->setDone( true );
        $em->flush();
        return new JsonResponse( true );
    }

    /**
     * @Route("/get-tours-by-date", name="get-tours-by-date")
     */
    public function getToursByDate(): Response
    {
        $data = Util::decodeBody();
        $em = $this->getDoctrine()->getManager();
        $htcTours = $em->getRepository( 'App:HTCTour' )->findByDate( new \DateTime( $data[ 'date' ] ) );
        $lsTours = $em->getRepository( 'App:LSTour' )->findBy( [ 'startDate' => new \DateTime( $data[ 'date' ] ) ] );
        return new JsonResponse( array_merge( $htcTours, $lsTours ) );
    }

    /**
     * @Route("/change-days-show/{ls}", name="change-days-show")
     */
    public function changeDaysShow( $ls = null ): Response
    {
        $data = Util::decodeBody();
        $em = $this->getDoctrine()->getManager();
        $object = $ls ? $em->getRepository( 'App:CodeTour' )->findOneBy( [] ) : $em->getRepository( 'App:CodeTourOneDay' )->findOneBy( [] );
        $object->setDaysShow( $data[ 'days' ] );
        $em->persist( $object );
        $em->flush();
        return new JsonResponse( true );
    }

    /**
     * @Route("/tour-days-shows/{ls}", name="tour-days-shows")
     */
    public function getTourDaysShow( $ls = null ): Response
    {
        $em = $this->getDoctrine()->getManager();
        $days = $ls ? $em->getRepository( 'App:CodeTour' )->findOneBy( [] )->getDaysShow() : $em->getRepository( 'App:CodeTourOneDay' )->findOneBy( [] )->getDaysShow();
        return new JsonResponse( $days );
    }

    /**
     * @Route("/change-notification-state/{id}", name="change-notification-state")
     */
    public function changeNotificationState( NotificationTour $notificationTour ): Response
    {
        $em = $this->getDoctrine()->getManager();
        $notificationTour->setDone();
        $em->persist( $notificationTour );
        $em->flush();
        return new JsonResponse( [ $notificationTour->getDone() ] );
    }


    /**
     * @Route("/send-to-guide", name="send-to-guide")
     */
    public function sendToGuide( \Swift_Mailer $mailer ): Response
    {
        $data = Util::decodeBody();
        $em = $this->getDoctrine()->getManager();
        if ( $data[ 'type' ] == 'ls' ) {
            $tour = $em->getRepository( 'App:LSTour' )->find( $data[ 'id' ] );
            $notification = $em->getRepository( 'App:LSTour' )->findNotificationByCode( $tour, 'sg' );
            $template = "emails/tour-to-guide.html.twig";
        } else {
            $tour = $em->getRepository( 'App:HTCTour' )->find( $data[ 'id' ] );
            $notification = $em->getRepository( 'App:HTCTour' )->findNotificationByCode( $tour, 'sg' );
            $template = "emails/htcTour-to-guide.html.twig";
        }

        $message = ( new \Swift_Message( 'New ' . $tour->getCode() ) )
            ->setFrom( 'office@' . $this->get( 'router' )->getContext()->getHost() )
            ->setTo( $tour->getGuide()->getEmail() )
            ->setBody( $this->renderView( $template, array( 'tour' => $tour ) ), 'text/html' );
        if($files = $tour->getAttachment()){
            foreach($files as $file){
                $message->attach(\Swift_Attachment::fromPath($file->pathToDownload())->setFilename($file->getName()));
            }
        }
        try {
            $mailer->send( $message );
            $tour->setSent( true );
            $em->persist( $tour );
            if ( $notification ) {
                $notification->setDone( true );
                $em->persist( $notification );
            }
            $em->flush();
            return new JsonResponse( true );
        } catch ( \Exception $e ) {
            return new JsonResponse( false );
        }


    }

    static function checkTourNotifications( $tours, EntityManager $em, $justTransfer = null )
    {
        if(!is_array($tours))
            $tours = [$tours];
        foreach ($tours as $tour){
            if ( !empty( $tour->getBookings()->toArray() ) )
                foreach ( $em->getRepository( 'App:LSTour' )->findNotificationsToCheck( $tour, $justTransfer ) as $notification ) {
                    switch ( $notification->getNotification()->getCode() ) {
                        case 'a':
                            {
                                $accommodations = $em->getRepository( 'App:LSTour' )->countAccommodationsCompleted( $tour );
                                $totalAccommodation = count( $tour->getDestinations()->toArray() ) * count( $tour->getBookings()->toArray() ) + $em->getRepository( 'App:LSTour' )->countAccommodationsExt( $tour );
                                if ( $accommodations == $totalAccommodation ) {
                                    $notification->setDone( true );
                                    $em->persist( $notification );
                                    $em->flush();
                                } else if ( $notification->getDone() ) {
                                    $notification->setDone( false );
                                    $em->persist( $notification );
                                    $em->flush();
                                }
                                break;
                            }
                        case 'at':
                            {
                                $transfers = $em->getRepository( 'App:LSTour' )->countTransferComplete( $tour );
                                $totalTransfer = 2 * count( $tour->getBookings()->toArray() );
                                if ( isset( $transfers[ 0 ][ 1 ] ) && $transfers[ 0 ][ 1 ] == $totalTransfer ) {
                                    $notification->setDone( true );
                                    $em->persist( $notification );
                                    $em->flush();
                                } else if ( $notification->getDone() ) {
                                    $notification->setDone( false );
                                    $em->persist( $notification );
                                    $em->flush();
                                }
                                break;
                            }
                    }
                }
        }
    }
}

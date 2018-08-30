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
use App\Entity\File;
use App\Entity\Booking;
use App\Entity\Transfer;
use App\Entity\TransferAirport;
use App\Utils\Util;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Psr\Log\LoggerInterface;
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
class ApiController extends AbstractController
{

    /**
     * @Route("/delete-various/{entity}", name="api_delete_varius")
     */
    public function deleteItems( $entity ): Response
    {
        $data = Util::decodeBody();
        if ( is_array( $data ) && isset( $data[ 'items' ] ) ) {
            $em = $this->getDoctrine()->getManager();
            $repo = $em->getRepository( "App:$entity" );
            foreach ( $data[ 'items' ] as $item ) {
                $em->remove( $repo->find( $item ) );
            }
            $em->flush();
            return new JsonResponse( [ 'removed' ] );
        }
        return new JsonResponse( [ 'fail' ] );
    }

    /**
     * @Route("/notification-type/{type}", name="api_notification_by_type")
     */
    public function getNotificationByType( $type ): Response
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository( "App:Notification" );
        $result = [];
        foreach ( $repo->findBy( [], [ 'orderList' => 'DESC' ] ) as $notification ) {
            if ( in_array( $type, $notification->getTypes() ) ) {
                $result[] = $notification->jsonSerialize();
            }
        }
        return new JsonResponse( $result );
    }

    /**
     * @Route("/upload-file", name="updload-file")
     */
    public function uploadFile(): Response
    {

        $fs = new Filesystem();
        $em = $this->getDoctrine()->getManager();

        foreach ( $_FILES as $value ) {
            $file = new File();
            $file->setName( $value[ 'name' ] );
            $file->setTemporal( true );
            $file->setPath( date_timestamp_get( new \DateTime() ) );
            try {
                $fs->copy( $value[ 'tmp_name' ], File::FILE_PATH . $file->getPath() );
                $em->persist( $file );
                $em->flush();
                return new JsonResponse( [ $file->getPath() ] );
            } catch ( \Exception $e ) {

            }
        }
        return new JsonResponse( [] );
    }

    /**
     * @Route("/delete-file", name="delete-file")
     */
    public function deleteFile( Request $request, LoggerInterface $logger ): Response
    {
        $data = Util::decodeBody();
        if ( is_array( $data ) && isset( $data[ 'path' ] ) ) {
            $em = $this->getDoctrine()->getManager();
            $em->remove( $em->getRepository( 'App:File' )->findOneBy( [ 'path' => $data[ 'path' ] ] ) );
            $em->flush();
            return new JsonResponse( [ 'success' ] );
        }
        return new JsonResponse( [ 'fail' ] );
    }

    /**
     * @Route("/download-file/{id}", name="download-file")
     */
    public function donwloadFile( File $file )
    {
        header( 'Content-Description: File Transfer' );
        header( 'Content-Type: application/octet-stream' );
        header( 'Content-Disposition: attachment; filename="' . $file->getName() . '"' );
        header( 'Expires: 0' );
        header( 'Cache-Control: must-revalidate' );
        header( 'Pragma: public' );
        header( 'Content-Length: ' . filesize( $file->pathToDownload() ) );
        flush(); // Flush system output buffer
        readfile( $file->pathToDownload() );
        exit;
    }

    /**
     * @Route("/transfer-list", name="transfer-list")
     */
    public function listTransfer(): Response
    {
        $result = [];
        $data = Util::decodeBody();
        if ( isset( $data[ 'search' ] ) ) {
            $em = $this->getDoctrine()->getManager();
            $transfers = $this->getDoctrine()->getRepository( 'App:TransferAirport' )->advanceSearch( isset( $data[ 'dateStart' ] ) ? new \DateTime( $data[ 'dateStart' ] ) : null, isset( $data[ 'dateEnd' ] ) ? new \DateTime( $data[ 'dateEnd' ] ) : null, isset( $data[ 'type' ] ) ? $data[ 'type' ] : 'all', isset( $data[ 'notifications' ] ) ? $data[ 'notifications' ] : [], isset( $data[ 'notificationStatus' ] ) ? $data[ 'notificationStatus' ] : false, isset( $data[ 'company' ] ) ? $em->getRepository( 'App:Company' )->find( $data[ 'company' ] ) : false, isset( $data[ 'drive' ] ) && $data[ 'drive' ] ? $em->getRepository( 'App:Driver' )->find( $data[ 'drive' ] ) : false, isset( $data[ 'receptive' ] ) && $data[ 'receptive' ] ? $em->getRepository( 'App:TransferReciptive' )->find( $data[ 'receptive' ] ) : false, $this->getDoctrine()->getManager()->getRepository( 'App:Transfer' )->findOneBy( [] )->getDaysShow() );
        } else
            $transfers = $this->getDoctrine()->getRepository( 'App:TransferAirport' )->list( null, $this->getDoctrine()->getManager()->getRepository( 'App:Transfer' )->findOneBy( [] )->getDaysShow() );
        foreach ( $transfers as $transfer ) {
            $result[ $transfer->getDate()->format( 'd-m-Y' ) ][] = $transfer->getTransferForList();
        }
        return new JsonResponse( $result );
    }

    /**
     * @Route("/retrieve-transfer-airport/{id}", name="retrieve-transfer-airport")
     */
    public function retrieveTransfer( TransferAirport $transferAirport ): Response
    {
        return new JsonResponse( $transferAirport->retrieve() );
    }

    /**
     * @Route("/insert-transfer-airport", name="insert-transfer-airport")
     */
    public function insertTransfer(): Response
    {
        $data = Util::decodeBody();
        $em = $this->getDoctrine()->getManager();
        $transferAirport = new TransferAirport();
        if ( isset( $data[ 'accommodation' ] ) ) {
            $acc = new Accommodation( $data[ 'accommodation' ] );
            $em->persist( $acc );
            $accBooking = new AccommodationBooking();
            $accBooking->setAccommodation( $acc );
            $em->persist( $accBooking );
            $transferAirport->setAccommodation( $accBooking );
        }
        if ( isset( $data[ 'flyData' ] ) )
            $transferAirport->setFlightData( $data[ 'flyData' ] );
        if ( isset( $data[ 'driver' ] ) )
            $transferAirport->setDrive( $em->getRepository( 'App:Driver' )->find( $data[ 'driver' ] ) );
        if ( isset( $data[ 'date' ] ) ) {
            $date = $data[ 'date' ];
            if ( isset( $data[ 'time' ] ) )
                $date .= ' ' . $data[ 'time' ];
            $transferAirport->setDate( new \DateTime( $date ) );
        }
        if ( isset( $data[ 'booking' ] ) ) {
            $newBooking = new Booking();
            $newBooking->setName( $data[ 'booking' ] );
            $newBooking->setNumberPerson( $data[ 'number' ] );
            $transferAirport->setNumberPerson( $data[ 'number' ] );
            if ( isset( $data[ 'type' ] ) ) {
                if ( $data[ 'type' ] == 'in' ) {
                    $transferAirport->setInTranfer( true );
                    if ( isset( $newBooking ) )
                        $newBooking->setAirPortTransferIn( $transferAirport );
                } else {
                    $transferAirport->setInTranfer( false );
                    if ( isset( $newBooking ) )
                        $newBooking->setAirPortTransferOut( $transferAirport );
                }
            }
            $em->persist( $newBooking );
        }

        if ( isset( $data[ 'tour' ] ) )
            $transferAirport->setTour( $em->getRepository( 'App:HTCTour' )->find( $data[ 'tour' ] ) );
        if ( isset( $data[ 'company' ] ) )
            $transferAirport->setCompany( $em->getRepository( 'App:Company' )->find( $data[ 'company' ] ) );

        $em->persist( $transferAirport );
        $em->flush();
        return new JsonResponse( [ 'updated' ] );
    }

    /**
     * @Route("/edit-transfer-airport/{id}", name="edit-transfer-airport")
     */
    public function editTransfer( TransferAirport $transferAirport ): Response
    {
        $data = Util::decodeBody();
        $em = $this->getDoctrine()->getManager();
        if ( isset( $data[ 'accommodation' ] ) ) {
            if ( $accommodation = $transferAirport->getAccommodation() ) {
                if ( $accommodation->getAccommodation()->getName() != $data[ 'accommodation' ] && $data[ 'accommodation' ] != "" ) {
                    $accommodation = $accommodation->getAccommodation();
                    $accommodation->setName( $data[ 'accommodation' ] );
                    $em->persist( $accommodation );
                } elseif ( $data[ 'accommodation' ] == "" ) {
                    $em->remove( $accommodation );
                    $em->flush();
                }
            } elseif ( $data[ 'accommodation' ] != "" ) {
                $acc = new Accommodation( $data[ 'accommodation' ] );
                $em->persist( $acc );
                $accBooking = new AccommodationBooking();
                $accBooking->setAccommodation( $acc );
                $em->persist( $accBooking );
                $transferAirport->setAccommodation( $accBooking );
            }
        }
        if ( isset( $data[ 'flyData' ] ) )
            $transferAirport->setFlightData( $data[ 'flyData' ] );
        if ( isset( $data[ 'driver' ] ) )
            $transferAirport->setDrive( $em->getRepository( 'App:Driver' )->find( $data[ 'driver' ] ) );
        if ( isset( $data[ 'date' ] ) ) {
            $date = $data[ 'date' ];
            if ( isset( $data[ 'time' ] ) )
                $date .= ' ' . $data[ 'time' ];
            $transferAirport->setDate( new \DateTime( $date ) );
        }

        $em->persist( $transferAirport );
        $em->flush();

        if ( $booking = $transferAirport->getBooking() ) {
            if ( $tour = $booking->getTour() ) {
                TourController::checkTourNotifications( $tour, $em, true );
            }
        }
        return new JsonResponse( [ 'updated' ] );
    }

    /**
     * @Route("/change-transfer-state/{id}", name="change-transfer-state")
     */
    public function changeTransferState( TransferAirport $transferAirport ): Response
    {
        $transferAirport->setCanceled( !$transferAirport->getCanceled() );
        $em = $this->getDoctrine()->getManager();
        $em->persist( $transferAirport );
        $em->flush();
        return new JsonResponse( [ $transferAirport->getCanceled() ] );
    }

    /**
     * @Route("/change-sent-state/{id}", name="change-sent-state")
     */
    public function changeSentState( TransferAirport $transferAirport ): Response
    {
        $transferAirport->setSent( !$transferAirport->getSent() );
        $em = $this->getDoctrine()->getManager();
        if($tour = $transferAirport->getTour()){
                TourController::checkTourNotifications( $tour, $em, true );
        }
        $em->persist( $transferAirport );
        $em->flush();
        return new JsonResponse( [ $transferAirport->getSent() ] );
    }

    /**
     * @Route("/remove-transfer-airport/{id}", name="remove-transfer-airport")
     */
    public function removeTransfer( TransferAirport $transferAirport ): Response
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove( $transferAirport );
        $em->flush();
        return new JsonResponse( [ 'removed' ] );
    }


    /**
     * @Route("/export-transfer-excel", name="export-transfer-excel")
     */
    public function exportTransferExcel(): Response
    {
        $data = Util::decodeBody();
        $em = $this->getDoctrine()->getManager();
        $transfers = $em->getRepository( 'App:TransferAirport' )->findByIds( $data[ 'ids' ] );

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue( 'A1', 'Date' )->setCellValue( 'B1', 'Type' )->setCellValue( 'C1', 'Tour' )->setCellValue( 'D1', 'Contact Name' )->setCellValue( 'E1', 'Pax. N' )->setCellValue( 'F1', 'Fly Date' )->setCellValue( 'G1', 'Accommodation' )->setCellValue( 'H1', 'Company' )->setCellValue( 'I1', 'Sent' )->setCellValue( 'J1', 'Drive/Receptive' );

        for ( $i = 0, $c = 0, $r = 2, $cols = [ 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J' ]; $i < count( $transfers ); $i++ ) {
            $transfer = $transfers[ $i ];
            if ( $tour = $transfer->getTour() )
                $tour = $tour->getCode(); else if ( $booking = $transfer->getBooking() ) {
                $tour = $booking->getTourObject() ? $booking->getTourObject()->getCode() : 'No Tour';
            } else
                $tour = 'No Tour';
            if ( $transfer->getCanceled() ) {
                $sheet->getStyle( $cols[ $c ] . $r )->getFont()->getColor()->setARGB( \PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED );
                $sheet->getStyle( $cols[ $c + 1 ] . $r )->getFont()->getColor()->setARGB( \PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED );
                $sheet->getStyle( $cols[ $c + 2 ] . $r )->getFont()->getColor()->setARGB( \PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED );
                $sheet->getStyle( $cols[ $c + 3 ] . $r )->getFont()->getColor()->setARGB( \PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED );
                $sheet->getStyle( $cols[ $c + 4 ] . $r )->getFont()->getColor()->setARGB( \PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED );
                $sheet->getStyle( $cols[ $c + 5 ] . $r )->getFont()->getColor()->setARGB( \PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED );
                $sheet->getStyle( $cols[ $c + 6 ] . $r )->getFont()->getColor()->setARGB( \PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED );
                $sheet->getStyle( $cols[ $c + 7 ] . $r )->getFont()->getColor()->setARGB( \PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED );
                $sheet->getStyle( $cols[ $c + 8 ] . $r )->getFont()->getColor()->setARGB( \PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED );
                $sheet->getStyle( $cols[ $c + 9 ] . $r )->getFont()->getColor()->setARGB( \PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED );
            }
            $sheet->setCellValue( $cols[ $c++ ] . $r, $transfer->getDate()->format( 'd-m-Y' ) )->setCellValue( $cols[ $c++ ] . $r, $transfer->getInTranfer() ? 'In' : 'Out' )->setCellValue( $cols[ $c++ ] . $r, $tour )->setCellValue( $cols[ $c++ ] . $r, $transfer->getInTranfer() ? $transfer->getBookingIn()->getName() : $transfer->getBookingOut()->getName() )->setCellValue( $cols[ $c++ ] . $r, $transfer->getInTranfer() ? $transfer->getBookingIn()->getNumberPerson() : $transfer->getBookingOut()->getNumberPerson() )->setCellValue( $cols[ $c++ ] . $r, $transfer->getFlightData() )->setCellValue( $cols[ $c++ ] . $r, $transfer->getAccommodation() ? $transfer->getAccommodation()->getAccommodation()->getName() : 'No Accommodation' )->setCellValue( $cols[ $c++ ] . $r, $transfer->getCompany() ? $transfer->getCompany()->getName() : 'No Company' );
            $sheet->setCellValue( $cols[ $c++ ] . $r, $transfer->getSent() ? 'Yes' : 'No' );
            if ( $transfer->getSent() ) {
                if ( $drive = $transfer->getDrive() ) {
                    $text = $drive->getName().' (Drive)';
                } else {
                    $transfersReceptives = $em->getRepository( 'App:TransferReciptive' )->findAll();
                    foreach ( $transfersReceptives as $transferReciptive ) {
                        $number = $transfer->getNumberPerson();
                        if ( $number <= $transferReciptive->getPaxMax() || !$transferReciptive->getPaxMax() ) {
                            $text = $transferReciptive->getName().' (Receptive)';
                        }
                    }
                }
            } else
                $text = '-------';
            $sheet->setCellValue( $cols[ $c++ ] . $r++, $text );
            $c = 0;
        }
        $writer = new Xlsx( $spreadsheet );
        $file = new File();
        $name = 'Transfers ' . $transfers[ 0 ]->getDate()->format( 'd-m-Y' );
        if ( count( $transfers ) > 1 )
            $name .= ' to ' . $transfers[ count( $transfers ) - 1 ]->getDate()->format( 'd-m-Y' );
        $file->setName( $name . '.xlsx' );
        $file->setTemporal( true );
        $file->setPath( date_timestamp_get( new \DateTime() ) );
        $em->persist( $file );
        $em->flush();
        $writer->save( File::FILE_PATH . $file->getPath() );
        return new JsonResponse( $file->getId() );
        return new JsonResponse( true );
    }

    /**
     * @Route("/change-booking-state/{id}", name="change-booking-state")
     */
    public function changeBookingState( Booking $booking ): Response
    {
        $booking->setCanceled( !$booking->getCanceled() );
        $em = $this->getDoctrine()->getManager();
        $em->persist( $booking );
        $em->flush();
        return new JsonResponse( [ $booking->getCanceled() ] );
    }

    /**
     * @Route("/change-code/", name="change-code")
     */
    public function changeCode(): Response
    {
        $data = Util::decodeBody();
        $em = $this->getDoctrine()->getManager();
        if ( $data[ 'ls' ] ) {
            $code = $em->getRepository( 'App:CodeTour' )->findOneBy( [] );
            $company = $em->getRepository( 'App:Company' )->findOneBy( [ 'companyLS' => $code ] );
            $code->setName( $data[ 'code' ] );
            $company->setCode( $data[ 'code' ] );
        } else {
            $code = $em->getRepository( 'App:CodeTourOneDay' )->findOneBy( [] );
            $company = $em->getRepository( 'App:Company' )->findOneBy( [ 'companyHTC' => $code ] );
            $code->setName( $data[ 'code' ] );
            $company->setCode( $data[ 'code' ] );
        }
        $em->persist( $code );
        $em->flush();
        return new JsonResponse( [ 'changed' ] );
    }

    /**
     * @Route("/change-transfer-days-show", name="change-transfer-days-show")
     */
    public function changeDaysShow(): Response
    {
        $data = Util::decodeBody();
        $em = $this->getDoctrine()->getManager();
        $object = $em->getRepository( 'App:Transfer' )->findOneBy( [] );
        $object->setDaysShow( $data[ 'days' ] );
        $em->persist( $object );
        $em->flush();
        return new JsonResponse( true );
    }

    /**
     * @Route("/transfer-days-shows", name="transfer-days-shows")
     */
    public function getTourDaysShow(): Response
    {
        $em = $this->getDoctrine()->getManager();
        if ( !( $transfer = $em->getRepository( 'App:Transfer' )->findOneBy( [] ) ) ) {
            $transfer = new Transfer();
            $em->persist( $transfer );
            $em->flush();
        }
        return new JsonResponse( $transfer->getDaysShow() );
    }


    /**
     * @Route("/transfer-send-mails", name="transfer-send-mails")
     */
    public function transferSendMails( \Swift_Mailer $mailer ): Response
    {
        $data = Util::decodeBody();
        $em = $this->getDoctrine()->getManager();

        $transfers = $em->getRepository( 'App:TransferAirport' )->findIds( Util::array_string_values( $data[ 'ids' ] ) );

        $transfersReceptives = $em->getRepository( 'App:TransferReciptive' )->findAll();


        foreach ( $transfersReceptives as $transferReciptive ) {
            $toSend = [];
            $idsTosends = [];
            foreach ( $transfers as $transfer ) {
                $number = $transfer->getNumberPerson();
                if ( !in_array($transfer->getId(), $idsTosends) && ($number <= $transferReciptive->getPaxMax() || !$transferReciptive->getPaxMax())) {
                    $toSend[] = $transfer;
                    $idsTosends[] = $transfer->getId();
                }

            }
            if(!empty($toSend)){
                $message = ( new \Swift_Message( 'New Transfer' ) )->setFrom( 'office@' . $this->get( 'router' )->getContext()->getHost() )->setTo( $transferReciptive->getEmail() )->setBody( $this->renderView( 'emails/transfer-receptive.html.twig', array( 'transfers' => $toSend ) ), 'text/html' );
                try {
                    $mailer->send( $message );
                    $tours = [];
                    foreach ($toSend as $transfer){
                        $transfer->setSent( true );
                        $em->persist( $transfer );
                        if($booking = $transfer->getBooking()){
                            if($tour = $booking->getTour())
                                $tours[] = $tour;
                        }
                    }
                    $em->flush();
                    if(!empty($tours))
                    TourController::checkTourNotifications( $tours, $em, true );
                    break;
                } catch ( \Exception $e ) {
                    return new JsonResponse( false );
                }
            }
        }
        return new JsonResponse( true );
    }
}

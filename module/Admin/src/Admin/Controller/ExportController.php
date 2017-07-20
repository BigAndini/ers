<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use ErsBase\Entity;
use Admin\Form;
use Admin\Service;
use Admin\DataTables;
use Heartsentwined\Cron\Service\Cron;

class ExportController extends AbstractActionController {
    public function indexAction() {
        return $this->notFoundAction();
    }
    
    public function exportXlsAction()
    {
        set_time_limit( 0 );

        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        /*$orders = $em->getRepository('ErsBase\Entity\Order')
                ->findBy(array(), array('created' => 'ASC'));*/
        $packages = $em->getRepository('ErsBase\Entity\Package')
                ->findBy(array(), array('created' => 'ASC'));
        
        $filename = getcwd() . "/tmp/excel-" . date( "m-d-Y" ) . ".xls";
        $realPath = realpath( $filename );
        if ( false === $realPath ) {
            touch( $filename );
            chmod( $filename, 0644 );
        }
        $filename = realpath( $filename );
        
        $finalData = array();
        $finalData[] = array(
            'code',
            'participant firstname',
            'participant surname',
            'list of items',
            'date of purchase',
            'status',
        );
        foreach ($packages as $package) {
            if($package->getStatus() == 'order pending') {
                continue;
            }
            $order = $package->getOrder();
            $item_list = '';
            foreach($package->getItems() as $item) {
                $item_list .= $item->getName();
                foreach($item->getItemVariants() as $variant) {
                    $item_list .= $variant->getName().' '.$variant->getValue().'; ';
                }
                $item_list .= "\r\n";
            }
            $item_list = preg_replace('/\r\n$/', '', $item_list);
            
            $finalData[] = array(
                utf8_decode($package->getCode()->getValue()),
                utf8_decode($package->getParticipant()->getFirstname()),
                utf8_decode($package->getParticipant()->getSurname()),
                utf8_decode($item_list),
                utf8_decode($order->getCreated()->format('d.m.Y H:i:s')),
                utf8_decode($package->getStatus()),
            );
        }
        $handle = fopen( $filename, "w" );
        if(!$handle) {
            $logger = $this->getServiceLocator()->get('Logger');
            $logger->warn('unable to open file '.$filename);
            exit();
        }
        foreach ($finalData as $finalRow) {
            fputcsv( $handle, $finalRow, "\t" );
        }
        fclose($handle);
        
        $response = new \Zend\Http\Response();
        $response->getHeaders()
                ->addHeaderLine('Content-Type', 'application/vnd.ms-excel; charset=utf-8')
                ->addHeaderLine('Content-Disposition', 'attachment; filename=orders-'.date('Ymd\THis').'.xls')
                ->addHeaderLine('Content-Length', filesize($filename));
        $response->setContent(file_get_contents($filename));
        return $response;
    }
}
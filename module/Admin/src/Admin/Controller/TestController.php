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

class TestController extends AbstractActionController {
    public function indexAction() {
        return $this->notFoundAction();
    }
    
    public function exportXlsAction()
    {
        set_time_limit( 0 );

        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        /*$orders = $entityManager->getRepository('ErsBase\Entity\Order')
                ->findBy(array(), array('created' => 'ASC'));*/
        $packages = $entityManager->getRepository('ErsBase\Entity\Package')
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
            throw new \Exception('unable to open file '.$filename);
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
    
    public function datatablesAction()
    {
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $queryBuilder = $entityManager->getRepository('ErsBase\Entity\Order')->createQueryBuilder('n');
        
        /*$entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->add('select', 'p , q')
              ->add('from', '\ErsBase\Entity\Order q')
              ->leftJoin('q.product', 'p');*/
        
        
        $table = new DataTables\Order;
        /*$table->setAdapter($this->getDbAdapter())
                ->setSource($queryBuilder)
                ->setParamAdapter($this->getRequest()->getPost());*/
        $table->setSource($queryBuilder)
                ->setParamAdapter($this->getRequest()->getPost());
        
        return new ViewModel(array(
            'orderTable' => $table->render()
        ));
    }
    
    public function exceptionAction() {
        throw new \Exception('This is a test exception');
    }
    
    public function paidOrderSumAction() {
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $orders = $entityManager->getRepository('ErsBase\Entity\Order')
                ->findBy(array('payment_status' => 'paid'));
        
        $orderSum = 0;
        $paymentSum = 0;
        foreach($orders as $order) {
            $orderSum += (float) $order->getPrice();
            $paymentSum += (float) $order->getSum();
        }
        
        return new ViewModel(array(
            'ordersSum' => $orderSum,
            'paymentSum' => $paymentSum,
        ));
    }
    
    public function colTestAction() {
        return new ViewModel();
    }
    
    public function flashMessengerAction() {
        $this->flashMessenger()->addSuccessMessage('This is a success message');
        $this->flashMessenger()->addWarningMessage('This is a warning message');
        $this->flashMessenger()->addErrorMessage('This is an error message');
        return new ViewModel();
    }
}
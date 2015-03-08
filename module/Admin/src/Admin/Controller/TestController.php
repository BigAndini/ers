<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use ersEntity\Entity;
use Admin\Form;
use Admin\Service;
use Admin\DataTables;

class TestController extends AbstractActionController {
    public function indexAction() {
        return $this->notFoundAction();
    }
    
    public function exportXlsAction()
    {
        set_time_limit( 0 );

        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $orders = $em->getRepository("ersEntity\Entity\Order")
                ->findBy(array(), array('created' => 'ASC'));
        
        $filename = getcwd() . "/tmp/excel-" . date( "m-d-Y" ) . ".xls";
        $realPath = realpath( $filename );
        if ( false === $realPath ) {
            touch( $filename );
            chmod( $filename, 0644 );
        }
        $filename = realpath( $filename );
        
        $finalData = array();
        foreach ($orders as $order) {
            $finalData[] = array(
                utf8_decode($order->getId()),
                utf8_decode($order->getCode()->getValue()),
                utf8_decode($order->getPurchaser()->getFirstname()),
                utf8_decode($order->getPurchaser()->getSurname()),
                utf8_decode($order->getCreated()->format('d.m.Y H:i:s')),
            );
        }
        $handle = fopen( $filename, "w" );
        if(!$handle) {
            error_log('unable to open file '.$filename);
            exit();
        }
        foreach ($finalData as $finalRow) {
            fputcsv( $handle, $finalRow, "\t" );
        }
        fclose($handle);
        #$this->_helper->layout->disableLayout();
        #$this->_helper->viewRenderer->setNoRender();
        /*$this->getResponse()->setRawHeader( "Content-Type: application/vnd.ms-excel; charset=UTF-8" )
            ->setRawHeader( "Content-Disposition: attachment; filename=excel.xls" )
            ->setRawHeader( "Content-Transfer-Encoding: binary" )
            ->setRawHeader( "Expires: 0" )
            ->setRawHeader( "Cache-Control: must-revalidate, post-check=0, pre-check=0" )
            ->setRawHeader( "Pragma: public" )
            ->setRawHeader( "Content-Length: " . filesize( $filename ) )
            ->sendResponse();
        readfile( $filename ); exit();*/
        
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
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $qb = $em->getRepository("ersEntity\Entity\Order")->createQueryBuilder('n');
        
        /*$em = $this->getEntityManager();
        $queryBuilder = $em->createQueryBuilder();
        
        $queryBuilder->add('select', 'p , q')
              ->add('from', '\ersEntity\Entity\Order q')
              ->leftJoin('q.product', 'p');*/
        
        
        $table = new DataTables\Order;
        /*$table->setAdapter($this->getDbAdapter())
                ->setSource($qb)
                ->setParamAdapter($this->getRequest()->getPost());*/
        $table->setSource($qb)
                ->setParamAdapter($this->getRequest()->getPost());
        
        return new ViewModel(array(
            'orderTable' => $table->render()
        ));
    }
}
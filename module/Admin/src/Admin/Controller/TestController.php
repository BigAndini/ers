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
    
    public function mailqAction() {
        $emailService = $em = $this->getServiceLocator()
            ->get('ErsBase\Service\EmailService');
        
        $emailService->mailqWorker();
        return new ViewModel(array(
        ));
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
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $qb = $em->getRepository('ErsBase\Entity\Order')->createQueryBuilder('n');
        
        /*$em = $this->getEntityManager();
        $queryBuilder = $em->createQueryBuilder();
        
        $queryBuilder->add('select', 'p , q')
              ->add('from', '\ErsBase\Entity\Order q')
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
    
    public function exceptionAction() {
        throw new \Exception('This is a test exception');
    }
    
    public function paidOrderSumAction() {
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $orders = $em->getRepository('ErsBase\Entity\Order')
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
    
    public function orderSaveAction() {
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $orders = $em->getRepository('ErsBase\Entity\Order')
                ->findBy(array('total_sum' => 0));
        $count = 0;
        foreach($orders as $order) {
            $order->setTotalSum($order->getSum());
            $order->setOrderSum($order->getPrice());
            $em->persist($order);
            if($count >= 10) {
                $em->flush();
                $count = 0;
            }
            $count++;
        }
    }
    
    public function eticketHtmlAction() {
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $package = $em->getRepository('ErsBase\Entity\Package')
                ->findOneBy(array('id' => 52));
        
        $products = $em->getRepository('ErsBase\Entity\Product')
                ->findAll();
        
        $config = $this->getServiceLocator()->get('Config');
       
        $name = $package->getParticipant()->getFirstname().' '.$package->getParticipant()->getSurname();
        $code = $package->getCode()->getValue();

        /*
         * QR-Code creation
         */
        $qr = $this->getServiceLocator()->get('QRCode');
        $qr->isHttps(); // or $qr->isHttp();
        #$qr->setData('http://prereg.eja.net/onsite/register/'.  \urlencode($code));
        $onsitereg = $config['ERS']['onsitereg'];
        # ensure the url has no trailing slash
        \rtrim( $onsitereg, '/\\' );
        $qr->setData($onsitereg.'/'.\urlencode($code));
        
        $qr->setCorrectionLevel('H', 0);
        $qr->setDimensions(200, 200);
        $qr_config = array(
            'adapter'      => 'Zend\Http\Client\Adapter\Socket',
            'ssltransport' => 'tls',
            'sslcapath'    => '/etc/ssl/certs/',
            'sslverifypeer' => false,
        );

        // Instantiate a client object
        $client = new \Zend\Http\Client($qr->getResult(), $qr_config);

        // The following request will be sent over a TLS secure connection.
        $response = $client->send();
        
        $qr_content = $response->getContent();
        $base64_qrcode = "data:image/png;base64,".  \base64_encode($qr_content);
        
        #file_put_contents(getcwd().'/public/img/qrcode.png', $qr_content);
        
        /*
         * Barcode creation
         */
        
        // Only the text to draw is required
        $barcodeOptions = array(
            'text' => $code, 
            'barHeight' => 40,
            'factor' => 1.1,
            'drawText' => false,
        );

        // No required options
        $rendererOptions = array();

        // Draw the barcode in a new image,
        $imageResource = \Zend\Barcode\Barcode::factory(
            'code39', 'image', $barcodeOptions, $rendererOptions
        )->draw();
        
        ob_start(); //Start output buffer.
            imagejpeg($imageResource); //This will normally output the image, but because of ob_start(), it won't.
            $contents = ob_get_contents(); //Instead, output above is saved to $contents
        ob_end_clean(); //End the output buffer.
        
        #file_put_contents(getcwd().'/public/img/barcode2.jpg', $contents);
        
        $base64_barcode = "data:image/png;base64,".  \base64_encode($contents);
        
        /*
         * prepare items
         */
        $items = array();
        foreach($package->getItems() as $item) {
            $items[$item->getProductId()][] = $item;
        }
        
        $agegroupService = $this->getServiceLocator()
                ->get('PreReg\Service\AgegroupService:ticket');
        $agegroup = $agegroupService->getAgegroupByUser($package->getParticipant());
        
        /* 
         * generate PDF
         */
        $viewModel = new ViewModel();
        #$viewModel->setTemplate('pdf/eticket_'.$this->getLanguage());
        $viewModel->setTemplate('pdf/eticket_en');
        $viewModel->setVariables(array(
            'name' => $name,
            'package' => $package,
            'items' => $items,
            'products' => $products,
            'agegroup' => $agegroup,
            'code' => $code,
            'qrcode' => $base64_qrcode,
            'barcode' => $base64_barcode,
        ));
        
        return $viewModel;
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
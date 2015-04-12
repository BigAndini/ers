<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use ersEntity\Entity;
use Zend\Console\Request as ConsoleRequest;

class CronController extends AbstractActionController {
    public function cronAction() {
        $request = $this->getRequest();
 
        // Make sure that we are running in a console and the user has not tricked our
        // application into running this action from a public web server.
        if (!$request instanceof ConsoleRequest){
            throw new \RuntimeException('You can only use this action from a console! Got this request from '.get_class($request));
        }
 
        // Get system service name  from console and check if the user used --verbose or -v flag
        #$doname   = $request->getParam('doname', false);
        #$verbose     = $request->getParam('verbose');
        
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $orderStatus = $em->getRepository("ersEntity\Entity\OrderStatus")
                ->findBy(array('value' => 'cron'));
        foreach($orderStatus as $status) {
            $em->remove($status);
        }
        $em->flush();
        
        $orders = $em->getRepository("ersEntity\Entity\Order")
                ->findBy(array(), array('created' => 'DESC'));
        
        $logger = $this
            ->getServiceLocator()
            ->get('Logger');
        $logger->info('We are in runCron of TestCron');
        
        $output = '';
        foreach($orders as $order) {
            if($order->hasOrderStatus('cron')) {
                continue;
            }
            $output .= $order->getCode()->getValue().PHP_EOL;
            $orderStatus = new Entity\OrderStatus();
            $orderStatus->setValue('cron');
            $orderStatus->setOrder($order);
            $em->persist($orderStatus);
            
            $em->flush();
        }
        
        $output .= 'ready!';
        /*
         * ensure a newline at the end of output.
         */
        $output .= PHP_EOL;
        return $output;
    }
}
<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Cron;

use ErsBase\Entity;

class TestCron {
    public function runCron() {
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $orders = $em->getRepository('ErsBase\Entity\Order')
                ->findBy(array(), array('created' => 'DESC'));
        
        $logger = $this->getServiceLocator()->get('Logger');
        $logger->info('We are in runCron of TestCron');
        
        foreach($orders as $order) {
            if($order->hasOrderStatus('cron')) {
                continue;
            }
            $orderStatus = new Entity\OrderStatus();
            $orderStatus->setValue('cron');
            $em->persist($orderStatus);
            
            $order->setOrderStatus($orderStatus);
            $em->persist($order);
            $em->persist();
        }
    }
}
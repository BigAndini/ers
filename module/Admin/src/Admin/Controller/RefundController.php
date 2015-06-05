<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class RefundController extends AbstractActionController {
    public function indexAction() {
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        /*
         * search for orders that do contain items in status refund
         */
        $orders = $em->getRepository("ersEntity\Entity\Order");
        
        $items = $em->getRepository("ersEntity\Entity\Item")
                ->findBy(array('status' => 'refund'), array('updated' => 'DESC'));
        
        return new ViewModel(array(
            'items' => $items,
        ));
    }
}

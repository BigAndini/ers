<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class BankaccountController extends AbstractActionController {
 
    public function indexAction()
    {
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $orders = $em->getRepository("ersEntity\Entity\Order")
                ->findBy(array());
        
        return new ViewModel(array(
            'orders' => $orders,
        ));
    }
    
    public function uploadCsvAction() {
        return new ViewModel();
    }
    
    public function detailAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/order', array());
        }
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $order = $em->getRepository("ersEntity\Entity\Order")
                ->findOneBy(array('id' => $id));
        
        return new ViewModel(array(
            'order' => $order,
        ));
    }   
}
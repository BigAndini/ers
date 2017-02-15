<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace PreReg\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use ErsBase\Entity;
use ErsBase\Service;
use PreReg\Form;

class CartController extends AbstractActionController {
    /*
     * initialize shopping cart
     */
    private function initialize() {
        $cartContainer = new Container('cart');
        if(!isset($cartContainer->init) && $cartContainer->init == 1) {
            $cartContainer->order = new Entity\Order();
            $cartContainer->init = 1;
        }
    }
    
    /*
     * overview of the shopping cart
     */
    public function indexAction() {
        $this->initialize();
        return $this->redirect()->toRoute('order', array(
            'action' => 'index',
        ));
    }
    
    public function resetAction() {
        $logger = $this->getServiceLocator()->get('Logger');

        $breadcrumbService = new Service\BreadcrumbService();
        
        $emptycart = false;

        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');

        $form = new Form\SimpleForm($em);
        $form->get('submit')->setAttributes(array(
            'value' => _('Clear Shopping Cart'),
            'class' => 'btn btn-danger',
        ));

        if ($this->request->isPost()) {
            $form->setData($this->request->getPost());

            if ($form->isValid()) {
                
                $orderService = $this->getServiceLocator()->get('ErsBase\Service\OrderService');
                $order = $orderService->getOrder();        
                
                # TODO: move delete order to OrderService
                foreach($order->getPackages() as $package) {
                    $participant = $package->getUser();
                    if(!$participant->getActive()) {
                        $em->remove($participant);
                    }
                    foreach($package->getItems() as $item) {
                        $em->remove($item);
                    }
                    $em->remove($package);
                }
                $em->remove($order);
                
                $cartContainer = new Container('cart');
                $cartContainer->init = 0;
                $emptycart = true;
            } else {
                $logger->warn($form->getMessages());
            }
        }

        return new ViewModel(array(
            'form' => $form,
            'breadcrumb' => $breadcrumbService->get('cart'),
            'emptycart' => $emptycart,
        ));
    }
}
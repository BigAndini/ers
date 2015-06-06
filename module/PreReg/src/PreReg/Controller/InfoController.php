<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace PreReg\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use PreReg\Service;

class InfoController extends AbstractActionController {
    public function indexAction() {
        return new ViewModel();
    }
    public function termsAction() {
        $forrest = new Service\BreadcrumbFactory;
        if(!$forrest->exists('terms')) {
            $forrest->set('terms', 'home');
        }
        return new ViewModel(array(
            'breadcrumb' => $forrest->get('terms'),
        ));
    }
    public function impressumAction() {
        return new ViewModel();
    }

    public function cookieAction() {
        return new ViewModel();
    }
    public function helpAction() {
        return new ViewModel();
    }
    /*
     * display long description of payment type
     */
    public function paymentAction() {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('product');
        }
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $forrest = new Service\BreadcrumbFactory();
        $breadcrumb = $forrest->get('paymenttype');
        
        return new ViewModel(array(
            'paymenttype' => $em->getRepository("ersEntity\Entity\PaymentType")->findOneBy(array('id' => $id)),
            'breadcrumb' => $breadcrumb,
        ));
    }
}
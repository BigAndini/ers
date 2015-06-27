<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace OnsiteReg\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use OnsiteReg\Form;

class PackageController extends AbstractActionController {
    public function indexAction() {
        $form = new Form\Search();
        return new ViewModel(array(
            'form' => $form,
        ));
    }
    public function detailAction() {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('onsite', array());
        }
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        /* @var $package \ersEntity\Entity\Package */
        $package = $em->getRepository("ersEntity\Entity\Package")
                ->find($id);
        
//        $forrest = new \PreReg\Service\BreadcrumbFactory();
//        $forrest->set('order', 'onsite/package', array('action' => 'detail', 'id' => $id));
//        $forrest->set('user', 'onsite/package', array('action' => 'detail', 'id' => $id));
//        $forrest->set('package', 'onsite/package', array('action' => 'detail', 'id' => $id));
//        $forrest->set('item', 'onsite/package', array('action' => 'detail', 'id' => $id));
        
        $agegroupService = $this->getServiceLocator()->get('PreReg\Service\AgegroupService:ticket');
        $ticketAgegroup = $agegroupService->getAgegroupByUser($package->getParticipant());
        
        $allItemsPaid = $package->getItems()->forAll(function($_, $item){ return $item->getStatus() === 'paid'; });
        
        $form = new Form\ConfirmPackage();
        $form->bind($package);
        
        return new ViewModel(array(
            'package' => $package,
            'order' => $package->getOrder(),
            'ticketAgegroup' => $ticketAgegroup,
            'allItemsPaid' => $allItemsPaid,
            'form' => $form,
        ));
    }
    
    public function shipAction() {
        return $this->redirect()->toRoute('onsite/search');
    }
    
}
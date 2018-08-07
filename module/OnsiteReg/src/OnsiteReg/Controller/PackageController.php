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
        return $this->redirect()->toRoute('onsite');
    }
    
    public function detailAction() {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('onsite', array());
        }
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        /* @var $package \ErsBase\Entity\Package */
        $package = $entityManager->getRepository('ErsBase\Entity\Package')
                ->find($id);
        
        #$forrest = new \ErsBase\Service\BreadcrumbFactory();
        $forrest = new \ErsBase\Service\BreadcrumbService();
//        $forrest->set('order', 'onsite/package', array('action' => 'detail', 'id' => $id));
//        $forrest->set('user', 'onsite/package', array('action' => 'detail', 'id' => $id));
        $forrest->set('package', 'onsite/package', array('action' => 'detail', 'id' => $id));
//        $forrest->set('item', 'onsite/package', array('action' => 'detail', 'id' => $id));
        
        $agegroupService = $this->getServiceLocator()->get('ErsBase\Service\AgegroupService:ticket');
        $ticketAgegroup = $agegroupService->getAgegroupByUser($package->getParticipant());
        
        $unshippedItems = [];
        $shippedItems = [];
        foreach($package->getAllItems() as $item) {
            if($item->getShipped())
                $shippedItems[] = $item;
            else
                $unshippedItems[] = $item;
        }
        
        $form = new Form\ConfirmItems();
        $form->bind($package);
        
        $searchForm = new Form\Search();
        
        $real_roles = $this->getServiceLocator()
                ->get('BjyAuthorize\Provider\Identity\ProviderInterface')->getIdentityRoles();
        
        $roles = array();
        foreach($real_roles as $r) {
            $roles[] = $r->getRoleId();
        }
        
        return new ViewModel(array(
            'package' => $package,
            'shippedItems' => $shippedItems,
            'unshippedItems' => $unshippedItems,
            'order' => $package->getOrder(),
            'ticketAgegroup' => $ticketAgegroup,
            'form' => $form,
            'searchForm' => $searchForm,
            'roles' => $roles,
        ));
    }
    
    public function shipAjaxAction() {
        if(!$this->getRequest()->isPost()) {
            return $this->notFoundAction();
        }
        
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $id = $this->params()->fromRoute('id', 0);
        $package = $entityManager->getRepository('ErsBase\Entity\Package')->find($id);
        if(!$package) {
            return $this->notFoundAction();
        }
            
        $postData = $this->getRequest()->getPost();
        
        $itemId = $postData->itemId;
        $matchedItems = $package->getAllItems()->filter(function($item) use ($itemId){ return $item->getId() === (int)$itemId; });
        if($matchedItems->count() !== 1) {
            return $this->notFoundAction();
        }
        $item = $matchedItems->first();
        
        $itemViewModel = new ViewModel();
        $itemViewModel->setTemplate('partial/package-detail-item');
        $itemViewModel->setVariable('item', $item);
        $itemViewModel->setTerminal('true');
        
        $form = new Form\ConfirmItems();
        $form->setData($postData);
        
        if($form->isValid()) {
            if($item->getShipped()) {
                $itemViewModel->setVariable('error', 'This item has already been shipped!');
                error_log('attempted duplicate shipping of item ' . $item->getId() . ' in package ' . $package->getId() . '!');
                return $itemViewModel;
            }

            #if($item->getStatus()->getValue() !== 'paid') {
            if(!$item->getStatus()->getValid()) {
                $itemViewModel->setVariable('error', 'The item cannot be set to shipped because it is not paid.');
                error_log('attempted shipping with invalid status of item ' . $item->getId() . ' in package ' . $package->getId() . '!');
                return $itemViewModel;
            }

            $item->setShipped(true);
            $item->setShippedDate(new \DateTime());
            $entityManager->persist($item);

            $log = new \ErsBase\Entity\Log();
            $log->setUser($this->zfcUserAuthentication()->getIdentity());
            $log->setData('SHIPPED Item ' . $item->getName() . ' of package ' . $package->getCode()->getValue() . '.');
            $entityManager->persist($log);
            $entityManager->flush();

            # TODO: create log entry for this.
            error_log('set item ' . $item->getId() . ' of package ' . $package->getId() . ' to shipped');
        }
        else {
            $logger = $this->getServiceLocator()->get('Logger');
            $logger->warn($form->getMessages());
            
            $errors = call_user_func_array('array_merge', $form->getMessages());
            $itemViewModel->setVariable('error',
                    implode("\n", $errors)
                    . "\nPlease refresh the page and try again." );
        }
        
        return $itemViewModel;
    }
    
    public function undoItemAction() {
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $packageId = $this->params()->fromRoute('id', 0);
        $itemId = $this->params()->fromRoute('item-id', 0);
        
        $package = $entityManager->getRepository('ErsBase\Entity\Package')->find($packageId);
        
        if(!$package)
            return $this->notFoundAction();
        
        $matchedItems = $package->getAllItems()->filter(function($item) use ($itemId){ return $item->getId() === (int)$itemId; });
        if($matchedItems->count() !== 1) {
            $this->flashMessenger()->addErrorMessage('No item with id ' . $itemId . ' was not found in the package!');
            return $this->redirect()->toRoute('onsite/package', ['action' => 'detail', 'id' => $package->getId()]);
        }
        
        $item = $matchedItems->first();
        $form = new Form\UndoItem();
        
        if($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()->getPost());
            if($form->isValid()) {
                $item->setShipped(false);
                $item->setShippedDate(null);
                $entityManager->persist($item);
                
                $log = new \ErsBase\Entity\Log();
                $log->setUser($this->zfcUserAuthentication()->getIdentity());
                $log->setData('UNSHIPPED Item ' . $item->getName() . ' of package ' . $package->getCode()->getValue() . '.');
                $entityManager->persist($log);
                
                $entityManager->flush();
                
                $this->flashMessenger()->addSuccessMessage('The item was successfully marked as unshipped again!');
                return $this->redirect()->toRoute('onsite/package', ['action' => 'detail', 'id' => $package->getId()]);
            }
        }
        
        return new ViewModel([
            'package' => $package,
            'item' => $item,
            'form' => $form,
        ]);
    }
    
}
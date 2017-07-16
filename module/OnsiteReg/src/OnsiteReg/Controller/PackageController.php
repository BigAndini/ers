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
        
//        $forrest = new \ErsBase\Service\BreadcrumbFactory();
//        $forrest->set('order', 'onsite/package', array('action' => 'detail', 'id' => $id));
//        $forrest->set('user', 'onsite/package', array('action' => 'detail', 'id' => $id));
//        $forrest->set('package', 'onsite/package', array('action' => 'detail', 'id' => $id));
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
    
    public function shipAction() {
        if(!$this->getRequest()->isPost()) {
            return $this->redirect()->toRoute('onsite/search');
        }
        
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $id = $this->params()->fromRoute('id', 0);
        $package = $entityManager->getRepository('ErsBase\Entity\Package')->find($id);
        if(!$package) {
            return $this->notFoundAction();
        }
            
        $postData = $this->getRequest()->getPost();
        
        $form = new Form\ConfirmItems();
        $form->setData($postData);
        
        $toDetailRedirect = $this->redirect()->toRoute('onsite/package', ['action' => 'detail', 'id' => $package->getId()]);
        
        if($form->isValid()) {
            $itemIds = $postData->items;
            
            foreach($itemIds as $itemId) {
                $matchedItems = $package->getAllItems()->filter(function($item) use ($itemId){ return $item->getId() === (int)$itemId; });
                if($matchedItems->count() !== 1) {
                    $this->flashMessenger()->addErrorMessage('No item with id ' . $itemId . ' was not found in the package!');
                    return $toDetailRedirect;
                }
                
                $item = $matchedItems->first();
                
                if($item->getShipped()) {
                    $this->flashMessenger()->addErrorMessage('The item ' . $item->getName() . ' was changed since it was last displayed. Please try again!');
                    return $toDetailRedirect;
                }
                
                if($item->getStatus()->getValue() !== 'paid') {
                    $this->flashMessenger()->addErrorMessage('The item ' . $item->getName() . ' cannot be set to shipped because it is not paid.');
                    return $toDetailRedirect;
                }
                
                $item->setShipped(true);
                $item->setShippedDate(new \DateTime());
                $entityManager->persist($item);
                
                $log = new \ErsBase\Entity\Log();
                $log->setUser($this->zfcUserAuthentication()->getIdentity());
                $log->setData('SHIPPED Item ' . $item->getName() . ' of package ' . $package->getCode()->getValue() . '.');
                $entityManager->persist($log);
                
                # TODO: create log entry for this.
                error_log('set item ' . $item->getId() . ' of package ' . $package->getId() . ' to shipped');
            }
            $entityManager->flush();
            
            $this->flashMessenger()->addSuccessMessage('The items were successfully marked as shipped!');
        }
        else {
            $logger = $this->getServiceLocator()->get('Logger');
            $logger->warn($form->getMessages());
            
            foreach(call_user_func_array('array_merge', $form->getMessages()) as $error) {
                $this->flashMessenger()->addErrorMessage($error);
            }
        }
        
        return $toDetailRedirect;
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
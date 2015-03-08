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
use PreReg\Form;
use ersEntity\Entity;
use PreReg\Service;
use PreReg\InputFilter;

class PurchaserController extends AbstractActionController {
    public function indexAction()
    {
        return $this->notFoundAction();
    }
    
    
    /*
     * add a purchaser user object to the session when none of the participants 
     * is the purchaser
     */
    public function addAction() {
        $form = new Form\Purchaser(); 
        $request = $this->getRequest(); 

        $forrest = new Service\BreadcrumbFactory();
        if($request->isPost()) 
        { 
            $user = new Entity\User();

            $inputFilter = new InputFilter\Purchaser();
            $form->setInputFilter($inputFilter->getInputFilter()); 
            $form->setData($request->getPost()); 
            
            error_log('before isValid');
            if($form->isValid())
            { 
                error_log('in isValid');
                $user->populate($form->getData()); 
                $cartContainer = new Container('cart');
                $cartContainer->order->addParticipant($user);
                $cartContainer->order->setPurchaser($user);
                
                $breadcrumb = $forrest->get('purchaser');

                return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
            } else {
                error_log(var_export($form->getMessages(), true));
            } 
            error_log('after isValid');
        }
        
        return new ViewModel(array(
            'form' => $form,
            'breadcrumb' => $forrest->get('purchaser'),
        ));
    }
    
    /*
     * edit a participant which is already added to this session or for which 
     * this user already bought a product. This user is only able to edit the 
     * details if the participant himself hasn't logged in to his account, yet.
     */
    public function editAction() 
    {
        $forrest = new Service\BreadcrumbFactory();
        $breadcrumb = $forrest->get('purchaser');
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
        }
        
        $cartContainer = new Container('cart');
        $participant = $cartContainer->order->getParticipantBySessionId($id);
        
        $form = new Form\Purchaser(); 
        $request = $this->getRequest(); 
        
        $form->bind($participant);
        
        if($request->isPost()) 
        {
            $form->setInputFilter($form->getInputFilter()); 
            $form->setData($request->getPost()); 
                
            if($form->isValid())
            { 
                $participant = $form->getData();
                $cartContainer = new Container('cart');
                $cartContainer->order->setParticipantBySessionId($participant, $id);
                
                $breadcrumb = $forrest->get('participant');
                return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
            } else {
                error_log(var_export($form->getMessages(), true));
            } 
        }
        
        $breadcrumb = $forrest->get('purchaser');
        return new ViewModel(array(
            'id' => $id,
            'form' => $form,
            'breadcrumb' => $breadcrumb,
        ));
    }
    
    public function deleteAction() {
        # maybe we do not need to delete a participant here, because the 
        # participants user object is only held in the session and will be 
        # deleted after session is not valid anymore.
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('participant');
        }
        
        $forrest = new Service\BreadcrumbFactory();
        if(!$forrest->exists('participant')) {
            $forrest->set('participant', 'participant');
        }
        
        $breadcrumb = $forrest->get('participant');
        
        $cartContainer = new Container('cart');
        $participant = $cartContainer->order->getParticipantBySessionId($id);
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');

            if ($del == 'Yes') {
                $id = (int) $request->getPost('id');
                
                $cartContainer->order->removeParticipantBySessionId($id);
            }

            return $this->redirect()->toRoute(
                    $breadcrumb->route, 
                    $breadcrumb->params, 
                    $breadcrumb->options
                );
        }

        $package = $cartContainer->order->getPackageByParticipantSessionId($id);
        
        
        return new ViewModel(array(
            'id'    => $id,
            'participant' => $participant,
            'package' => $package,
            'breadcrumb' => $breadcrumb,
        ));
    }
}
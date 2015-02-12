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

class ParticipantController extends AbstractActionController {
    /*
     * - Show list of participants of this session
     * - inclufde participant for which this user already bought products, if 
     *   the user is logged in.
     */
    public function indexAction()
    {
        $session_cart = new Container('cart');
        $participants = $session_cart->order->getParticipants();
        
        $context = new Container('context');
        $context->route = 'participant';
        $context->params = array();
        $context->options = array();
        
        return new ViewModel(array(
            'participants' => $participants,
        ));
    }
    
    /*
     * add a participant user object to the session for which the purchaser is 
     * able to assign a product afterwards.
     */
    public function addAction() {
        
        $form = new Form\ParticipantForm(); 
        $request = $this->getRequest(); 

        if($request->isPost()) 
        { 
            $user = new Entity\User();


            $form->setInputFilter($form->getInputFilter()); 
            $form->setData($request->getPost()); 
                
            if($form->isValid())
            { 
                $user->populate($form->getData()); 
                $session_cart = new Container('cart');
                $session_cart->order->addParticipant($user);
                
                error_log('adding participant to order.');
                
                $context = new Container('context');
                if(isset($context->route)) {
                    return $this->redirect()->toRoute($context->route, $context->params, $context->options);
                } else {
                    return $this->redirect()->toRoute('participant');
                }
                
            } else {
                error_log(var_export($form->getMessages()));
            } 
        }
        
        $context = new Container('context');
        if(empty($context->route)) {
            $context->route = 'participant';
            $context->params = array();
            $context->options = array();
        }
        return [
            'form' => $form,
            'context' => $context,
        ];
    }
    
    /*
     * edit a participant which is already added to this session or for which 
     * this user already bought a product. This user is only able to edit the 
     * details if the participant himself hasn't logged in to his account, yet.
     */
    public function editAction() 
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('participant', array(
                'action' => 'add'
            ));
        }
        
        $session_cart = new Container('cart');
        $participant = $session_cart->order->getParticipantBySessionId($id);
        
        $form = new Form\ParticipantForm(); 
        $request = $this->getRequest(); 
        
        $form->bind($participant);
        
        if($request->isPost()) 
        {
            $form->setInputFilter($form->getInputFilter()); 
            $form->setData($request->getPost()); 
                
            if($form->isValid())
            { 
                #$participant->populate($form->getData());
                $participant = $form->getData();
                $session_cart = new Container('cart');
                $session_cart->order->setParticipantBySessionId($participant, $id);
                
                $context = new Container('context');
                if(isset($context->route)) {
                    return $this->redirect()->toRoute($context->route, $context->params, $context->options);
                } else {
                    return $this->redirect()->toRoute('participant');
                }
                
            } else {
                error_log(var_export($form->getMessages()));
            } 
        }
        
        $context = new Container('context');
        if(empty($context->route)) {
            $context->route = 'participant';
            $context->params = array();
            $context->options = array();
        }
        return [
            'id' => $id,
            'form' => $form,
            'context' => $context,
        ];
    }
    
    public function deleteAction() {
        # maybe we do not need to delete a participant here, because the 
        # participants user object is only held in the session and will be 
        # deleted after session is not valid anymore.
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('participant');
        }
        
        $context = new Container('context');
        if(empty($context->route)) {
            $context->route = 'participant';
            $context->params = array();
            $context->options = array();
        }
        
        $session_cart = new Container('cart');
        $participant = $session_cart->order->getParticipantBySessionId($id);
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');

            if ($del == 'Yes') {
                $id = (int) $request->getPost('id');
                
                $participant = $session_cart->order->removeParticipantBySessionId($id);
            }

            #return $this->redirect()->toRoute('participant');
            return $this->redirect()->toRoute($context->route, $context->params, $context->options);
        }

        $package = $session_cart->order->getPackageByParticipantSessionId($id);
        
        
        return array(
            'id'    => $id,
            'participant' => $participant,
            'package' => $package,
            'context' => $context,
        );
    }
}
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
    /*protected $table;
    
    public function getTable($name)
    {
        if (!isset($this->table[$name])) {
            $sm = $this->getServiceLocator();
            $className = "PreReg\Model\\".$name."Table";
            $this->table[$name] = $sm->get($className);
            $this->table[$name]->setServiceLocator($sm);
        }
        return $this->table[$name];
    }*/
    
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
                error_log('Form is not valid!');
                $messages = $form->getMessages();
                error_log('got '.count($messages).' messages.');
                foreach($messages as $m) {
                    if(is_array($m)) {
                        foreach($m as $n) {
                            error_log($n);
                        }
                    } else {
                        error_log($m);
                    }
                }
            } 
        }
        
        $context = new Container('context');
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
    public function editAction() {
        
    }
    
    public function deleteAction() {
        # maybe we do not need to delete a participant here, because the 
        # participants user object is only held in the session and will be 
        # deleted after session is not valid anymore.
    }
}
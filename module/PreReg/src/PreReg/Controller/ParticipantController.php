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
        $clearance = new Container('forrest');
        $clearance->getManager()->getStorage()->clear('forrest');
        $forrest = new Container('forrest');
        $forrest->trace = new \ArrayObject();
        
        $session_cart = new Container('cart');
        $participants = $session_cart->order->getParticipants();
        
        $breadcrumb = new \ArrayObject();
        $breadcrumb->route = 'participant';
        $breadcrumb->params = new \ArrayObject();
        $breadcrumb->options = new \ArrayObject();
        $forrest->trace->participant = $breadcrumb;
        
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
                
                $forrest = new Container('forrest');
                $breadcrumb = $forrest->trace->participant;
                error_log('route: '.$breadcrumb->route);
                error_log('action: '.$breadcrumb->params->action);
                if($breadcrumb->route == 'product' && $breadcrumb->params->action == 'view') {
                    $breadcrumb->params->participant_id = $user->getSessionId();
                }
                return $this->redirect()->toRoute(
                    $breadcrumb->route, 
                    $breadcrumb->params->getArrayCopy(), 
                    $breadcrumb->options->getArrayCopy()
                );
            } else {
                error_log(var_export($form->getMessages()));
            } 
        }
        
        $forrest = new Container('forrest');
        if(!isset($forrest->trace->participant)) {
            $breadcrumb = new \ArrayObject();
            $breadcrumb->route = 'participant';
            $breadcrumb->params = new \ArrayObject();
            $breadcrumb->options = new \ArrayObject();
            $forrest->trace->participant = $breadcrumb;
        }

        return [
            'form' => $form,
            'forrest' => $forrest,
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
                $participant = $form->getData();
                $session_cart = new Container('cart');
                $session_cart->order->setParticipantBySessionId($participant, $id);
                
                $forrest = new Container('forrest');
                $breadcrumb = $forrest->trace->participant;
                return $this->redirect()->toRoute(
                    $breadcrumb->route, 
                    $breadcrumb->params->getArrayCopy(), 
                    $breadcrumb->options->getArrayCopy()
                );
            } else {
                error_log(var_export($form->getMessages()));
            } 
        }
        
        $forrest = new Container('forrest');
        if(!isset($forrest->trace->participant)) {
            $breadcrumb = ArrayObject();
            $breadcrumb->route = 'participant';
            $breadcrumb->params = new \ArrayObject();
            $breadcrumb->options = new \ArrayObject();
            $forrest->trace->participant = $breadcrumb;
        }
        return [
            'id' => $id,
            'form' => $form,
            'forrest' => $forrest,
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
        
        $forrest = new Container('forrest');
        if(!isset($forrest->trace->participant)) {
            $breadcrumb = new \ArrayObject();
            $breadcrumb->route = 'participant';
            $breadcrumb->params = new \ArrayObject();
            $breadcrumb->options = new \ArrayObject();
            $forrest->trace->participant = $breadcrumb;
        }
        
        $session_cart = new Container('cart');
        $participant = $session_cart->order->getParticipantBySessionId($id);
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');

            if ($del == 'Yes') {
                $id = (int) $request->getPost('id');
                
                $session_cart->order->removeParticipantBySessionId($id);
            }

            $forrest = new Container('forrest');
            $breadcrumb = $forrest->trace->participant;
            return $this->redirect()->toRoute(
                    $breadcrumb->route, 
                    $breadcrumb->params->getArrayCopy(), 
                    $breadcrumb->options->getArrayCopy()
                );
        }

        $package = $session_cart->order->getPackageByParticipantSessionId($id);
        
        
        return array(
            'id'    => $id,
            'participant' => $participant,
            'package' => $package,
            'forrest' => $forrest,
        );
    }
}
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

class ParticipantController extends AbstractActionController {
    /*
     * - Show list of participants of this session
     * - inclufde participant for which this user already bought products, if 
     *   the user is logged in.
     */
    public function indexAction()
    {
        $forrest = new Service\BreadcrumbFactory(); 
        $forrest->reset();
        $forrest->set('participant', 'participant');
        
        $session_cart = new Container('cart');
        $participants = $session_cart->order->getParticipants();
       
        return new ViewModel(array(
            'participants' => $participants,
        ));
    }
    
    /*
     * add a participant user object to the session for which the purchaser is 
     * able to assign a product afterwards.
     */
    public function addAction() {
        $form = new Form\Participant(); 
        $request = $this->getRequest(); 

        $forrest = new Service\BreadcrumbFactory();
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
                
                $breadcrumb = $forrest->get('participant');
                if($breadcrumb->route == 'product' && ($breadcrumb->params['action'] == 'add' || $breadcrumb->params['action'] == 'edit')) {
                    $breadcrumb->params['participant_id'] = $user->getSessionId();
                }

                return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
            } else {
                error_log(var_export($form->getMessages(), true));
            } 
        }
        
        if(!$forrest->exists('participant')) {
            $forrest->set('participant', 'participant');
        }

        return [
            'form' => $form,
            'breadcrumb' => $forrest->get('participant'),
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
        
        $form = new Form\Participant(); 
        $request = $this->getRequest(); 
        $forrest = new Service\BreadcrumbFactory();
        
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
                
                $breadcrumb = $forrest->get('participant');
                return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
            } else {
                error_log(var_export($form->getMessages(), true));
            } 
        }
        
        if(!$forrest->exists('participant')) {
            $forrest->set('participant', 'participant');
        }
        $breadcrumb = $forrest->get('participant');
        return [
            'id' => $id,
            'form' => $form,
            'breadcrumb' => $breadcrumb,
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
        
        $forrest = new Service\BreadcrumbFactory();
        if(!$forrest->exists('participant')) {
            $forrest->set('participant', 'participant');
        }
        
        $breadcrumb = $forrest->get('participant');
        
        $session_cart = new Container('cart');
        $participant = $session_cart->order->getParticipantBySessionId($id);
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');

            if ($del == 'Yes') {
                $id = (int) $request->getPost('id');
                
                $session_cart->order->removeParticipantBySessionId($id);
            }

            return $this->redirect()->toRoute(
                    $breadcrumb->route, 
                    $breadcrumb->params, 
                    $breadcrumb->options
                );
        }

        $package = $session_cart->order->getPackageByParticipantSessionId($id);
        
        
        return array(
            'id'    => $id,
            'participant' => $participant,
            'package' => $package,
            'breadcrumb' => $breadcrumb,
        );
    }
}
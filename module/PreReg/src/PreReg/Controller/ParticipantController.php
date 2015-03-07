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
    
    private function getCountryOptions($countryId = null) {
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $qb = $em->getRepository("ersEntity\Entity\Country")->createQueryBuilder('n');
        $countries = $qb->orderBy('n.ordering', 'ASC')->getQuery()->getResult();
        /*$paymenttypes = $qb->where(
                $qb->expr()->orX(
                    $qb->expr()->eq('n.activeFrom_id', $id),
                    $qb->expr()->eq('n.activeUntil_id', $id)
            ))->getQuery()->getResult();*/
        /*$countries = $em->getRepository("ersEntity\Entity\Country")
                ->findBy(array(), array('ordering' => 'ASC', 'name' => 'ASC'));*/
        $options = array();
        foreach($countries as $country) {
            $selected = false;
            if($countryId == $country->getId()) {
                $selected = true;
            }
            $options[] = array(
                'value' => $country->getId(),
                'label' => $country->getName(),
                'selected' => $selected,
            );
        }
        /*$selected = false;
        if($countryId == null) {
            $selected = true;
        }
        $options[] = array(
            'value' => 0,
            'label' => 'no Country',
            'selected' => $selected,
        );*/
        return $options;
    }
    
    /*
     * add a participant user object to the session for which the purchaser is 
     * able to assign a product afterwards.
     */
    public function addAction() {
        $form = new Form\Participant(); 
        $request = $this->getRequest(); 

        $form->get('Country_id')->setValueOptions($this->getCountryOptions());
        
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

        return new ViewModel(array(
            'form' => $form,
            'breadcrumb' => $forrest->get('participant'),
        ));
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
        
        
        return new ViewModel(array(
            'id'    => $id,
            'participant' => $participant,
            'package' => $package,
            'breadcrumb' => $breadcrumb,
        ));
    }
}
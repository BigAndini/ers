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
        
        $cartContainer = new Container('cart');
        $participants = $cartContainer->order->getParticipants();
       
        return new ViewModel(array(
            'participants' => $participants,
        ));
    }
    
    private function getCountryOptions($countryId = null) {
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $qb1 = $em->getRepository("ersEntity\Entity\Country")->createQueryBuilder('n');
        $qb1->where($qb1->expr()->isNotNull('n.ordering'));
        $qb1->orderBy('n.ordering', 'ASC');
        $result1 = $qb1->getQuery()->getResult();
        
        $qb2 = $em->getRepository("ersEntity\Entity\Country")->createQueryBuilder('n');
        $qb2->where($qb2->expr()->isNull('n.ordering'));
        $qb2->orderBy('n.name', 'ASC');
        $result2 = $qb2->getQuery()->getResult();

        $countries = array_merge($result1, $result2);

        $cartContainer = new Container('cart');
        $countryContainerId = $cartContainer->Country_id;
        
        $options = array();
        $selected = false;
        if($countryId == null && $countryContainerId == null) {
            $selected = true;
        }
        $options[] = array(
            'value' => 0,
            'label' => 'no Country',
            'selected' => $selected,
        );
        foreach($countries as $country) {
            $selected = false;
            if($countryContainerId == $country->getId()) {
                $selected = true;
            }
            if($countryId == $country->getId()) {
                $selected = true;
            }
            $options[] = array(
                'value' => $country->getId(),
                'label' => $country->getName(),
                'selected' => $selected,
            );
        }
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

            $inputFilter = new InputFilter\Participant();

            $form->setInputFilter($inputFilter->getInputFilter()); 
            $form->setData($request->getPost()); 
            
            if($form->isValid())
            { 
                $user->populate($form->getData()); 
                $cartContainer = new Container('cart');
                $cartContainer->order->addParticipant($user);
                $cartContainer->Country_id = $user->getCountryId();
                
                if($user->getCountryId() == 0) {
                    $user->setCountryId(null);
                }
                
                $breadcrumb = $forrest->get('participant');
                if($breadcrumb->route == 'product' && ($breadcrumb->params['action'] == 'add' || $breadcrumb->params['action'] == 'edit')) {
                    $breadcrumb->params['participant_id'] = $user->getSessionId();
                }

                return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
            } else {
                $logger = $this
                    ->getServiceLocator()
                    ->get('Logger');
                $logger->warn($form->getMessages());
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
        
        $cartContainer = new Container('cart');
        $participant = $cartContainer->order->getParticipantBySessionId($id);
        
        $form = new Form\Participant(); 
        $request = $this->getRequest(); 
        $forrest = new Service\BreadcrumbFactory();
        
        $form->get('Country_id')->setValueOptions($this->getCountryOptions());
        
        $form->bind($participant);
        
        if($request->isPost()) 
        {
            $inputFilter = new InputFilter\Participant();
            $form->setInputFilter($inputFilter->getInputFilter()); 
            $form->setData($request->getPost()); 
                
            if($form->isValid())
            { 
                $participant = $form->getData();
                $cartContainer = new Container('cart');
                $cartContainer->order->setParticipantBySessionId($participant, $id);
                
                if($participant->getCountryId() == 0) {
                    $participant->setCountryId(null);
                }
                
                $breadcrumb = $forrest->get('participant');
                return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
            } else {
                $logger = $this
                    ->getServiceLocator()
                    ->get('Logger');
                $logger->warn($form->getMessages());
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
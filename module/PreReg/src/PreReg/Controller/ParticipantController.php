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
use ErsBase\Entity;
use ErsBase\Service;
use PreReg\InputFilter;

class ParticipantController extends AbstractActionController {
    /*
     * - Show list of participants of this session
     * - inclufde participant for which this user already bought products, if 
     *   the user is logged in.
     */
    public function indexAction()
    {
        $breadcrumbService = new Service\BreadcrumbService(); 
        $breadcrumbService->reset();
        $breadcrumbService->set('participant', 'participant');
     
        $orderService = $this->getServiceLocator()
                ->get('ErsBase\Service\OrderService');
        $order = $orderService->getOrder();
        
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $participants = $order->getParticipants();
        
        foreach($participants as $participant) {
            if($participant->getCountryId()) {
                $country = $em->getRepository('ErsBase\Entity\Country')
                        ->findOneBy(array('id' => $participant->getCountryId()));
                $participant->setCountry($country);
            }
        }
        
        return new ViewModel(array(
            'participants' => $participants,
        ));
    }
    
    /*
     * add a participant user object to the session for which the buyer is 
     * able to assign a product afterwards.
     */
    public function addAction() {
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $form = new Form\Participant(); 
        $form->setServiceLocator($this->getServiceLocator());
        $optionService = $this->getServiceLocator()
                ->get('ErsBase\Service\OptionService');
        $form->get('Country_id')->setValueOptions($optionService->getCountryOptions());
        
        $user = new Entity\User();
        $user->setActive(false);
        $form->bind($user);
        
        $breadcrumbService = new Service\BreadcrumbService();
        
        $request = $this->getRequest(); 
        if($request->isPost()) 
        { 
            #$inputFilter = new InputFilter\Participant();
            #$inputFilter->setEntityManager($em);

            #$form->setInputFilter($inputFilter->getInputFilter()); 
            $form->setData($request->getPost()); 
            
            if($form->isValid())
            { 
                $orderService = $this->getServiceLocator()
                    ->get('ErsBase\Service\OrderService');
                $order = $orderService->getOrder();
                
                $participant = $em->getRepository('ErsBase\Entity\User')
                        ->findOneBy(array('email' => $user->getEmail(), 'active' => false));
                
                if($participant) {
                    $participant->loadData($user);
                    $em->persist($participant);
                    $orderService->addParticipant($participant);
                    #$em->persist($participant);
                } else {
                    $active_user = $em->getRepository('ErsBase\Entity\User')
                        ->findOneBy(array('email' => $user->getEmail(), 'active' => true));
                    
                    if($active_user) {
                        # TODO: flash error message: login is needed
                    } else {
                        #$em->persist($user);
                        $orderService->addParticipant($user);
                    }   
                }
                
                $orderService->setCountryId($user->getCountryId());
                
                if($user->getCountryId() == 0) {
                    $user->setCountryId(null);
                    $user->setCountry(null);
                }
                
                $em->persist($order);
                $em->flush();
                
                $breadcrumb = $breadcrumbService->get('participant');
                if($breadcrumb->route == 'product' && isset($breadcrumb->params['action']) && ($breadcrumb->params['action'] == 'add' || $breadcrumb->params['action'] == 'edit')) {
                    unset($breadcrumb->params['agegroup_id']);
                    $breadcrumb->options['fragment'] = 'person';
                    $breadcrumb->options['query']['participant_id'] = $user->getId();
                }

                return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
            } else {
                $logger = $this->getServiceLocator()->get('Logger');
                $logger->warn($form->getMessages());
            } 
        }
        
        if(!$breadcrumbService->exists('participant')) {
            $breadcrumbService->set('participant', 'participant');
        }

        return new ViewModel(array(
            'form' => $form,
            'breadcrumb' => $breadcrumbService->get('participant'),
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
        
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $orderService = $this->getServiceLocator()
                ->get('ErsBase\Service\OrderService');
        $order = $orderService->getOrder();
        
        $participant = $order->getParticipantById($id);
        
        if(!$participant) {
            # TODO: add flash messenger message with error and return
        }
        
        $breadcrumbService = new Service\BreadcrumbService();
        
        $form = new Form\Participant();
        $form->setServiceLocator($this->getServiceLocator());
        $optionService = $this->getServiceLocator()
                ->get('ErsBase\Service\OptionService');
        $form->get('Country_id')->setValueOptions($optionService->getCountryOptions());
        $form->bind($participant);
        
        $request = $this->getRequest(); 
        if($request->isPost()) 
        {
            $form->setData($request->getPost()); 
                
            if($form->isValid())
            { 
                if($participant->getCountryId() == 0) {
                    $participant->setCountryId(null);
                    $participant->setCountry(null);
                }
                
                $em->persist($participant);
                $em->flush();
                
                $breadcrumb = $breadcrumbService->get('participant');
                return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
            } else {
                $logger = $this->getServiceLocator()->get('Logger');
                $logger->warn($form->getMessages());
            } 
        }
        
        if(!$breadcrumbService->exists('participant')) {
            $breadcrumbService->set('participant', 'participant');
        }
        $breadcrumb = $breadcrumbService->get('participant');
        return new ViewModel(array(
            'id' => $id,
            'form' => $form,
            'breadcrumb' => $breadcrumb,
        ));
    }
    
    public function deleteAction() {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('participant');
        }
        
        $breadcrumbService = new Service\BreadcrumbService();
        if(!$breadcrumbService->exists('participant')) {
            $breadcrumbService->set('participant', 'participant');
        }
        
        $breadcrumb = $breadcrumbService->get('participant');
        
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        /*$participant = $em->getRepository('ErsBase\Entity\User')
                ->findOneBy(array('id' => $id));*/
        
        $orderService = $this->getServiceLocator()
                ->get('ErsBase\Service\OrderService');
        $order = $orderService->getOrder();
        
        $participant = $order->getParticipantById($id);
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');

            if ($del == 'Yes') {
                $id = (int) $request->getPost('id');
        
                $participant = $order->getParticipantById($id);
                
                $orderService->removeParticipant($participant);
            }

            return $this->redirect()->toRoute(
                    $breadcrumb->route, 
                    $breadcrumb->params, 
                    $breadcrumb->options
                );
        }

        $package = $order->getPackageByParticipantId($id);
        
        return new ViewModel(array(
            'id'    => $id,
            'participant' => $participant,
            'package' => $package,
            'breadcrumb' => $breadcrumb,
        ));
    }
}
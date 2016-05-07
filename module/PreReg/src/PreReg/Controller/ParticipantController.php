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
     
        $orderService = $this->getServiceLocator()->get('ErsBase\Service\OrderService');
        $order = $orderService->getOrder();
        
        $em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        
        $participants = $order->getParticipants();
        
        foreach($participants as $participant) {
            if ($participant->getCountryId()) {
                $country = $em->getRepository('ErsBase\Entity\Country')
                        ->findOneBy(['id' => $participant->getCountryId()]);
                $participant->setCountry($country);
            }
        }
        
        return new ViewModel([
            'participants' => $participants,
        ]);
    }
    
    /*
     * add a participant user object to the session for which the buyer is 
     * able to assign a product afterwards.
     */
    public function addAction() {
        $form = new Form\Participant();
        $form->setServiceLocator($this->getServiceLocator());
        $optionService = $this->getServiceLocator()->get('ErsBase\Service\OptionService');
        $form->get('Country_id')->setValueOptions($optionService->getCountryOptions());

        $user = new Entity\User();
        $user->setActive(false);
        $form->bind($user);

        $breadcrumbService = new Service\BreadcrumbService();

        $request = $this->getRequest();
        if ($request->isPost())
        {
            $form->setData($request->getPost());

            if ($form->isValid())
            {
                $orderService = $this->getServiceLocator()->get('ErsBase\Service\OrderService');
                $order = $orderService->getOrder();

                $em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');

                if ($user->getCountryId() == 0) {
                    $user->setCountryId(null);
                    $user->setCountry(null);
                } else {
                    $country = $em->getRepository('ErsBase\Entity\Country')
                        ->findOneBy(array('id' => $user->getCountryId()));
                    $user->setCountryId($country->getId());
                    $user->setCountry($country);
                }
                
                if (!$user->getEmail()) {
                    // No email address was entered. Treat as a new participant.
                    $orderService->addParticipant($user);
                } else {
                    $existing_user = $em->getRepository('ErsBase\Entity\User')
                        ->findOneBy(['email' => $user->getEmail()]);

                    if ($existing_user && !$existing_user->getActive()) {
                        // Re-use the existing participant and add them to the current order
                        $existing_user->loadData($user);
                        $em->persist($existing_user);
                        $orderService->addParticipant($existing_user);
                    } elseif ($existing_user && $existing_user->getActive()) {
                        throw new \Exception("This email address belongs to a registered user. Please log in.");
                    } else {
                        // This email address is new. Make a regular participant out of it.
                        $orderService->addParticipant($user);
                    }
                }

                $orderService->setCountryId($user->getCountryId());
                
                $em->persist($order);
                $em->flush();
                
                $breadcrumb = $breadcrumbService->get('participant');
                if (
                    $breadcrumb->route == 'product' &&
                    isset($breadcrumb->params['action']) &&
                    ($breadcrumb->params['action'] == 'add' || $breadcrumb->params['action'] == 'edit')
                ) {
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
        
        if (!$breadcrumbService->exists('participant')) {
            $breadcrumbService->set('participant', 'participant');
        }

        return new ViewModel([
            'form' => $form,
            'breadcrumb' => $breadcrumbService->get('participant'),
        ]);
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
            return $this->redirect()->toRoute('participant', [
                'action' => 'add'
            ]);
        }
        
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $orderService = $this->getServiceLocator()
                ->get('ErsBase\Service\OrderService');
        $order = $orderService->getOrder();
        
        $participant = $order->getParticipantById($id);
        
        if (!$participant) {
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
        if ($request->isPost()) {
            $form->setData($request->getPost()); 
                
            if ($form->isValid()) { 
                if ($participant->getCountryId() == 0) {
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
        
        if (!$breadcrumbService->exists('participant')) {
            $breadcrumbService->set('participant', 'participant');
        }
        $breadcrumb = $breadcrumbService->get('participant');
        return new ViewModel([
            'id' => $id,
            'form' => $form,
            'breadcrumb' => $breadcrumb,
        ]);
    }
    
    public function deleteAction() {
        $logger = $this->getServiceLocator()->get('Logger');

        $breadcrumbService = new Service\BreadcrumbService();

        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('participant');
        }

        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');

        $form = new Form\SimpleForm($em);
        $form->get('submit')->setAttributes(array(
            'value' => 'Delete',
            'class' => 'btn btn-danger',
        ));
        
        $orderService = $this->getServiceLocator()->get('ErsBase\Service\OrderService');
        $order = $orderService->getOrder();
        
        $participant = $order->getParticipantById($id);

        $form->bind($participant);

        if ($this->request->isPost()) {
            $form->setData($this->request->getPost());

            if ($form->isValid()) {
                $orderService->removeParticipant($participant);

                $breadcrumb = $breadcrumbService->get('participant');
                return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
            } else {
                $logger->warn($form->getMessages());
            }
        }
        
        $package = $order->getPackageByParticipantId($id);

        return new ViewModel(array(
            'form' => $form,
            'package' => $package,
            'breadcrumb' => $breadcrumbService->get('participant'),
        ));
    }
}
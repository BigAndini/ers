<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use ErsBase\Entity;
use Admin\Form;
use ErsBase\Service;
use Admin\InputFilter;

class UserController extends AbstractActionController {
    
    public function indexAction()
    {
        $forrest = new Service\BreadcrumbService();
        $forrest->set('user', 'admin/user');
        
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        return new ViewModel(array(
            'users' => $em->getRepository('ErsBase\Entity\User')->findAll(),
         ));
    }

    private function getCountryOptions($countryId = null) {
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $qb1 = $em->getRepository('ErsBase\Entity\Country')->createQueryBuilder('n');
        $qb1->where($qb1->expr()->isNotNull('n.position'));
        $qb1->orderBy('n.position', 'ASC');
        $result1 = $qb1->getQuery()->getResult();
        
        $qb2 = $em->getRepository('ErsBase\Entity\Country')->createQueryBuilder('n');
        $qb2->where($qb2->expr()->isNull('n.position'));
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
    
    public function addAction()
    {
        $forrest = new Service\BreadcrumbService();
        if(!$forrest->exists('user')) {
            $forrest->set('user', 'admin/user');
        }
        $breadcrumb = $forrest->get('user');
        
        #$form = new Form\User();
        $form = $this->getServiceLocator()->get('Admin\Form\User');
        $form->get('submit')->setValue('Add');
        $form->get('Country_id')->setValueOptions($this->getCountryOptions());
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $user = new Entity\User();
            
            #$form->setInputFilter($user->getInputFilter());
            $inputFilter = $this->getServiceLocator()
                    ->get('Admin\InputFilter\User');
            $form->setInputFilter($inputFilter->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $user->populate($form->getData());
                
                if($user->getEmail() == '') {
                    $user->setEmail(null);
                }
                
                if($user->getCountryId() == 0) {
                    $user->setCountry(null);
                    $user->setCountryId(null);
                }
                
                $user->setActive(true);
                
                $em = $this->getServiceLocator()
                    ->get('Doctrine\ORM\EntityManager');
                
                $em->persist($user);
                $em->flush();
                
                if(array_key_exists('q', $breadcrumb->options['query'])) {
                    $breadcrumb->options['query']['q'] = $user->getEmail();
                }

                return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
            } else {
                $logger = $this->getServiceLocator()->get('Logger');
                $logger->warn($form->getMessages());
            }
        }
        
        return new ViewModel(array(
            'form' => $form,                
        ));
    }

    public function editAction()
    {
        $forrest = new Service\BreadcrumbService();
        if(!$forrest->exists('user')) {
            $forrest->set('user', 'admin/user');
        }
        $breadcrumb = $forrest->get('user');
        
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/user', array(
                'action' => 'add'
            ));
        }
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $user = $em->getRepository('ErsBase\Entity\User')->findOneBy(array('id' => $id));
        
        $form = new Form\User($this->getServiceLocator());
        $form->bind($user);
        $form->get('submit')->setAttribute('value', 'Edit');
        $form->get('Country_id')->setValueOptions($this->getCountryOptions());

        $request = $this->getRequest();
        if ($request->isPost()) {
            $inputFilter = $this->getServiceLocator()
                    ->get('Admin\InputFilter\User');
            #$form->setInputFilter($inputFilter->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $user = $form->getData();
                if($user->getEmail() == '') {
                    $user->setEmail(NULL);
                }
                if($user->getCountryId() == 0) {
                    $user->setCountry(null);
                    $user->setCountryId(null);
                }
                $em->persist($user);
                $em->flush();

                return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
            } else {
                $logger = $this->getServiceLocator()->get('Logger');
                $logger->warn($form->getMessages());
            }
        }

        return new ViewModel(array(
            'id' => $id,
            'form' => $form,
            'breadcrumb' => $breadcrumb,
        ));
    }

    public function deleteAction()
    {
        $forrest = new Service\BreadcrumbService();
        if(!$forrest->exists('user')) {
            $forrest->set('user', 'admin/user');
        }
        $breadcrumb = $forrest->get('user');
        
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            #return $this->redirect()->toRoute('admin/user');
            return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
        }
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $user = $em->getRepository('ErsBase\Entity\User')
                ->findOneBy(array('id' => $id));

        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');

            if ($del == 'Yes') {
                $id = (int) $request->getPost('id');
                $user = $em->getRepository('ErsBase\Entity\User')
                    ->findOneBy(array('id' => $id));
                $em->remove($user);
                $em->flush();
            }

            return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
            #return $this->redirect()->toRoute('admin/user');
        }

        return new ViewModel(array(
            'id'    => $id,
            'user' => $user,
            'breadcrumb' => $breadcrumb,
        ));
    }
    
    public function roleAction() {
        return new ViewModel(array(
            'id' => $id,
            'form' => $form,
            'breadcrumb' => $breadcrumb,
        ));
    }
}
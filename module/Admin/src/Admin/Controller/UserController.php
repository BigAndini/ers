<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use ersEntity\Entity;
use Admin\Form;
use Admin\Service;
use Admin\InputFilter;

class UserController extends AbstractActionController {
    
    public function indexAction()
    {
        $forrest = new Service\BreadcrumbFactory();
        $forrest->set('user', 'admin/user');
        
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        return new ViewModel(array(
            'users' => $em->getRepository("ersEntity\Entity\User")->findAll(),
         ));
    }

    public function addAction()
    {
        $forrest = new Service\BreadcrumbFactory();
        if(!$forrest->exists('user')) {
            $forrest->set('user', 'admin/user');
        }
        $breadcrumb = $forrest->get('user');
        
        $form = new Form\User();
        $form->get('submit')->setValue('Add');
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $user = new Entity\User();
            
            $form->setInputFilter($user->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $user->populate($form->getData());
                
                $em = $this
                    ->getServiceLocator()
                    ->get('Doctrine\ORM\EntityManager');
                
                $em->persist($user);
                $em->flush();

                return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
            } else {
                $logger = $this
                    ->getServiceLocator()
                    ->get('Logger');
                $logger->warn($form->getMessages());
            }
        }
        
        return new ViewModel(array(
            'form' => $form,                
        ));
    }

    public function editAction()
    {
        $forrest = new Service\BreadcrumbFactory();
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
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $user = $em->getRepository("ersEntity\Entity\User")->findOneBy(array('id' => $id));
        
        $form = new Form\User();
        $form->bind($user);
        $form->get('submit')->setAttribute('value', 'Edit');

        /*$roles = $em->getRepository("ersEntity\Entity\Role")->findAll();
        $userRoles = $user->getRoles();
        $roleValue = array();
        foreach($roles as $role) {
            $roleValue[] = array(
                'value' => $role->getId(),
                'label' => $role->getRoleId(),
                'selected' => is_numeric($userRoles->indexOf($role)) ? true : false,
                'disabled' => $role->getActive() ? true : false,
            );
        }
        $form->get('roles')->setValueOptions($roleValue);*/
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            #$inputFilter = new InputFilter\User();
            $inputFilter = $this->getServiceLocator()
                    ->get('Admin\InputFilter\User');
            $form->setInputFilter($inputFilter->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $em->persist($form->getData());
                $em->flush();

                return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
            } else {
                $logger = $this
                    ->getServiceLocator()
                    ->get('Logger');
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
        $forrest = new Service\BreadcrumbFactory();
        if(!$forrest->exists('user')) {
            $forrest->set('user', 'admin/user');
        }
        $breadcrumb = $forrest->get('user');
        
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            #return $this->redirect()->toRoute('admin/user');
            return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
        }
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $user = $em->getRepository("ersEntity\Entity\User")
                ->findOneBy(array('id' => $id));

        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');

            if ($del == 'Yes') {
                $id = (int) $request->getPost('id');
                $user = $em->getRepository("ersEntity\Entity\User")
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
}
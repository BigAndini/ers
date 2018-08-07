<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use ErsBase\Entity;
#use Admin\Form;
use ErsBase\Service;

class RoleController extends AbstractActionController {
    
    public function indexAction()
    {
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        return new ViewModel(array(
            'roles' => $entityManager->getRepository('ErsBase\Entity\Role')->findBy(array(),array('roleId' => 'ASC')),
         ));
    }

    public function addAction()
    {
        $forrest = new Service\BreadcrumbService();
        if(!$forrest->exists('role')) {
            $forrest->set('role', 'admin/role');
        }
        $breadcrumb = $forrest->get('role');
        
        #$form = new Form\Role();
        $form = $this->getServiceLocator()->get('Admin\Form\Role');
        $form->get('submit')->setValue('Add');
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $role = new Entity\Role();
            
            #$form->setInputFilter($role->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $role->populate($form->getData());
                
                $entityManager = $this->getServiceLocator()
                    ->get('Doctrine\ORM\EntityManager');
                
                $role->setParentId(null);
                if(is_numeric($role->getParentId()) && $role->getParentId() > 0) {
                    $parent = $entityManager->getRepository('ErsBase\Entity\Role')
                        ->findOneBy(array('id' => $role->getParentId()));
                
                    $role->setParent($parent);
                }
                
                $entityManager->persist($role);
                $entityManager->flush();

                return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
            } else {
                $logger = $this->getServiceLocator()->get('Logger');
                $logger->warn($form->getMessages());
            }
        }
        
        return new ViewModel(array(
            'form' => $form,
            'breadcrumb' => $breadcrumb,
        ));
    }

    public function editAction()
    {
        $forrest = new Service\BreadcrumbService();
        if(!$forrest->exists('role')) {
            $forrest->set('role', 'admin/role');
        }
        $breadcrumb = $forrest->get('role');
        
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/role', array(
                'action' => 'add'
            ));
        }
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $role = $entityManager->getRepository('ErsBase\Entity\Role')->findOneBy(array('id' => $id));

        #$form = new Form\Role();
        $form = $this->getServiceLocator()->get('Admin\Form\Role');
        $form->bind($role);
        $form->get('submit')->setAttribute('value', 'Edit');
        
        $options = $form->get('Parent_id')->getValueOptions();
        unset($options[$role->getId()]);
        $form->get('Parent_id')->setValueOptions($options);

        $request = $this->getRequest();
        if ($request->isPost()) {
            #$form->setInputFilter($role->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $entityManager->persist($form->getData());
                $entityManager->flush();

                return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
            }
        }

        return new ViewModel(array(
            'id' => $id,
            'form' => $form,
        ));
    }

    public function deleteAction()
    {
        $forrest = new Service\BreadcrumbService();
        if(!$forrest->exists('role')) {
            $forrest->set('role', 'admin/role');
        }
        $breadcrumb = $forrest->get('role');
        
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
        }
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $role = $entityManager->getRepository('ErsBase\Entity\Role')
                ->findOneBy(array('id' => $id));

        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');

            if ($del == 'Yes') {
                $id = (int) $request->getPost('id');
                $role = $entityManager->getRepository('ErsBase\Entity\Role')
                    ->findOneBy(array('id' => $id));
                $entityManager->remove($role);
                $entityManager->flush();
            }

            return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
        }
        
        $childs = $entityManager->getRepository('ErsBase\Entity\Role')
                ->findBy(array('Parent_id' => $id));
        
        return new ViewModel(array(
            'id'     => $id,
            'role'   => $role,
            'childs' => $childs,
            'breadcrumb' => $breadcrumb,
        ));
    }
}
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
#use Admin\Form;
use Admin\Service;

class RoleController extends AbstractActionController {
    
    public function indexAction()
    {
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        return new ViewModel(array(
            'roles' => $em->getRepository("ersEntity\Entity\Role")->findBy(array(),array('roleId' => 'ASC')),
         ));
    }

    public function addAction()
    {
        $forrest = new Service\BreadcrumbFactory();
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
            
            $form->setInputFilter($role->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $role->populate($form->getData());
                
                $em = $this->getServiceLocator()
                    ->get('Doctrine\ORM\EntityManager');
                
                if(is_numeric($role->getParentId()) && $role->getParentId() > 0) {
                    $parent = $em->getRepository("ersEntity\Entity\Role")
                        ->findOneBy(array('id' => $role->getParentId()));
                
                    $role->setParent($parent);
                } else {
                    $role->setParentId(null);
                }
                
                $em->persist($role);
                $em->flush();

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
        $forrest = new Service\BreadcrumbFactory();
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
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $role = $em->getRepository("ersEntity\Entity\Role")->findOneBy(array('id' => $id));

        #$form = new Form\Role();
        $form = $this->getServiceLocator()->get('Admin\Form\Role');
        $form->bind($role);
        $form->get('submit')->setAttribute('value', 'Edit');
        
        $options = $form->get('Parent_id')->getValueOptions();
        unset($options[$role->getId()]);
        $form->get('Parent_id')->setValueOptions($options);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($role->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $em->persist($form->getData());
                $em->flush();

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
        $forrest = new Service\BreadcrumbFactory();
        if(!$forrest->exists('role')) {
            $forrest->set('role', 'admin/role');
        }
        $breadcrumb = $forrest->get('role');
        
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
        }
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $role = $em->getRepository("ersEntity\Entity\Role")
                ->findOneBy(array('id' => $id));

        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');

            if ($del == 'Yes') {
                $id = (int) $request->getPost('id');
                $role = $em->getRepository("ersEntity\Entity\Role")
                    ->findOneBy(array('id' => $id));
                $em->remove($role);
                $em->flush();
            }

            return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
        }
        
        $childs = $em->getRepository("ersEntity\Entity\Role")
                ->findBy(array('Parent_id' => $id));
        
        return new ViewModel(array(
            'id'     => $id,
            'role'   => $role,
            'childs' => $childs,
            'breadcrumb' => $breadcrumb,
        ));
    }
}
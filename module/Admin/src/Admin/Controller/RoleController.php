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

class RoleController extends AbstractActionController {
    
    public function indexAction()
    {
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        return new ViewModel(array(
            'roles' => $em->getRepository("ersEntity\Entity\Role")->findAll(),
         ));
    }

    public function addAction()
    {
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
                
                $em = $this
                    ->getServiceLocator()
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

                return $this->redirect()->toRoute('admin/role');
            } else {
                error_log(var_export($form->getMessages(), true));
            }
        }
        
        return array(
            'form' => $form,                
        );
    }

    public function editAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/role', array(
                'action' => 'add'
            ));
        }
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $role = $em->getRepository("ersEntity\Entity\Role")->findOneBy(array('id' => $id));

        #$form = new Form\Role();
        $form = $this->getServiceLocator()->get('Admin\Form\Role');
        $form->bind($role);
        $form->get('submit')->setAttribute('value', 'Edit');
        
        $options = $form->get('parent_id')->getValueOptions();
        unset($options[$role->getId()]);
        $form->get('parent_id')->setValueOptions($options);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($role->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $em->persist($form->getData());
                $em->flush();

                return $this->redirect()->toRoute('admin/role');
            }
        }

        return array(
            'id' => $id,
            'form' => $form,
        );
    }

    /*
     * The delete action is for Agegroups, Counters and Roles the same.
     */
    public function deleteAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/role');
        }
        $em = $this
            ->getServiceLocator()
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

            return $this->redirect()->toRoute('admin/role');
        }

        return array(
            'id'    => $id,
            'role' => $role,
        );
    }
}
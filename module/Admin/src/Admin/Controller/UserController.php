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

class UserController extends AbstractActionController {
    
    public function indexAction()
    {
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        return new ViewModel(array(
            'users' => $em->getRepository("ersEntity\Entity\User")->findAll(),
         ));
    }

    public function addAction()
    {
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

                return $this->redirect()->toRoute('admin/user');
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

        $roles = $em->getRepository("ersEntity\Entity\Role")->findAll();
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
        $form->get('roles')->setValueOptions($roleValue);
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($user->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $em->persist($form->getData());
                $em->flush();

                return $this->redirect()->toRoute('admin/user');
            } else {
                error_log(var_export($form->getMessages(), true));
            }
        }

        return array(
            'id' => $id,
            'form' => $form,
        );
    }

    /*
     * The delete action is for Agegroups, Counters and Users the same.
     */
    public function deleteAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/user');
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

            return $this->redirect()->toRoute('admin/user');
        }

        return array(
            'id'    => $id,
            'user' => $user,
        );
    }
}
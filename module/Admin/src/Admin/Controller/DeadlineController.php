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
#use RegistrationSystem\Form\UserForm;
use Admin\Form;
use Zend\Form\Element;

class DeadlineController extends AbstractActionController {
    
    public function indexAction()
    {
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        return new ViewModel(array(
            'deadlines' => $em->getRepository("ersEntity\Entity\Deadline")->findAll(),
         ));
    }

    public function addAction()
    {
        $form = new Form\DeadlineForm();
        $form->get('submit')->setValue('Add');
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $deadline = new Entity\Deadline();
            
            $form->setInputFilter($deadline->getInputFilter());
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $deadline->populate($form->getData());
                
                $em = $this
                    ->getServiceLocator()
                    ->get('Doctrine\ORM\EntityManager');
                
                $em->persist($deadline);
                $em->flush();

                return $this->redirect()->toRoute('admin/deadline');
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
            return $this->redirect()->toRoute('admin/deadline', array(
                'action' => 'add'
            ));
        }
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $deadline = $em->getRepository("ersEntity\Entity\Deadline")->findOneBy(array('id' => $id));

        $form = new Form\DeadlineForm();
        $form->bind($deadline);
        $form->get('submit')->setAttribute('value', 'Edit');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($deadline->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                #$deadline->populate($form->getData());
                
                $em->persist($form->getData());
                #$em->persist($deadline);
                $em->flush();

                return $this->redirect()->toRoute('admin/deadline');
            }
        }

        return array(
            'id' => $id,
            'form' => $form,
        );
    }
    
    public function copyAction()
    {   
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/deadline', array(
                'action' => 'add'
            ));
        }
        $product = $this->getTable('Product')->getById($id);

        $form = $this->getServiceLocator()->get('Form\DeadlineForm');
        $form->bind($product);
        $form->get('submit')->setAttribute('value', 'Copy');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($product->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $new_id = $this->getTable('Deadline')->save($form->getData());
                error_log('saved price limit: '.$id);

                // Redirect to list of products
                return $this->redirect()->toRoute('admin/deadline');
            } else {
                error_log(var_export($form->getMessages()));
            }
        }

        return array(
            'id' => $id,
            'form' => $form,
        );
    }

    /*
     * The delete action is for Agegroups, Counters and Deadlines the same.
     */
    public function deleteAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/deadline');
        }
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $productprice = $em->getRepository("ersEntity\Entity\Deadline")
                ->findOneBy(array('id' => $id));

        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');

            if ($del == 'Yes') {
                $id = (int) $request->getPost('id');
                $productprice = $em->getRepository("ersEntity\Entity\Deadline")
                    ->findOneBy(array('id' => $id));
                $em->remove($productprice);
                $em->flush();
            }

            return $this->redirect()->toRoute('admin/deadline');
        }

        return array(
            'id'    => $id,
            'deadline' => $productprice,
        );
    }
}
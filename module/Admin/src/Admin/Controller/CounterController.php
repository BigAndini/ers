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

class CounterController extends AbstractActionController {
    
    public function indexAction()
    {
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        return new ViewModel(array(
            'counters' => $em->getRepository("ersEntity\Entity\Counter")->findAll(),
         ));
    }

    public function addAction()
    {
        $form = new Form\CounterForm();
        $form->get('submit')->setValue('Add');
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $counter = new Entity\Counter();
            
            $form->setInputFilter($counter->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $counter->populate($form->getData());
                
                $em = $this
                    ->getServiceLocator()
                    ->get('Doctrine\ORM\EntityManager');
                
                error_log('counter: '.get_class($counter->getCounter()));
                
                $em->persist($counter);
                $em->flush();

                return $this->redirect()->toRoute('admin/counter');
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
            return $this->redirect()->toRoute('admin/counter', array(
                'action' => 'add'
            ));
        }
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $counter = $em->getRepository("ersEntity\Entity\Counter")->findOneBy(array('id' => $id));

        $form = new Form\CounterForm();
        $form->bind($counter);
        $form->get('submit')->setAttribute('value', 'Edit');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($counter->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                #$counter->populate($form->getData());
                
                $em->persist($form->getData());
                #$em->persist($counter);
                $em->flush();

                return $this->redirect()->toRoute('admin/counter');
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
            return $this->redirect()->toRoute('admin/counter', array(
                'action' => 'add'
            ));
        }
        $product = $this->getTable('Product')->getById($id);

        $form = $this->getServiceLocator()->get('Form\CounterForm');
        $form->bind($product);
        $form->get('submit')->setAttribute('value', 'Copy');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($product->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $new_id = $this->getTable('Counter')->save($form->getData());
                error_log('saved price limit: '.$id);

                // Redirect to list of products
                return $this->redirect()->toRoute('admin/counter');
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
     * The delete action is for Agegroups, Counters and Counters the same.
     */
    public function deleteAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/counter');
        }
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $productprice = $em->getRepository("ersEntity\Entity\Counter")
                ->findOneBy(array('id' => $id));

        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');

            if ($del == 'Yes') {
                $id = (int) $request->getPost('id');
                $productprice = $em->getRepository("ersEntity\Entity\Counter")
                    ->findOneBy(array('id' => $id));
                $em->remove($productprice);
                $em->flush();
            }

            // Redirect to list of products
            return $this->redirect()->toRoute('admin/counter');
        }

        return array(
            'id'    => $id,
            'counter' => $productprice,
        );
    }
}
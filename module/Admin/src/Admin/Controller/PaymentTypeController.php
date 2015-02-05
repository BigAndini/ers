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

class PaymentTypeController extends AbstractActionController {
    
    public function indexAction()
    {
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        return new ViewModel(array(
            'paymenttypes' => $em->getRepository("ersEntity\Entity\PaymentType")->findAll(),
         ));
    }

    public function enableAction() {}
    
    public function disableAction() {}
    
    public function addBankTransferAction() {
        $form = new Form\PaymentTypeBankTransferForm();
        $form->get('submit')->setValue('Add');
    
        $request = $this->getRequest();
        if ($request->isPost()) {
            // Make certain to merge the files info!
            $post = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );

            $form->setData($post);
            if ($form->isValid()) {
                $data = $form->getData();
                error_log(var_export($data, true));
                // Form is valid, save the form!
                return $this->redirect()->toRoute('admin/payment-type');
            } else {
                error_log(var_export($form->getMessages(), true));
            }
        }

        return array('form' => $form);
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $paymenttype = new Entity\PaymentType();
            
            $form->setInputFilter($paymenttype->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $paymenttype->populate($form->getData());
                
                $em = $this
                    ->getServiceLocator()
                    ->get('Doctrine\ORM\EntityManager');
                
                $em->persist($paymenttype);
                $em->flush();

                return $this->redirect()->toRoute('admin/payment-type');
            } else {
                error_log(var_export($form->getMessages(), true));
            }
        }
        
        return array(
            'form' => $form,                
        );
    }
    
    public function addAction()
    {
        $form = new Form\PaymentTypeForm();
        $form->get('submit')->setValue('Add');
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $deadline = new Entity\PaymentType();
            
            $form->setInputFilter($deadline->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $deadline->populate($form->getData());
                
                $em = $this
                    ->getServiceLocator()
                    ->get('Doctrine\ORM\EntityManager');
                
                error_log('deadline: '.get_class($deadline->getPaymentType()));
                
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
        $deadline = $em->getRepository("ersEntity\Entity\PaymentType")->findOneBy(array('id' => $id));

        $form = new Form\PaymentTypeForm();
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

    public function deleteAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/deadline');
        }
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $productprice = $em->getRepository("ersEntity\Entity\PaymentType")
                ->findOneBy(array('id' => $id));

        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');

            if ($del == 'Yes') {
                $id = (int) $request->getPost('id');
                $productprice = $em->getRepository("ersEntity\Entity\PaymentType")
                    ->findOneBy(array('id' => $id));
                $em->remove($productprice);
                $em->flush();
            }

            // Redirect to list of products
            return $this->redirect()->toRoute('admin/deadline');
        }

        return array(
            'id'    => $id,
            'deadline' => $productprice,
        );
    }
}
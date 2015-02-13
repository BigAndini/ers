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

# for file upload
use Zend\InputFilter;

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
    
        $paymenttype = new Entity\PaymentType();
        
        $inputFilter = $paymenttype->getInputFilter();
        
        // File Input
        $fileInput = new InputFilter\FileInput('logo-upload');
        $fileInput->setRequired(true);
        $fileInput->getFilterChain()->attachByName(
            'filerenameupload',
            array(
                #'target'    => './data/tmpuploads/payment.jpg',
                'target'    => getcwd().'/public/media/payment.jpg',
                'randomize' => true,
            )
        );
        $inputFilter->add($fileInput);
        
        $form->setInputFilter($inputFilter);
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $files = $request->getFiles()->toArray();
            error_log(var_export($files, true));
            // Make certain to merge the files info!
            $post = array_merge_recursive(
                $request->getPost()->toArray(),
                $files
            );

            $form->setData($post);
            if ($form->isValid()) {
                $data = $form->getData();
                error_log(var_export($data, true));
                $paymenttype->populate($data);
                $paymenttype->setType('BankTransfer');
                
                $em = $this
                    ->getServiceLocator()
                    ->get('Doctrine\ORM\EntityManager');
                
                $paymenttype->setLogo(\basename($data['logo-upload']['tmp_name']));
                
                $em->persist($paymenttype);
                $em->flush();
                
                return $this->redirect()->toRoute('admin/payment-type');
            } else {
                error_log(var_export($form->getMessages(), true));
            }
        }

        return array('form' => $form);
    }
    
    public function addAction()
    {
        $form = new Form\PaymentTypeForm();
        $form->get('submit')->setValue('Add');
        
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

    public function editBankTransferAction() {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/payment-type', array(
                'action' => 'add'
            ));
        }
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $paymenttype = $em->getRepository("ersEntity\Entity\PaymentType")->findOneBy(array('id' => $id));
        
        $form = new Form\PaymentTypeBankTransferForm();
        $form->bind($paymenttype);
        $form->get('submit')->setValue('Edit');
    
        $form->setInputFilter($paymenttype->getInputFilter());
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            // Make certain to merge the files info!
            $post = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );

            $form->setData($post);
            if ($form->isValid()) {
                $em->persist($form->getData());
                $em->flush();
                
                return $this->redirect()->toRoute('admin/payment-type');
            } else {
                error_log(var_export($form->getMessages(), true));
            }
        }

        return array('form' => $form);
    }
    
    public function editAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/payment-type', array(
                'action' => 'add'
            ));
        }
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $paymenttype = $em->getRepository("ersEntity\Entity\PaymentType")->findOneBy(array('id' => $id));

        $form = new Form\PaymentTypeForm();
        $form->bind($paymenttype);
        $form->get('submit')->setAttribute('value', 'Edit');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($paymenttype->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                #$paymenttype->populate($form->getData());
                
                $em->persist($form->getData());
                #$em->persist($paymenttype);
                $em->flush();

                return $this->redirect()->toRoute('admin/payment-type');
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
            return $this->redirect()->toRoute('admin/payment-type');
        }
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $paymenttype = $em->getRepository("ersEntity\Entity\PaymentType")
                ->findOneBy(array('id' => $id));

        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');

            if ($del == 'Yes') {
                $id = (int) $request->getPost('id');
                $paymenttype = $em->getRepository("ersEntity\Entity\PaymentType")
                    ->findOneBy(array('id' => $id));
                $em->remove($paymenttype);
                $em->flush();
            }

            return $this->redirect()->toRoute('admin/payment-type');
        }

        return array(
            'id'    => $id,
            'paymenttype' => $paymenttype,
        );
    }
}
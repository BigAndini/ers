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
use Admin\InputFilter;

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

    public function addLogoAction() {
        
    }
    public function editLogoAction() {
        
    }
    public function deleteLogoAction() {
        
    }
    
    public function addBankTransferAction() {
        $form = new Form\PaymentTypeBankTransfer();
        $form->get('submit')->setValue('Add');

        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $deadlines = $em->getRepository("ersEntity\Entity\Deadline")
                ->findBy(array(), array('deadline' => 'ASC'));
        $options = array();
        foreach($deadlines as $deadline) {
            $options[] = array(
                'value' => $deadline->getId(),
                'label' => 'Deadline: '.$deadline->getDeadline()->format('Y-m-d H:i:s'),
                'selected' => false,
            );
        }
        $options[] = array(
            'value' => 0,
            'label' => 'no Deadline',
            'selected' => true,
        );
        $form->get('activeFrom_id')->setAttribute('options', $options);
        $form->get('activeUntil_id')->setAttribute('options', $options);
        
        $paymenttype = new Entity\PaymentType();
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $inputFilter = new InputFilter\PaymentTypeBankTransfer();
            $inputFilter->setEntityManager($em);
            
            $form->setInputFilter($inputFilter->getInputFilter());
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $paymenttype->populate($form->getData());
                $paymenttype->setType('BankTransfer');
                
                if($paymenttype->getActiveFromId() == 0) {
                    $paymenttype->setActiveFromId(null);
                } else {
                    $activeFrom = $em->getRepository("ersEntity\Entity\Deadline")
                        ->findOneBy(array('id' => $paymenttype->getActiveFromId()));
                    $paymenttype->setActiveFrom($activeFrom);
                }
                if($paymenttype->getActiveUntilId() == 0) {
                    $paymenttype->setActiveUntilId(null);
                } else {
                    $activeUntil = $em->getRepository("ersEntity\Entity\Deadline")
                        ->findOneBy(array('id' => $paymenttype->getActiveUntilId()));
                    $paymenttype->setActiveUntil($activeUntil);
                }
                
                $em->persist($paymenttype);
                $em->flush();
                
                return $this->redirect()->toRoute('admin/payment-type');
            } else {
                error_log(var_export($form->getMessages(), true));
            }
        }

        return new ViewModel(array(
            'form' => $form
        ));
    }
    
    public function addCreditCardAction() {
        $form = new Form\PaymentTypeBankTransfer();
        $form->get('submit')->setValue('Add');

        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $deadlines = $em->getRepository("ersEntity\Entity\Deadline")
                ->findBy(array(), array('deadline' => 'ASC'));
        $options = array();
        foreach($deadlines as $deadline) {
            $options[] = array(
                'value' => $deadline->getId(),
                'label' => 'Deadline: '.$deadline->getDeadline()->format('Y-m-d H:i:s'),
                'selected' => false,
            );
        }
        $options[] = array(
            'value' => 0,
            'label' => 'no Deadline',
            'selected' => true,
        );
        $form->get('activeFrom_id')->setAttribute('options', $options);
        $form->get('activeUntil_id')->setAttribute('options', $options);
        
        $paymenttype = new Entity\PaymentType();
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $inputFilter = new InputFilter\PaymentTypeBankTransfer();
            $inputFilter->setEntityManager($em);
            
            $form->setInputFilter($inputFilter->getInputFilter());
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $paymenttype->populate($form->getData());
                $paymenttype->setType('CreditCard');
                
                if($paymenttype->getActiveFromId() == 0) {
                    $paymenttype->setActiveFromId(null);
                } else {
                    $activeFrom = $em->getRepository("ersEntity\Entity\Deadline")
                        ->findOneBy(array('id' => $paymenttype->getActiveFromId()));
                    $paymenttype->setActiveFrom($activeFrom);
                }
                if($paymenttype->getActiveUntilId() == 0) {
                    $paymenttype->setActiveUntilId(null);
                } else {
                    $activeUntil = $em->getRepository("ersEntity\Entity\Deadline")
                        ->findOneBy(array('id' => $paymenttype->getActiveUntilId()));
                    $paymenttype->setActiveUntil($activeUntil);
                }
                
                $em->persist($paymenttype);
                $em->flush();
                
                return $this->redirect()->toRoute('admin/payment-type');
            } else {
                error_log(var_export($form->getMessages(), true));
            }
        }

        return new ViewModel(array(
            'form' => $form
        ));
    }
    
    public function addAction()
    {
        $form = new Form\PaymentType();
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
        
        return new ViewModel(array(
            'form' => $form,                
        ));
    }

    public function editBankTransferAction() {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/payment-type', array(
                'action' => 'addBankTransfer'
            ));
        }
        
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $paymenttype = $em->getRepository("ersEntity\Entity\PaymentType")->findOneBy(array('id' => $id));
            
        $deadlines = $em->getRepository("ersEntity\Entity\Deadline")
                ->findBy(array(), array('deadline' => 'ASC'));
        $from_options = array();
        $until_options = array();
        foreach($deadlines as $deadline) {
            $from_selected = false;
            if($deadline->getId() == $paymenttype->getActiveFromId()) {
                $from_selected = true;
            }
            $until_selected = false;
            if($deadline->getId() == $paymenttype->getActiveUntilId()) {
                $until_selected = true;
            }
            $from_options[] = array(
                'value' => $deadline->getId(),
                'label' => 'Deadline: '.$deadline->getDeadline()->format('Y-m-d H:i:s'),
                'selected' => $from_selected,
            );
            $until_options[] = array(
                'value' => $deadline->getId(),
                'label' => 'Deadline: '.$deadline->getDeadline()->format('Y-m-d H:i:s'),
                'selected' => $until_selected,
            );
        }
        $from_selected = false;
        if($paymenttype->getActiveFromId() == 0) {
            $from_selected = true;
        }
        $from_options[] = array(
            'value' => 0,
            'label' => 'no Deadline',
            'selected' => $from_selected,
        );
        $until_selected = false;
        if($paymenttype->getActiveUntilId() == 0) {
            $until_selected = true;
        }
        $until_options[] = array(
            'value' => 0,
            'label' => 'no Deadline',
            'selected' => $until_selected,
        );
        
        $form = new Form\PaymentTypeBankTransfer();
        
        $form->get('activeFrom_id')->setAttribute('options', $from_options);
        $form->get('activeUntil_id')->setAttribute('options', $until_options);

        $inputFilter = new InputFilter\PaymentTypeBankTransfer();
        $inputFilter->setEntityManager($em);

        $paymenttype->setInputFilter($inputFilter->getInputFilter());
        $form->bind($paymenttype);
        $form->get('submit')->setValue('Edit');
    
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $paymenttype = $form->getData();
                
                if($paymenttype->getActiveFromId() == 0) {
                    $paymenttype->setActiveFromId(null);
                } else {
                    $activeFrom = $em->getRepository("ersEntity\Entity\Deadline")->findOneBy(array('id' => $paymenttype->getActiveFromId()));
                    $paymenttype->setActiveFrom($activeFrom);
                }
                if($paymenttype->getActiveUntilId() == 0) {
                    $paymenttype->setActiveUntilId(null);
                } else {
                    $activeUntil = $em->getRepository("ersEntity\Entity\Deadline")->findOneBy(array('id' => $paymenttype->getActiveUntilId()));
                    $paymenttype->setActiveUntil($activeUntil);
                }
                
                $em->persist($paymenttype);
                $em->flush();
                
                return $this->redirect()->toRoute('admin/payment-type');
            } else {
                error_log(var_export($form->getMessages(), true));
            }
        }

        return new ViewModel(array(
            'form'  => $form,
            'id'    => $id,
        ));
    }
    
    public function editCreditCardAction() {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/payment-type', array(
                'action' => 'addCreditCard'
            ));
        }
        
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $paymenttype = $em->getRepository("ersEntity\Entity\PaymentType")->findOneBy(array('id' => $id));
            
        $deadlines = $em->getRepository("ersEntity\Entity\Deadline")
                ->findBy(array(), array('deadline' => 'ASC'));
        $from_options = array();
        $until_options = array();
        foreach($deadlines as $deadline) {
            $from_selected = false;
            if($deadline->getId() == $paymenttype->getActiveFromId()) {
                $from_selected = true;
            }
            $until_selected = false;
            if($deadline->getId() == $paymenttype->getActiveUntilId()) {
                $until_selected = true;
            }
            $from_options[] = array(
                'value' => $deadline->getId(),
                'label' => 'Deadline: '.$deadline->getDeadline()->format('Y-m-d H:i:s'),
                'selected' => $from_selected,
            );
            $until_options[] = array(
                'value' => $deadline->getId(),
                'label' => 'Deadline: '.$deadline->getDeadline()->format('Y-m-d H:i:s'),
                'selected' => $until_selected,
            );
        }
        $from_selected = false;
        if($paymenttype->getActiveFromId() == 0) {
            $from_selected = true;
        }
        $from_options[] = array(
            'value' => 0,
            'label' => 'no Deadline',
            'selected' => $from_selected,
        );
        $until_selected = false;
        if($paymenttype->getActiveUntilId() == 0) {
            $until_selected = true;
        }
        $until_options[] = array(
            'value' => 0,
            'label' => 'no Deadline',
            'selected' => $until_selected,
        );
        
        $form = new Form\PaymentTypeBankTransfer();
        
        $form->get('activeFrom_id')->setAttribute('options', $from_options);
        $form->get('activeUntil_id')->setAttribute('options', $until_options);

        $inputFilter = new InputFilter\PaymentTypeBankTransfer();
        $inputFilter->setEntityManager($em);

        $paymenttype->setInputFilter($inputFilter->getInputFilter());
        $form->bind($paymenttype);
        $form->get('submit')->setValue('Edit');
    
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $paymenttype = $form->getData();
                
                if($paymenttype->getActiveFromId() == 0) {
                    $paymenttype->setActiveFromId(null);
                } else {
                    $activeFrom = $em->getRepository("ersEntity\Entity\Deadline")->findOneBy(array('id' => $paymenttype->getActiveFromId()));
                    $paymenttype->setActiveFrom($activeFrom);
                }
                if($paymenttype->getActiveUntilId() == 0) {
                    $paymenttype->setActiveUntilId(null);
                } else {
                    $activeUntil = $em->getRepository("ersEntity\Entity\Deadline")->findOneBy(array('id' => $paymenttype->getActiveUntilId()));
                    $paymenttype->setActiveUntil($activeUntil);
                }
                
                $em->persist($paymenttype);
                $em->flush();
                
                return $this->redirect()->toRoute('admin/payment-type');
            } else {
                error_log(var_export($form->getMessages(), true));
            }
        }

        return new ViewModel(array(
            'form'  => $form,
            'id'    => $id,
        ));
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

        $form = new Form\PaymentType();
        $form->bind($paymenttype);
        $form->get('submit')->setAttribute('value', 'Edit');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($paymenttype->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $em->persist($form->getData());
                $em->flush();

                return $this->redirect()->toRoute('admin/payment-type');
            }
        }

        return new ViewModel(array(
            'id' => $id,
            'form' => $form,
        ));
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

        return new ViewModel(array(
            'id'    => $id,
            'paymenttype' => $paymenttype,
        ));
    }
}
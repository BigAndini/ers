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
use Admin\Form;
use Admin\InputFilter;

class PaymentTypeController extends AbstractActionController {

    public function indexAction() {
        $em = $this->getServiceLocator()
                ->get('Doctrine\ORM\EntityManager');

        return new ViewModel(array(
            'paymenttypes' => $em->getRepository('ErsBase\Entity\PaymentType')
                    ->findBy(array(), array('position' => 'ASC')),
        ));
    }

    public function addLogoAction() {
    }

    public function editLogoAction() {
    }

    public function deleteLogoAction() {
    }


    public function addAction() {
        $form = new Form\PaymentType();
        $form->get('submit')->setValue('Add');

        $deadlineOptions = $this->buildDeadlineOptions();
        $form->get('active_from_id')->setAttribute('options', $deadlineOptions);
        $form->get('active_until_id')->setAttribute('options', $deadlineOptions);
        $form->get('active_from_id')->setValue(0);
        $form->get('active_until_id')->setValue(0);

        $em = $this->getServiceLocator()
                ->get('Doctrine\ORM\EntityManager');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $inputFilter = new InputFilter\PaymentType();
            $inputFilter->setEntityManager($em);

            $form->setInputFilter($inputFilter->getInputFilter());
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $paymenttype = new Entity\PaymentType();
                $paymenttype->populate($form->getData());

                if ($paymenttype->getActiveFromId() == 0) {
                    $paymenttype->setActiveFromId(null);
                } else {
                    $active_from = $em->getRepository('ErsBase\Entity\Deadline')->find($paymenttype->getActiveFromId());
                    $paymenttype->setActiveFrom($active_from);
                }
                if ($paymenttype->getActiveUntilId() == 0) {
                    $paymenttype->setActiveUntilId(null);
                } else {
                    $active_until = $em->getRepository('ErsBase\Entity\Deadline')->find($paymenttype->getActiveUntilId());
                    $paymenttype->setActiveUntil($active_until);
                }

                $em->persist($paymenttype);
                $em->flush();

                return $this->redirect()->toRoute('admin/payment-type');
            } else {
                $logger = $this->getServiceLocator()->get('Logger');
                $logger->warn($form->getMessages());
            }
        }

        return new ViewModel(array(
            'form' => $form
        ));
    }


    public function editAction() {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/payment-type', ['action' => 'add']);
        }

        $em = $this->getServiceLocator()
                ->get('Doctrine\ORM\EntityManager');

        $paymenttype = $em->getRepository('ErsBase\Entity\PaymentType')->find($id);
        if (!$paymenttype)
            return $this->notFoundAction();

        $form = new Form\PaymentType();
        $form->get('submit')->setValue('Edit');

        $deadlineOptions = $this->buildDeadlineOptions();
        $form->get('active_from_id')->setAttribute('options', $deadlineOptions);
        $form->get('active_until_id')->setAttribute('options', $deadlineOptions);

        $form->bind($paymenttype);
        // fix binding for "no Deadline" selection
        if (!$paymenttype->getActiveFromId())
            $form->get('active_from_id')->setValue(0);
        if (!$paymenttype->getActiveUntilId())
            $form->get('active_until_id')->setValue(0);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $inputFilter = new InputFilter\PaymentType();
            $inputFilter->setEntityManager($em);

            $form->setInputFilter($inputFilter->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                if ($paymenttype->getActiveFromId() == 0) {
                    $paymenttype->setActiveFromId(null);
                } else {
                    $active_from = $em->getRepository('ErsBase\Entity\Deadline')->find($paymenttype->getActiveFromId());
                    $paymenttype->setActiveFrom($active_from);
                }
                if ($paymenttype->getActiveUntilId() == 0) {
                    $paymenttype->setActiveUntilId(null);
                } else {
                    $active_until = $em->getRepository('ErsBase\Entity\Deadline')->find($paymenttype->getActiveUntilId());
                    $paymenttype->setActiveUntil($active_until);
                }

                $em->persist($paymenttype);
                $em->flush();

                return $this->redirect()->toRoute('admin/payment-type');
            } else {
                $logger = $this->getServiceLocator()->get('Logger');
                $logger->warn($form->getMessages());
            }
        }

        return new ViewModel(array(
            'form' => $form,
            'id' => $id,
        ));
    }
    

    public function deleteAction() {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id)
            return $this->redirect()->toRoute('admin/payment-type');

        $em = $this->getServiceLocator()
                ->get('Doctrine\ORM\EntityManager');
        $paymenttype = $em->getRepository('ErsBase\Entity\PaymentType')->find($id);

        if (!$paymenttype)
            return $this->notFoundAction();

        $orders = $paymenttype->getOrders();

        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');

            if ($del == 'Yes') {
                $id = (int) $request->getPost('id');
                $paymenttype = $em->getRepository('ErsBase\Entity\PaymentType')->find($id);
                $em->remove($paymenttype);
                $em->flush();
            }

            return $this->redirect()->toRoute('admin/payment-type');
        }

        return new ViewModel(array(
            'id' => $id,
            'orders' => $orders,
            'paymenttype' => $paymenttype,
        ));
    }

    private function buildDeadlineOptions() {
        $deadlines = $this->getServiceLocator()
                ->get('Doctrine\ORM\EntityManager')
                ->getRepository('ErsBase\Entity\Deadline')
                ->findBy(array(), array('deadline' => 'ASC'));

        $options = array();
        foreach ($deadlines as $deadline) {
            $options[] = array(
                'value' => $deadline->getId(),
                'label' => 'Deadline: ' . $deadline->getDeadline()->format('Y-m-d H:i:s')
            );
        }
        $options[] = array(
            'value' => 0,
            'label' => 'no Deadline'
        );

        return $options;
    }

}

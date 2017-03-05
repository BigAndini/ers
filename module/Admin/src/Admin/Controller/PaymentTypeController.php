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
        $form = $this->getServiceLocator()
                ->get('Admin\Form\PaymentType');
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
                
                $currency = $em->getRepository('ErsBase\Entity\Currency')
                        ->findOneBy(array('id' => $paymenttype->getCurrencyId()));
                $paymenttype->setCurrency($currency);
                $paymenttype->setCurrencyId($currency->getId());

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

        $form = $this->getServiceLocator()
                ->get('Admin\Form\PaymentType');
        /*$form = new Form\PaymentType();
        $form->get('submit')->setValue('Save');

        $deadlineOptions = $this->buildDeadlineOptions();
        $form->get('active_from_id')->setAttribute('options', $deadlineOptions);
        $form->get('active_until_id')->setAttribute('options', $deadlineOptions);

        $typeOptions = [
            [
                'value' => '',
                'label' => 'Select type ...',
                'disabled' => true,
                'selected' => true,
            ],
            [
                'value' => 'sepa',
                'label' => 'Sepa Bank Account',
            ],
            [
                'value' => 'ipayment',
                'label' => 'iPayment Account',
            ],
            [
                'value' => 'paypal',
                'label' => 'Paypal Account',
            ],
        ];
        $form->get('type')->setAttribute('options', $typeOptions);*/
        
        
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
                    $active_from = $em->getRepository('ErsBase\Entity\Deadline')
                            ->find($paymenttype->getActiveFromId());
                    $paymenttype->setActiveFrom($active_from);
                }
                if ($paymenttype->getActiveUntilId() == 0) {
                    $paymenttype->setActiveUntilId(null);
                } else {
                    $active_until = $em->getRepository('ErsBase\Entity\Deadline')
                            ->find($paymenttype->getActiveUntilId());
                    $paymenttype->setActiveUntil($active_until);
                }
                
                $currency = $em->getRepository('ErsBase\Entity\Currency')
                        ->findOneBy(array('id' => $paymenttype->getCurrencyId()));
                $paymenttype->setCurrency($currency);
                $paymenttype->setCurrencyId($currency->getId());
                

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

    public function uploadCsvAction() {
        $form = new Form\UploadCsv();
        
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $accounts = $em->getRepository('ErsBase\Entity\PaymentType')
                ->findBy(array());
        
        $options = array();
        $options[] = array(
            'value' => 'choose bank account',
            'label' => 'choose bank account',
            'disabled' => true,
            'selected' => true,
        );
        foreach($accounts as $account) {
            $options[] = array(
                'value' => $account->getId(),
                'label' => $account->getName(),
            );
        }
        #$form->get('payment_type_id')->setAttribute('options', $options);
        $form->get('payment_type_id')->setAttribute('options', $options);
        
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
                
                #$id = $data['payment_type_id'];
                $id = $data['payment_type_id'];
                $paymentType = $em->getRepository('ErsBase\Entity\PaymentType')
                    ->findOneBy(array('id' => $id));
                
                $file = $data['csv-upload'];
                
                $bankAccountCsv = new Entity\BankAccountCsv();
                $bankAccountCsv->setCsvFile($file['name']);
                $bankAccountCsv->setPaymentType($paymentType);
                
                $em->persist($bankAccountCsv);
                
                /*
                 * open file for reading
                 */
                $handle = fopen($file['tmp_name'], "r");
                if (!$handle) {
                    throw new \Exception('Unable to open csv');
                }
                
                # only needed to disable negative statements.
                # DO NOT ADJUST FIELDS ACCORDING TO THE STATEMENT FORMAT HERE!
                $statement_format = json_decode($paymentType->getStatementFormat());
                /*$fix_amount = false;
                if(is_array($statement_format)) {
                    $fix_amount = true;
                }*/
                
                /*
                 * read every line in the file and generate bank statement entities
                 */
                $row = 1;
                $hashes = array();
                $separator = substr($data['separator'], 0, 1);
                while (($row_data = fgetcsv($handle, 1000, $separator)) !== FALSE) {
                    $bs = new Entity\BankStatement();
                    $bs->setPaymentType($paymentType);
                    $bs->setBankAccountCsv($bankAccountCsv);
                    $bs->setStatus('new');
                    foreach($row_data as $column => $value) {
                        $bsc = new Entity\BankStatementCol();
                        $bsc->setColumn(($column+1));
                        $bsc->setValue($value);
                        $bsc->setBankStatement($bs);
                        $bs->addBankStatementCol($bsc);
                    }
                    $bs->generateHash();
                    
                    $bankstatement = $em->getRepository('ErsBase\Entity\PaymentType')
                        ->findOneBy(array('hash' => $bs->getHash()));
                    if($bankstatement) {
                        continue;
                    }
                 
                    /*
                     * bank statement already exists in this file
                     */
                    if(in_array($bs->getHash(), $hashes)) {
                        error_log('The bank statement you want to upload exists twice in this file: '.$bs->getHash());
                        continue;
                    }
                    $hashes[] = $bs->getHash();
                    
                    if(isset($statement_format->sign->col) && isset($statement_format->sign->value)) {
                        if($bs->getBankStatementColByNumber($statement_format->sign->col)->getValue() != $statement_format->sign->value) {
                            $bs->setStatus('disabled');
                        }
                    }
                    
                    $em->persist($bs);
                    $row++;
                }
                fclose($handle);
                
                $em->flush();
                
                return $this->redirect()->toRoute('admin/payment-type');
            }
        }
        
        return new ViewModel(array(
            'form' => $form,
        ));
    }
    
    public function detailAction() {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/payment-type', array());
        }
        
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $paymentType = $em->getRepository('ErsBase\Entity\PaymentType')
                ->findOneBy(array('id' => $id));
        
        switch($paymentType->getType()) {
            case 'sepa':
                $form = new Form\AccountSepabankDetail($em);
                break;
            case 'ipayment':
                $form = new Form\AccountIpaymentDetail($em);
                if(empty($paymentType->getTrxCurrency())) {
                    $paymentType->setTrxCurrency('EUR');
                }
                if(empty($paymentType->getAction())) {
                    $paymentType->setAction('https://ipayment.de/merchant/%account_id%/processor/2.0/');
                }
                break;
            case 'paypal':
                $form = new Form\AccountPaypalDetail($em);
                break;
            default:
                $options = [
                    [
                        'value' => '',
                        'label' => 'unkown type',
                        'disabled' => true,
                        'selected' => true,
                    ],
                    [
                        'value' => 'sepa',
                        'label' => 'Sepa Bank Account',
                    ],
                    [
                        'value' => 'ipayment',
                        'label' => 'iPayment Account',
                    ],
                    [
                        'value' => 'paypal',
                        'label' => 'Paypal Account',
                    ],
                ];

                $form = new Form\AccountUnknownDetail($em);
                $form->get('type')->setAttribute('options', $options);
                break;
        }
        
        $form->bind($paymentType);
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $em->persist($form->getData());
                $em->flush();

                return $this->redirect()->toRoute('admin/payment-type');
            }
        }
        
        return new ViewModel(array(
            'form' => $form,
        ));
    }
    
}

<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Admin\Form;
use Admin\InputFilter;
use ErsBase\Entity;

class BankaccountController extends AbstractActionController {
 
    public function indexAction()
    {
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $accounts = $entityManager->getRepository('ErsBase\Entity\PaymentType')
                ->findBy(array());
        
        return new ViewModel(array(
            'accounts' => $accounts,
        ));
    }
    
    public function addAction()
    {
        $form = new Form\PaymentType();
        #$form->get('submit')->setValue('Add');
        
        $typeOptions = [
            'empty' => [
                'label' => 'Please select type of bank account',
                'value' => 0,
                'disabled' => true,
                'selected' => true,
            ],
            'sepa' => [
                'label' => 'SEPA Bank Account',
                'value' => 'sepa',
            ],
            'ipayment' => [
                'label' => '1&1 iPayment Account',
                'value' => 'ipayment',
            ],
            'paypal' => [
                'label' => 'Paypal Account',
                'value' => 'paypal',
            ],
        ];
        
        $form->get('type')->setValueOptions($typeOptions);
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $bankaccount = new Entity\PaymentType();
            
            #$form->setInputFilter($bankaccount->getInputFilter());
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $bankaccount->populate($form->getData());
                
                $entityManager = $this->getServiceLocator()
                    ->get('Doctrine\ORM\EntityManager');
                
                $entityManager->persist($bankaccount);
                $entityManager->flush();

                $this->flashMessenger()->addSuccessMessage('The bankaccount has been successfully added');
                return $this->redirect()->toRoute('admin/bankaccount');
            } else {
                $this->flashMessenger()->addErrorMessage($form->getMessages());
                $logger = $this->getServiceLocator()->get('Logger');
                $logger->warn($form->getMessages());
            }
        }
        
        return new ViewModel(array(
            'form' => $form,                
        ));
    }

    public function editAction()
    {
        $bankAccountId = (int) $this->params()->fromRoute('id', 0);
        if (!$bankAccountId) {
            return $this->redirect()->toRoute('admin/bankaccount', array(
                'action' => 'add'
            ));
        }
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $bankaccount = $entityManager->getRepository('ErsBase\Entity\PaymentType')->findOneBy(array('id' => $bankAccountId));

        $form = new Form\BankAccount();
        $form->bind($bankaccount);
        $form->get('submit')->setAttribute('value', 'Edit');

        $request = $this->getRequest();
        if ($request->isPost()) {
            #$form->setInputFilter($bankaccount->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $entityManager->persist($form->getData());
                $entityManager->flush();

                $this->flashMessenger()->addSuccessMessage('The bankaccount has been successfully changed');
                return $this->redirect()->toRoute('admin/bankaccount');
            }
        }

        return new ViewModel(array(
            'id' => $bankAccountId,
            'form' => $form,
        ));
    }

    public function deleteAction()
    {
        $bankAccountId = (int) $this->params()->fromRoute('id', 0);
        if (!$bankAccountId) {
            return $this->redirect()->toRoute('admin/bankaccount');
        }
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $bankaccount = $entityManager->getRepository('ErsBase\Entity\PaymentType')
                ->findOneBy(array('id' => $bankAccountId));
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');

            if ($del == 'Yes') {
                $bankAccountId = (int) $request->getPost('id');
                $bankaccount = $entityManager->getRepository('ErsBase\Entity\PaymentType')
                    ->findOneBy(array('id' => $bankAccountId));
                $entityManager->remove($bankaccount);
                $entityManager->flush();
                
                $this->flashMessenger()->addSuccessMessage('The bankaccount has been successfully deleted');
            }

            return $this->redirect()->toRoute('admin/bankaccount');
        }

        return new ViewModel(array(
            'id'    => $bankAccountId,
            'bankaccount' => $bankaccount,
        ));
    }
    
    public function formatAction() {
        $bankAccountId = (int) $this->params()->fromRoute('id', 0);
        if (!$bankAccountId) {
            return $this->redirect()->toRoute('admin/bankaccount');
        }
        
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $bankaccount = $entityManager->getRepository('ErsBase\Entity\PaymentType')
                ->findOneBy(array('id' => $bankAccountId));
        
        $form = new Form\BankAccountFormat();
        
        $statements = $entityManager->getRepository('ErsBase\Entity\BankStatement')
                ->findBy(
                        array('payment_type_id' => $bankaccount->getId()),
                        array(),
                        5
                        );
        
        $colCount = 0;
        foreach($statements as $statement) {
            if($colCount < count($statement->getBankStatementCols())) {
                $colCount = count($statement->getBankStatementCols());
            }
        }
        
        $statement_format = json_decode($bankaccount->getStatementFormat());
        
        if(! $statement_format instanceof \stdClass) {
            $statement_format = new \stdClass();
        }
        
        if(!isset($statement_format->matchKey)) {
            $statement_format->matchKey = 0;
        }
        $form->get('matchKey')->setAttribute('options', 
                $this->getColumnOptions($colCount, $statement_format->matchKey));
        
        if(!isset($statement_format->amount)) {
            $statement_format->amount = 0;
        }
        $form->get('amount')->setAttribute('options', 
                $this->getColumnOptions($colCount, $statement_format->amount));
        
        if(!isset($statement_format->factor)) {
            $statement_format->factor = 1;
        }
        $form->get('factor')->setValue($statement_format->factor);
        
        if(!isset($statement_format->name)) {
            $statement_format->name = 0;
        }
        $form->get('name')->setAttribute('options', 
                $this->getColumnOptions($colCount, $statement_format->name));
        
        if(!isset($statement_format->date)) {
            $statement_format->date = 0;
        }
        $form->get('date')->setAttribute('options', 
                $this->getColumnOptions($colCount, $statement_format->date));
        
        if(!isset($statement_format->sign->col)) {
            $statement_format->sign = new \stdClass();
        }
        if(!isset($statement_format->sign->col)) {
            $statement_format->sign->col = 0;
        }
        $form->get('sign')->setAttribute('options', 
                $this->getColumnOptions($colCount, $statement_format->sign->col));
        if(!isset($statement_format->sign->value)) {
            $statement_format->sign->value = '';
        }
        $form->get('sign-value')->setValue($statement_format->sign->value);
        
        $form->get('id')->setValue($bankaccount->getId());
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $inputFilter = new InputFilter\BankAccountFormat();
            #$form->setInputFilter($inputFilter->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $data = $form->getData();
                
                $bankAccountId = (int) $request->getPost('id');
                $bankaccount = $entityManager->getRepository('ErsBase\Entity\PaymentType')
                    ->findOneBy(array('id' => $bankAccountId));
                
                $format = array(
                    'matchKey' => $data['matchKey'],
                    'amount' => $data['amount'],
                    'factor' => $data['factor'],
                    'name' => $data['name'],
                    'date' => $data['date'],
                    'sign' => array(
                        'col' => $data['sign'],
                        'value' => $data['sign-value']
                    )
                );
                $bankaccount->setStatementFormat(json_encode($format));
                
                $entityManager->persist($bankaccount);
                $entityManager->flush();
                
                $this->flashMessenger()->addSuccessMessage('The format for the bankaccount has been successfully changed');

                return $this->redirect()->toRoute('admin/bankaccount');
            }
        }
        
        return new ViewModel(array(
            'form' => $form,
            'bankaccount' => $bankaccount,
            'colCount' => $colCount,
            'statements' => $statements,
        ));
    }
    
    private function getColumnOptions($count, $default = null) {
        $options = array();
        $options[] = array(
            'value' => '',
            'label' => '',
        );
        for($i=1; $i<=$count; $i++) {
            if($i == $default) {
                $selected = true;
            } else {
                $selected = false;
            }
            $options[] = array(
                'value' => $i,
                'label' => 'column '.$i,
                'selected' => $selected,
            );
        }
        return $options;
    }
    
    public function uploadCsvAction() {
        $form = new Form\UploadCsv();
        
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $accounts = $entityManager->getRepository('ErsBase\Entity\PaymentType')
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
        $form->get('bankaccount_id')->setAttribute('options', $options);
        
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
                
                $bankAccountId = $data['bankaccount_id'];
                $bankaccount = $entityManager->getRepository('ErsBase\Entity\PaymentType')
                    ->findOneBy(array('id' => $bankAccountId));
                
                $file = $data['csv-upload'];
                
                # check mime type of csv file
                
                $bankAccountCsv = new Entity\BankAccountCsv();
                $bankAccountCsv->setCsvFile($file['name']);
                $bankAccountCsv->setPaymentType($bankaccount);
                
                $entityManager->persist($bankAccountCsv);
                
                /*
                 * open file for reading
                 */
                $handle = fopen($file['tmp_name'], "r");
                if (!$handle) {
                    throw new \Exception('Unable to open csv');
                }
                
                # only needed to disable negative statements.
                # DO NOT ADJUST FIELDS ACCORDING TO THE STATEMENT FORMAT HERE!
                $statement_format = json_decode($bankaccount->getStatementFormat());
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
                    $bs->setPaymentType($bankaccount);
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
                    
                    $bankstatement = $entityManager->getRepository('ErsBase\Entity\BankStatement')
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
                    
                    $entityManager->persist($bs);
                    $row++;
                }
                fclose($handle);
                
                $entityManager->flush();
                
                $this->flashMessenger()->addSuccessMessage('The csv for the bankaccount has been successfully uploaded');
                
                return $this->redirect()->toRoute('admin/bankaccount');
            }
        }
        
        return new ViewModel(array(
            'form' => $form,
        ));
    }
    
    public function uploadsAction() {
        $bankAccountId = (int) $this->params()->fromRoute('id', 0);
        if (!$bankAccountId) {
            return $this->redirect()->toRoute('admin/bankaccount', array());
        }
        
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $bankaccount = $entityManager->getRepository('ErsBase\Entity\PaymentType')
                ->findOneBy(array('id' => $bankAccountId));
        
        return new ViewModel(array(
            'bankaccount' => $bankaccount,
        ));
    }
    
    public function deleteUploadAction()
    {
        $bankAccountId = (int) $this->params()->fromRoute('id', 0);
        if (!$bankAccountId) {
            return $this->redirect()->toRoute('admin/bankaccount');
        }
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $csv = $entityManager->getRepository('ErsBase\Entity\BankAccountCsv')
                ->findOneBy(array('id' => $bankAccountId));
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');

            if ($del == 'Yes') {
                $bankAccountId = (int) $request->getPost('id');
                $csv = $entityManager->getRepository('ErsBase\Entity\BankAccountCsv')
                    ->findOneBy(array('id' => $bankAccountId));
                /*if($csv->hasMatch()) {
                    return $this->redirect()->toRoute('admin/bankaccount');
                }*/
                
                $statusOrdered = $entityManager->getRepository('ErsBase\Entity\Status')
                    ->findOneBy(array('value' => 'ordered'));
                $statusService = $this->getServiceLocator()
                        ->get('ErsBase\Service\StatusService');
                
                foreach($csv->getBankStatements() as $bs) {
                    /*
                     * Hint: cannot be deleted if there's already a match.
                     * If somebody says it cannot be done, get out of the way 
                     * to let me do it.
                     */
                    foreach($bs->getMatches() as $match) {
                        $order = $match->getOrder();
                    
                        $statusService->setOrderStatus($order, $statusOrdered, false);
                        $entityManager->remove($match);
                    }
                    
                    $entityManager->remove($bs);
                    foreach($bs->getBankStatementCols() as $col) {
                        $entityManager->remove($col);
                    }
                }
                $entityManager->remove($csv);
                $entityManager->flush();
                
                $this->flashMessenger()->addSuccessMessage('The csv for the bankaccount has been successfully deleted');
            }

            return $this->redirect()->toRoute('admin/bankaccount');
        }

        return new ViewModel(array(
            'csv' => $csv,
        ));
    }
    
    public function detailAction() {
        $bankAccountId = (int) $this->params()->fromRoute('id', 0);
        if (!$bankAccountId) {
            return $this->redirect()->toRoute('admin/bankaccount', array());
        }
        
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $bankaccount = $entityManager->getRepository('ErsBase\Entity\PaymentType')
                ->findOneBy(array('id' => $bankAccountId));
        
        switch($bankaccount->getType()) {
            case 'sepa':
                $form = new Form\AccountSepabankDetail($entityManager);
                break;
            case 'ipayment':
                $form = new Form\AccountIpaymentDetail($entityManager);
                if(empty($bankaccount->getTrxCurrency())) {
                    $bankaccount->setTrxCurrency('EUR');
                }
                if(empty($bankaccount->getAction())) {
                    $bankaccount->setAction('https://ipayment.de/merchant/%account_id%/processor/2.0/');
                }
                break;
            case 'paypal':
                $form = new Form\AccountPaypalDetail($entityManager);
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

                $form = new Form\AccountUnknownDetail($entityManager);
                $form->get('type')->setAttribute('options', $options);
                break;
        }
        
        $form->bind($bankaccount);
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $entityManager->persist($form->getData());
                $entityManager->flush();

                return $this->redirect()->toRoute('admin/bankaccount');
            }
        }
        
        return new ViewModel(array(
            'form' => $form,
        ));
    }
}
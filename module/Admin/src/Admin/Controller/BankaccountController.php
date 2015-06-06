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
use ersEntity\Entity;

class BankaccountController extends AbstractActionController {
 
    public function indexAction()
    {
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $accounts = $em->getRepository("ersEntity\Entity\BankAccount")
                ->findBy(array());
        
        return new ViewModel(array(
            'accounts' => $accounts,
        ));
    }
    
    public function addAction()
    {
        $form = new Form\BankAccount();
        $form->get('submit')->setValue('Add');
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $bankaccount = new Entity\BankAccount();
            
            $form->setInputFilter($bankaccount->getInputFilter());
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $bankaccount->populate($form->getData());
                
                $em = $this->getServiceLocator()
                    ->get('Doctrine\ORM\EntityManager');
                
                $em->persist($bankaccount);
                $em->flush();

                return $this->redirect()->toRoute('admin/bankaccount');
            } else {
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
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/bankaccount', array(
                'action' => 'add'
            ));
        }
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $bankaccount = $em->getRepository("ersEntity\Entity\BankAccount")->findOneBy(array('id' => $id));

        $form = new Form\BankAccount();
        $form->bind($bankaccount);
        $form->get('submit')->setAttribute('value', 'Edit');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($bankaccount->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $em->persist($form->getData());
                $em->flush();

                return $this->redirect()->toRoute('admin/bankaccount');
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
            return $this->redirect()->toRoute('admin/bankaccount');
        }
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $bankaccount = $em->getRepository("ersEntity\Entity\BankAccount")
                ->findOneBy(array('id' => $id));
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');

            if ($del == 'Yes') {
                $id = (int) $request->getPost('id');
                $bankaccount = $em->getRepository("ersEntity\Entity\BankAccount")
                    ->findOneBy(array('id' => $id));
                $em->remove($bankaccount);
                $em->flush();
            }

            return $this->redirect()->toRoute('admin/bankaccount');
        }

        return new ViewModel(array(
            'id'    => $id,
            'bankaccount' => $bankaccount,
        ));
    }
    
    public function formatAction() {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/bankaccount');
        }
        
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $bankaccount = $em->getRepository("ersEntity\Entity\BankAccount")
                ->findOneBy(array('id' => $id));
        
        $form = new Form\BankAccountFormat();
        
        $statements = $em->getRepository("ersEntity\Entity\BankStatement")
                ->findBy(
                        array('BankAccount_id' => $bankaccount->getId()),
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
        
        $form->get('id')->setValue($bankaccount->getId());
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $inputFilter = new InputFilter\BankAccountFormat();
            $form->setInputFilter($inputFilter->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $data = $form->getData();
                
                $id = (int) $request->getPost('id');
                $bankaccount = $em->getRepository("ersEntity\Entity\BankAccount")
                    ->findOneBy(array('id' => $id));
                
                $format = array(
                    'matchKey' => $data['matchKey'],
                    'amount' => $data['amount'],
                    'name' => $data['name'],
                    'date' => $data['date'],
                );
                $bankaccount->setStatementFormat(json_encode($format));
                
                $em->persist($bankaccount);
                $em->flush();

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
        
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $accounts = $em->getRepository("ersEntity\Entity\BankAccount")
                ->findBy(array());
        
        $options = array();
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
                
                $id = $data['bankaccount_id'];
                $bankaccount = $em->getRepository("ersEntity\Entity\BankAccount")
                    ->findOneBy(array('id' => $id));
                
                $file = $data['csv-upload'];
                
                $bankAccountCsv = new Entity\BankAccountCsv();
                $bankAccountCsv->setCsvFile($file['name']);
                $bankAccountCsv->setBankAccount($bankaccount);
                
                $em->persist($bankAccountCsv);
                
                /*
                 * open file for reading
                 */
                $handle = fopen($file['tmp_name'], "r");
                if (!$handle) {
                    throw new \Exception('Unable to open csv');
                }
                
                /*
                 * read every line in the file and generate bank statement entities
                 */
                $row = 1;
                while (($row_data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    #$logger->info($row_data);
                
                    $hash = md5(implode($row_data));
                    $bankstatement = $em->getRepository("ersEntity\Entity\BankStatement")
                        ->findOneBy(array('hash' => $hash));
                    if($bankstatement) {
                        #$logger->info('bank statement already exists in db');
                        continue;
                    }
                    
                    $bs = new Entity\BankStatement();
                    #$bs->setBankStatementCols($row_data);
                    $bs->setBankAccount($bankaccount);
                    $bs->setBankAccountCsv($bankAccountCsv);
                    $bs->setHash($hash);
                    $bs->setStatus('new');
                    foreach($row_data as $column => $value) {
                        $bsc = new Entity\BankStatementCol();
                        $bsc->setColumn(($column+1));
                        $bsc->setValue($value);
                        $bsc->setBankStatement($bs);
                        $bs->addBankStatementCol($bsc);
                    }
                 
                    #$bankaccount->addBankStatement($bs);
                    $em->persist($bs);
                    $row++;
                }
                fclose($handle);

                /*
                 * save everything to database
                 */
                #$em->persist($bankaccount);
                $em->flush();
                
                return $this->redirect()->toRoute('admin/bankaccount');
            }
        }
        
        return new ViewModel(array(
            'form' => $form,
        ));
    }
    
    public function detailAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/order', array());
        }
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $order = $em->getRepository("ersEntity\Entity\Order")
                ->findOneBy(array('id' => $id));
        
        return new ViewModel(array(
            'order' => $order,
        ));
    }
    
    public function uploadsAction() {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/bankaccount', array());
        }
        
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $bankaccount = $em->getRepository("ersEntity\Entity\BankAccount")
                ->findOneBy(array('id' => $id));
        
        return new ViewModel(array(
            'bankaccount' => $bankaccount,
        ));
    }
    
    public function deleteUploadAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/bankaccount');
        }
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $csv = $em->getRepository("ersEntity\Entity\BankAccountCsv")
                ->findOneBy(array('id' => $id));
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');

            if ($del == 'Yes') {
                $id = (int) $request->getPost('id');
                $csv = $em->getRepository("ersEntity\Entity\BankAccountCsv")
                    ->findOneBy(array('id' => $id));
                if($csv->hasMatch()) {
                    return $this->redirect()->toRoute('admin/bankaccount');
                }
                foreach($csv->getBankStatements() as $bs) {
                    /*
                     * Hint: cannot be deleted if there's already a match.
                     */
                    $em->remove($bs);
                    foreach($bs->getBankStatementCols() as $col) {
                        $em->remove($col);
                    }
                }
                $em->remove($csv);
                $em->flush();
            }

            return $this->redirect()->toRoute('admin/bankaccount');
        }

        return new ViewModel(array(
            'csv' => $csv,
        ));
    }
}
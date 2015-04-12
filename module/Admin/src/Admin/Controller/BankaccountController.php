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
use ersEntity\Entity;

class BankaccountController extends AbstractActionController {
 
    public function indexAction()
    {
        $em = $this
            ->getServiceLocator()
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
                
                $em = $this
                    ->getServiceLocator()
                    ->get('Doctrine\ORM\EntityManager');
                
                $em->persist($bankaccount);
                $em->flush();

                return $this->redirect()->toRoute('admin/bankaccount');
            } else {
                $logger = $this
                    ->getServiceLocator()
                    ->get('Logger');
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
        $em = $this
            ->getServiceLocator()
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
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $bankaccount = $em->getRepository("ersEntity\Entity\BankAccount")
                ->findOneBy(array('id' => $id));
        $productprices = $bankaccount->getProductPrices();
        
        $qb = $em->getRepository("ersEntity\Entity\PaymentType")->createQueryBuilder('n');
        $paymenttypes = $qb->where(
                $qb->expr()->orX(
                    $qb->expr()->eq('n.activeFrom_id', $id),
                    $qb->expr()->eq('n.activeUntil_id', $id)
            ))->getQuery()->getResult();
        
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
            'bankstatements' => $bankstatements,
        ));
    }
    
    public function formatAction() {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/bankaccount');
        }
        
        $form = new Form\BankAccountFormat();
        
        $options = array();
        $options[] = array(
            'value' => '',
            'label' => '',
        );
        for($i=1; $i<=10; $i++) {
            $options[] = array(
                'value' => $i,
                'label' => 'column '.$i,
            );
        }
        
        $form->get('matchKey')->setAttribute('options', $options);
        $form->get('amount')->setAttribute('options', $options);
        $form->get('name')->setAttribute('options', $options);
        $form->get('date')->setAttribute('options', $options);
        
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $bankaccount = $em->getRepository("ersEntity\Entity\BankAccount")
                ->findOneBy(array('id' => $id));
        
        $statements = $em->getRepository("ersEntity\Entity\BankStatement")
                ->findBy(
                        array('BankAccount_id' => $bankaccount->getId()),
                        array(),
                        5
                        );
        
        return new ViewModel(array(
            'form' => $form,
            'bankaccount' => $bankaccount,
            'statements' => $statements,
        ));
    }
    
    public function uploadCsvAction() {
        $form = new Form\UploadCsv();
        
        $em = $this
            ->getServiceLocator()
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
                $logger = $this
                    ->getServiceLocator()
                    ->get('Logger');
                $logger->info($file);
                
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
                    $bs->setBankStatements($row_data);
                    $bs->setBankAccount($bankaccount);
                    $bs->setHash($hash);
                    
                    $bankaccount->addBankStatement($bs);
                    $row++;
                }
                fclose($handle);

                /*
                 * save everything to database
                 */
                $em->persist($bankaccount);
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
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $order = $em->getRepository("ersEntity\Entity\Order")
                ->findOneBy(array('id' => $id));
        
        return new ViewModel(array(
            'order' => $order,
        ));
    }   
}
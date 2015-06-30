<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

class AjaxController extends AbstractActionController {
    public function indexAction() {
        return new ViewModel();
    }
    public function matchingOrderAction() {
        $id = (int) $this->params()->fromRoute('id', 0);
        
        $viewModel = new ViewModel();
        $viewModel->setTemplate("partial/ajax/matching-order");
        
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $order = $em->getRepository("ersEntity\Entity\Order")
                #->findOneBy(array('id' => '297'));
                #->findOneBy(array('id' => '12'));
                #->findOneBy(array('id' => '54'));
                ->findOneBy(array('id' => $id));
        
        $viewModel->setVariable("order", $order);

        $viewModel->setTerminal(true);

        return $viewModel;
    }
    
    public function matchingBankstatementAction() {
        $id = (int) $this->params()->fromRoute('id', 0);
        
        $viewModel = new ViewModel();
        $viewModel->setTemplate("partial/ajax/matching-bankstatement");
        
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $bankaccount = $em->getRepository("ersEntity\Entity\BankAccount")
                ->findOneBy(array('id' => $id));
        
        $qb = $em->getRepository("ersEntity\Entity\BankStatement")->createQueryBuilder('s');
        $qb->leftJoin('s.matches', 'm');
        if($bankaccount->getVirtual()) {
            $qb->where(
                $qb->expr()->andX(
                    $qb->expr()->eq('s.BankAccount_id', '?1'),
                    $qb->expr()->neq('s.status', '?2')
                )
            );
        } else {
            $qb->where(
                $qb->expr()->andX(
                    $qb->expr()->eq('s.BankAccount_id', '?1'),
                    $qb->expr()->neq('s.status', '?2'),
                    $qb->expr()->orX(
                        $qb->expr()->isNull('m.BankStatement_id'),
                        $qb->expr()->neq('m.status', '?3')
                    )
                )
            );
        }
        
        $qb->setParameter(1, $id);
        $qb->setParameter(2, 'disabled');
        $qb->setParameter(3, 'disabled');
        error_log($qb->getQuery()->getSql());
        $statements = $qb->getQuery()->getResult();

        $viewModel->setVariable("statements", $statements);
        $viewModel->setTerminal(true);
        return $viewModel;
    }
    
    public function matchingStatementcolsAction() {
        $id = (int) $this->params()->fromRoute('id', 0);
        
        $viewModel = new ViewModel();
        $viewModel->setTemplate("partial/ajax/matching-statementcols");
        
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $statement = $em->getRepository("ersEntity\Entity\BankStatement")
                ->findOneBy(array('id' => $id));
        
        $viewModel->setVariable("statement", $statement);
        $viewModel->setTerminal(true);
        return $viewModel;
    }
    
    public function virtualBankaccountAction() {
        $id = (int) $this->params()->fromRoute('id', 0);
        
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $bankaccount = $em->getRepository("ersEntity\Entity\BankAccount")
                ->findOneBy(array('id' => $id));
        
        return $bankaccount->getVirtual();
    }
}
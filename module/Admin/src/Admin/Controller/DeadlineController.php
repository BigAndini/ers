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

class DeadlineController extends AbstractActionController {
    
    public function indexAction()
    {
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        return new ViewModel(array(
            'deadlines' => $entityManager->getRepository('ErsBase\Entity\Deadline')
                ->findBy(array(), array('deadline' => 'ASC')),
         ));
    }

    public function addAction()
    {
        $form = new Form\Deadline();
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $deadline = new Entity\Deadline();
            
            #$form->setInputFilter($deadline->getInputFilter());
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $deadline->populate($form->getData());
                
                $entityManager = $this->getServiceLocator()
                    ->get('Doctrine\ORM\EntityManager');
                
                $entityManager->persist($deadline);
                $entityManager->flush();

                return $this->redirect()->toRoute('admin/deadline');
            }
            $logger = $this->getServiceLocator()->get('Logger');
            $logger->warn($form->getMessages());
        }
        
        return new ViewModel(array(
            'form' => $form,                
        ));
    }

    public function editAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/deadline', array(
                'action' => 'add'
            ));
        }
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $deadline = $entityManager->getRepository('ErsBase\Entity\Deadline')->findOneBy(array('id' => $id));

        $form = new Form\Deadline();
        $form->bind($deadline);

        $request = $this->getRequest();
        if ($request->isPost()) {
            #$form->setInputFilter($deadline->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $entityManager->persist($form->getData());
                $entityManager->flush();

                return $this->redirect()->toRoute('admin/deadline');
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
            return $this->redirect()->toRoute('admin/deadline');
        }
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $deadline = $entityManager->getRepository('ErsBase\Entity\Deadline')
                ->findOneBy(array('id' => $id));
        $productprices = $deadline->getProductPrices();
        
        $queryBuilder = $entityManager->getRepository('ErsBase\Entity\PaymentType')->createQueryBuilder('n');
        $paymenttypes = $queryBuilder->where(
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->eq('n.active_from_id', $id),
                    $queryBuilder->expr()->eq('n.active_until_id', $id)
            ))->getQuery()->getResult();
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');

            if ($del == 'Yes') {
                $id = (int) $request->getPost('id');
                $deadline = $entityManager->getRepository('ErsBase\Entity\Deadline')
                    ->findOneBy(array('id' => $id));
                $entityManager->remove($deadline);
                $entityManager->flush();
            }

            return $this->redirect()->toRoute('admin/deadline');
        }

        return new ViewModel(array(
            'id'    => $id,
            'deadline' => $deadline,
            'productprices' => $productprices,
            'paymenttypes' => $paymenttypes,
        ));
    }
}
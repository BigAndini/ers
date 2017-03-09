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
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        return new ViewModel(array(
            'deadlines' => $em->getRepository('ErsBase\Entity\Deadline')
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
                
                $em = $this->getServiceLocator()
                    ->get('Doctrine\ORM\EntityManager');
                
                $em->persist($deadline);
                $em->flush();

                return $this->redirect()->toRoute('admin/deadline');
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
            return $this->redirect()->toRoute('admin/deadline', array(
                'action' => 'add'
            ));
        }
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $deadline = $em->getRepository('ErsBase\Entity\Deadline')->findOneBy(array('id' => $id));

        $form = new Form\Deadline();
        $form->bind($deadline);

        $request = $this->getRequest();
        if ($request->isPost()) {
            #$form->setInputFilter($deadline->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $em->persist($form->getData());
                $em->flush();

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
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $deadline = $em->getRepository('ErsBase\Entity\Deadline')
                ->findOneBy(array('id' => $id));
        $productprices = $deadline->getProductPrices();
        
        $qb = $em->getRepository('ErsBase\Entity\PaymentType')->createQueryBuilder('n');
        $paymenttypes = $qb->where(
                $qb->expr()->orX(
                    $qb->expr()->eq('n.active_from_id', $id),
                    $qb->expr()->eq('n.active_until_id', $id)
            ))->getQuery()->getResult();
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');

            if ($del == 'Yes') {
                $id = (int) $request->getPost('id');
                $deadline = $em->getRepository('ErsBase\Entity\Deadline')
                    ->findOneBy(array('id' => $id));
                $em->remove($deadline);
                $em->flush();
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
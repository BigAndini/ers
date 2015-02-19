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

class DeadlineController extends AbstractActionController {
    
    public function indexAction()
    {
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        return new ViewModel(array(
            'deadlines' => $em->getRepository("ersEntity\Entity\Deadline")
                ->findBy(array(), array('deadline' => 'ASC')),
         ));
    }

    public function addAction()
    {
        $form = new Form\Deadline();
        $form->get('submit')->setValue('Add');
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $deadline = new Entity\Deadline();
            
            $form->setInputFilter($deadline->getInputFilter());
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $deadline->populate($form->getData());
                
                $em = $this
                    ->getServiceLocator()
                    ->get('Doctrine\ORM\EntityManager');
                
                $em->persist($deadline);
                $em->flush();

                return $this->redirect()->toRoute('admin/deadline');
            } else {
                error_log(var_export($form->getMessages(), true));
            }
        }
        
        return array(
            'form' => $form,                
        );
    }

    public function editAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/deadline', array(
                'action' => 'add'
            ));
        }
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $deadline = $em->getRepository("ersEntity\Entity\Deadline")->findOneBy(array('id' => $id));

        $form = new Form\Deadline();
        $form->bind($deadline);
        $form->get('submit')->setAttribute('value', 'Edit');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($deadline->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $em->persist($form->getData());
                $em->flush();

                return $this->redirect()->toRoute('admin/deadline');
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
            return $this->redirect()->toRoute('admin/deadline');
        }
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $deadline = $em->getRepository("ersEntity\Entity\Deadline")
                ->findOneBy(array('id' => $id));
        $productprices = $deadline->getProductPrices();

        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');

            if ($del == 'Yes') {
                $id = (int) $request->getPost('id');
                $deadline = $em->getRepository("ersEntity\Entity\Deadline")
                    ->findOneBy(array('id' => $id));
                $em->remove($deadline);
                $em->flush();
            }

            return $this->redirect()->toRoute('admin/deadline');
        }

        return array(
            'id'    => $id,
            'deadline' => $deadline,
            'productprices' => $productprices,
        );
    }
}
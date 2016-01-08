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

class CounterController extends AbstractActionController {
    
    public function indexAction()
    {
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        return new ViewModel(array(
            'counters' => $em->getRepository("ErsBase\Entity\Counter")->findAll(),
         ));
    }

    public function addAction()
    {
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');

        $form = new Form\Counter($em);
        $form->get('submit')->setValue('Add');
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $counter = new Entity\Counter();
            
            #$form->setInputFilter($counter->getInputFilter());
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $counter->populate($form->getData());
                $counter->addProductVariantValue($em->getRepository('ErsBase\Entity\ProductVariantValue')->find((int)$form->get('productVariantValue')->getValue()));
                
                $em->persist($counter);
                $em->flush();

                return $this->redirect()->toRoute('admin/counter');
            } else {
                $logger = $this->getServiceLocator()->get('Logger');
                $logger->warn($form->getMessages());
            }
            
            var_dump($form->getMessages());
        }
        
        return new ViewModel(array(
            'form' => $form,                
        ));
    }

    public function editAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/counter', array('action' => 'add'));
        }
        
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $counter = $em->getRepository("ErsBase\Entity\Counter")->find($id);
        if(!$counter) {
            return $this->notFoundAction();
        }

        $form = new Form\Counter($em);
        $form->bind($counter);
        $form->get('submit')->setAttribute('value', 'Edit');
        
        // hack the many-to-many mapping into a single form field
        if($counter->getProductVariantValues()->count() == 1) {
            $form->get('productVariantValue')->setValue($counter->getProductVariantValues()[0]->getId());
        }

        $request = $this->getRequest();
        if ($request->isPost()) {
            #$form->setInputFilter($counter->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $counter->getProductVariantValues()->clear();
                $counter->addProductVariantValue($em->getRepository('ErsBase\Entity\ProductVariantValue')->find((int)$form->get('productVariantValue')->getValue()));
                
                $em->persist($counter);
                $em->flush();

                return $this->redirect()->toRoute('admin/counter');
            }
        }

        return new ViewModel(array(
            'id' => $id,
            'form' => $form,
        ));
    }

    /*
     * The delete action is for Agegroups, Counters and Counters the same.
     */
    public function deleteAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/counter');
        }
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $counter = $em->getRepository("ErsBase\Entity\Counter")
                ->find($id);
        if(!$counter) {
            return $this->notFoundAction();
        }

        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');

            if ($del == 'Yes') {
                $id = (int) $request->getPost('id');
                $counter = $em->getRepository("ErsBase\Entity\Counter")
                    ->findOneBy(array('id' => $id));
                $em->remove($counter);
                $em->flush();
            }

            return $this->redirect()->toRoute('admin/counter');
        }

        return new ViewModel(array(
            'id'    => $id,
            'counter' => $counter,
        ));
    }
}
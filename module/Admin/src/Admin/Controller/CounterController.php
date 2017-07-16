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
        $entityManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $counterService = $this->getServiceLocator()->get('ErsBase\Service\TicketCounterService');
        
        $counters = $entityManager->getRepository('ErsBase\Entity\Counter')->findAll();
        $counterCurrentValues = [];
        foreach($counters as $counter) {
            $counterCurrentValues[$counter->getId()] = $counterService->getCurrentItemCount($counter);
        }
        
        return new ViewModel(array(
            'counters' => $counters,
            'counterCurrentValues' => $counterCurrentValues,
         ));
    }

    public function addAction()
    {
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');

        $form = new Form\Counter($entityManager);
        $form->get('submit')->setValue('Add');
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $counter = new Entity\Counter();
            
            #$form->setInputFilter($counter->getInputFilter());
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $counter->populate($form->getData());
                $counter->addProductVariantValue($entityManager->getRepository('ErsBase\Entity\ProductVariantValue')->find((int)$form->get('productVariantValue')->getValue()));
                
                $entityManager->persist($counter);
                $entityManager->flush();

                $this->flashMessenger()->addSuccessMessage('The counter has been successfully added');
                return $this->redirect()->toRoute('admin/counter');
            } else {
                $this->flashMessenger()->addErrorMessage($form->getMessages());
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
        $counterId = (int) $this->params()->fromRoute('id', 0);
        if (!$counterId) {
            return $this->redirect()->toRoute('admin/counter', array('action' => 'add'));
        }
        
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $counter = $entityManager->getRepository('ErsBase\Entity\Counter')->find($counterId);
        if(!$counter) {
            return $this->notFoundAction();
        }

        $form = new Form\Counter($entityManager);
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
                $counter->addProductVariantValue($entityManager->getRepository('ErsBase\Entity\ProductVariantValue')->find((int)$form->get('productVariantValue')->getValue()));
                
                $entityManager->persist($counter);
                $entityManager->flush();
                
                $this->flashMessenger()->addSuccessMessage('The counter has been successfully changed');

                return $this->redirect()->toRoute('admin/counter');
            }
        }

        return new ViewModel(array(
            'id' => $counterId,
            'form' => $form,
        ));
    }

    /*
     * The delete action is for Agegroups, Counters and Counters the same.
     */
    public function deleteAction()
    {
        $counterId = (int) $this->params()->fromRoute('id', 0);
        if (!$counterId) {
            return $this->redirect()->toRoute('admin/counter');
        }
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $counter = $entityManager->getRepository('ErsBase\Entity\Counter')
                ->find($counterId);
        if(!$counter) {
            return $this->notFoundAction();
        }

        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');

            if ($del == 'Yes') {
                $counterId = (int) $request->getPost('id');
                $counter = $entityManager->getRepository('ErsBase\Entity\Counter')
                    ->findOneBy(array('id' => $counterId));
                $entityManager->remove($counter);
                $entityManager->flush();
                
                $this->flashMessenger()->addSuccessMessage('The counter has been successfully deleted');
            }

            return $this->redirect()->toRoute('admin/counter');
        }

        return new ViewModel(array(
            'id'    => $counterId,
            'counter' => $counter,
        ));
    }
}
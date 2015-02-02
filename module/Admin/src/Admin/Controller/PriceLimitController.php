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
#use RegistrationSystem\Form\UserForm;
use Admin\Form;
use Zend\Form\Element;

class PriceLimitController extends AbstractActionController {
    
    public function indexAction()
    {
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        return new ViewModel(array(
            'limits' => $em->getRepository("ersEntity\Entity\PriceLimit")->findAll(),
         ));
    }

    /*
     * Agegroup
     */
    public function addAgegroupAction()
    {
        return new ViewModel();
    }
    public function editAgegroupAction()
    {
        return new ViewModel();
    }
    public function copyAgegroupAction()
    {
        return new ViewModel();
    }
    
    /*
     * Counter
     */
    public function addCounterAction()
    {
        return new ViewModel();
    }
    
    public function editCounterAction()
    {
        return new ViewModel();
    }
    public function copyCounterAction()
    {
        return new ViewModel();
    }
    
    /*
     * Deadline
     */
    public function addDeadlineAction()
    {
        $form = new Form\PriceLimitDeadlineForm();
        $form->get('submit')->setValue('Add');
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $pricelimit = new Entity\PriceLimit();
            $form->setInputFilter($pricelimit->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $em = $this
                    ->getServiceLocator()
                    ->get('Doctrine\ORM\EntityManager');
                $pricelimit->exchangeArray($form->getData());
                $pricelimit->setType('deadline');
                
                $em->persist($pricelimit);
                $em->flush();

                return $this->redirect()->toRoute('admin/price-limit');
            } else {
                error_log(var_export($form->getMessages(), true));
            }
        }
        
        return array(
            'form' => $form,                
        );
    }

    public function editDeadlineAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/price-limit', array(
                'action' => 'addDeadline'
            ));
        }
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $pricelimit = $em->getRepository("ersEntity\Entity\PriceLimit")->findOneBy(array('id' => $id));

        $form = new Form\PriceLimitDeadlineForm();
        $form->bind($pricelimit);
        $form->get('submit')->setAttribute('value', 'Edit');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($pricelimit->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $pricelimit->exchangeArray($form->getData());
                
                $em->persist($pricelimit);
                $em->flush();

                return $this->redirect()->toRoute('admin/price-limit');
            }
        }

        return array(
            'id' => $id,
            'form' => $form,
        );
    }
    
    public function copyDeadlineAction()
    {   
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/price-limit', array(
                'action' => 'addDeadline'
            ));
        }
        $product = $this->getTable('Product')->getById($id);

        $form = $this->getServiceLocator()->get('Form\PriceLimitForm');
        $form->bind($product);
        $form->get('submit')->setAttribute('value', 'Copy');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($product->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $new_id = $this->getTable('PriceLimit')->save($form->getData());
                error_log('saved price limit: '.$id);

                // Redirect to list of products
                return $this->redirect()->toRoute('admin/price-limit');
            } else {
                $messages = $form->getMessages();
                error_log('got '.count($messages).' messages.');
                foreach($messages as $message) {
                    foreach($message as $m) {
                        error_log($m);
                    }
                }
            }
        }

        return array(
            'id' => $id,
            'form' => $form,
        );
    }

    /*
     * The delete action is for Agegroups, Counters and Deadlines the same.
     */
    public function deleteAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/price-limit');
        }
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $productprice = $em->getRepository("ersEntity\Entity\PriceLimit")
                ->findOneBy(array('id' => $id));

        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');

            if ($del == 'Yes') {
                $id = (int) $request->getPost('id');
                $productprice = $em->getRepository("ersEntity\Entity\PriceLimit")
                    ->findOneBy(array('id' => $id));
                $em->remove($productprice);
                $em->flush();
            }

            // Redirect to list of products
            return $this->redirect()->toRoute('admin/price-limit');
        }

        return array(
            'id'    => $id,
            'pricelimit' => $productprice,
        );
    }
}
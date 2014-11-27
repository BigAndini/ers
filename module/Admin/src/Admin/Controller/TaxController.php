<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Admin\Model\Entity;
#use RegistrationSystem\Form\UserForm;
use Admin\Form;

class TaxController extends AbstractActionController {
    protected $table;
    
    public function getTable()
    {
        if (!$this->table) {
            $sm = $this->getServiceLocator();
            $this->table = $sm->get('Admin\Model\TaxTable');
        }
        return $this->table;
    }
    public function indexAction()
    {
        return new ViewModel(array(
             'taxes' => $this->getTable()->fetchAll('percentage ASC'),
         ));
    }

    public function addAction()
    {
        $form = new Form\TaxForm();
        $form->get('submit')->setValue('Add');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $tax = new Entity\Tax();
            $form->setInputFilter($tax->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $tax->exchangeArray($form->getData());
                error_log('name: '.$tax->name);
                error_log('percentage: '.$tax->percentage);
                foreach($form->getData() as $message) {
                    if(is_array($message)) {
                        foreach($message as $k => $v) {
                            error_log($k.' -> '.$v);
                        }
                    } else {
                        error_log($message);
                    }
                }
                $this->getTable()->save($tax);

                // Redirect to list of taxes
                return $this->redirect()->toRoute('admin/tax');
            }
        }
        return array('form' => $form);
    }

    public function editAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/tax', array(
                'action' => 'add'
            ));
        }
        $tax = $this->getTable()->getById($id);

        $form  = new Form\TaxForm();
        $form->bind($tax);
        $form->get('submit')->setAttribute('value', 'Edit');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($tax->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $this->getTable()->save($form->getData());

                // Redirect to list of taxes
                return $this->redirect()->toRoute('admin/tax');
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
            return $this->redirect()->toRoute('admin/tax');
        }

        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');

            if ($del == 'Yes') {
                $id = (int) $request->getPost('id');
                $this->getTable()->removeById($id);
            }

            // Redirect to list of taxes
            return $this->redirect()->toRoute('admin/tax');
        }

        return array(
            'id'    => $id,
            'tax' => $this->getTable()->getById($id),
        );
    }
}
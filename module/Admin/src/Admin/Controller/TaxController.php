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

class TaxController extends AbstractActionController {
    public function indexAction()
    {
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        return new ViewModel(array(
            'taxes' => $em->getRepository("ersEntity\Entity\Tax")->findBy(array(), array('percentage' => 'ASC')),
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
                $tax->populate($form->getData());

                $em = $this
                    ->getServiceLocator()
                    ->get('Doctrine\ORM\EntityManager');
                $em->persist($tax);
                $em->flush();
                #$this->getTable()->save($tax);

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
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $tax = $em->getRepository("ersEntity\Entity\Tax")->findOneBy(array('id' => $id));
        #$tax = $this->getTable()->getById($id);

        $form  = new Form\TaxForm();
        $form->bind($tax);
        $form->get('submit')->setAttribute('value', 'Edit');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($tax->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                #$tax->populate($form->getData());
                #$em->persist($tax);
                $em->persist($form->getData());
                $em->flush();

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
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');

            if ($del == 'Yes') {
                
                $id = (int) $request->getPost('id');
                $tax = $em->getRepository("ersEntity\Entity\Tax")
                        ->findOneBy(array('id' => $id));
                $em->remove($tax);
                $em->flush();
                
                /*$id = (int) $request->getPost('id');
                $this->getTable()->removeById($id);*/
            }

            // Redirect to list of taxes
            return $this->redirect()->toRoute('admin/tax');
        }

        return array(
            'id'    => $id,
            'tax' => $tax = $em->getRepository("ersEntity\Entity\Tax")
                ->findOneBy(array('id' => $id)),
        );
    }
}
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
use ErsBase\Service;

class TaxController extends AbstractActionController {
    public function indexAction()
    {
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        return new ViewModel(array(
            'taxes' => $em->getRepository("ErsBase\Entity\Tax")->findBy(array(), array('percentage' => 'ASC')),
        ));
    }

    public function addAction()
    {
        $forrest = new Service\BreadcrumbService();
        if(!$forrest->exists('tax')) {
            $forrest->set('tax', 'admin/tax');
        }
        $breadcrumb = $forrest->get('tax');
        
        $form = new Form\Tax();
        $form->get('submit')->setValue('Add');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $tax = new Entity\Tax();
            $form->setInputFilter($tax->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $tax->populate($form->getData());

                $em = $this->getServiceLocator()
                    ->get('Doctrine\ORM\EntityManager');
                $em->persist($tax);
                $em->flush();

                return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
            }
        }
        return new ViewModel(array(
            'form' => $form,
            'breadcrumb' => $breadcrumb,
        ));
    }

    public function editAction()
    {
        $forrest = new Service\BreadcrumbService();
        $breadcrumb = $forrest->get('tax');
        if(!$forrest->exists('tax')) {
            $forrest->set('tax', 'admin/tax');
        }
        
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/tax', array(
                'action' => 'add'
            ));
        }
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $tax = $em->getRepository("ErsBase\Entity\Tax")->findOneBy(array('id' => $id));

        $form  = new Form\Tax();
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

                return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
            }
        }

        return new ViewModel(array(
            'id' => $id,
            'form' => $form,
            'breadcrumb' => $breadcrumb,
        ));
    }

    public function deleteAction()
    {
        $forrest = new Service\BreadcrumbService();
        if(!$forrest->exists('tax')) {
            $forrest->set('tax', 'admin/tax');
        }
        $breadcrumb = $forrest->get('tax');
        
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
        }
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');

            if ($del == 'Yes') {
                
                $id = (int) $request->getPost('id');
                $tax = $em->getRepository("ErsBase\Entity\Tax")
                        ->findOneBy(array('id' => $id));
                $em->remove($tax);
                $em->flush();
            }

            return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
        }

        return new ViewModel(array(
            'id'    => $id,
            'tax' => $tax = $em->getRepository("ErsBase\Entity\Tax")
                ->findOneBy(array('id' => $id)),
            'breadcrumb' => $breadcrumb,
        ));
    }
}
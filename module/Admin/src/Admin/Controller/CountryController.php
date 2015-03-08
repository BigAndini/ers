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
use Admin\Service;

class CountryController extends AbstractActionController {
    public function indexAction()
    {
        $forrest = new Service\BreadcrumbFactory();
        $forrest->set('country', 'admin/country');
        
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $qb1 = $em->getRepository("ersEntity\Entity\Country")->createQueryBuilder('n');
        $qb1->where($qb1->expr()->isNotNull('n.ordering'));
        $qb1->orderBy('n.ordering', 'ASC');
        $result1 = $qb1->getQuery()->getResult();
        
        $qb2 = $em->getRepository("ersEntity\Entity\Country")->createQueryBuilder('n');
        $qb2->where($qb2->expr()->isNull('n.ordering'));
        $qb2->orderBy('n.name', 'ASC');
        $result2 = $qb2->getQuery()->getResult();

        $countries = array_merge($result1, $result2);

        return new ViewModel(array(
            'countries' => $countries,
        ));
    }

    public function editAction()
    {
        $forrest = new Service\BreadcrumbFactory();
        $breadcrumb = $forrest->get('country');
        if(!$forrest->exists('country')) {
            $forrest->set('country', 'admin/country');
        }
        
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/country');
        }
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $country = $em->getRepository("ersEntity\Entity\Country")->findOneBy(array('id' => $id));

        $form  = new Form\Country();
        $form->bind($country);
        $form->get('submit')->setAttribute('value', 'Edit');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($country->getInputFilter());
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
            'country' => $country,
            'breadcrumb' => $breadcrumb,
        ));
    }

    public function deleteAction()
    {
        $forrest = new Service\BreadcrumbFactory();
        if(!$forrest->exists('tax')) {
            $forrest->set('tax', 'admin/tax');
        }
        $breadcrumb = $forrest->get('tax');
        
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
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
            }

            return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
        }

        return new ViewModel(array(
            'id'    => $id,
            'tax' => $tax = $em->getRepository("ersEntity\Entity\Tax")
                ->findOneBy(array('id' => $id)),
            'breadcrumb' => $breadcrumb,
        ));
    }
}
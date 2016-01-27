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

class CountryController extends AbstractActionController {
    public function indexAction()
    {
        $forrest = new Service\BreadcrumbService();
        $forrest->set('country', 'admin/country');
        
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $qb1 = $em->getRepository('ErsBase\Entity\Country')->createQueryBuilder('n');
        $qb1->where($qb1->expr()->isNotNull('n.position'));
        $qb1->orderBy('n.position', 'ASC');
        $result1 = $qb1->getQuery()->getResult();
        
        $qb2 = $em->getRepository('ErsBase\Entity\Country')->createQueryBuilder('n');
        $qb2->where($qb2->expr()->isNull('n.position'));
        $qb2->orderBy('n.name', 'ASC');
        $result2 = $qb2->getQuery()->getResult();

        $countries = array_merge($result1, $result2);

        return new ViewModel(array(
            'countries' => $countries,
        ));
    }

    public function editAction()
    {
        $forrest = new Service\BreadcrumbService();
        $breadcrumb = $forrest->get('country');
        if(!$forrest->exists('country')) {
            $forrest->set('country', 'admin/country');
        }
        
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/country');
        }
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $country = $em->getRepository('ErsBase\Entity\Country')->findOneBy(array('id' => $id));

        $form  = new Form\Country();
        $form->bind($country);
        $form->get('submit')->setAttribute('value', 'Edit');

        $request = $this->getRequest();
        if ($request->isPost()) {
            #$form->setInputFilter($country->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                #$tax->populate($form->getData());
                #$em->persist($tax);
                $country = $form->getData();
                if($country->getPosition() == 0) {
                    $country->setPosition(null);
                }
                $em->persist($country);
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
                $tax = $em->getRepository('ErsBase\Entity\Tax')
                        ->findOneBy(array('id' => $id));
                $em->remove($tax);
                $em->flush();
            }

            return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
        }

        return new ViewModel(array(
            'id'    => $id,
            'tax' => $tax = $em->getRepository('ErsBase\Entity\Tax')
                ->findOneBy(array('id' => $id)),
            'breadcrumb' => $breadcrumb,
        ));
    }
}
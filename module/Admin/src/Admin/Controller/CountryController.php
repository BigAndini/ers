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
        
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $queryBuilder1 = $entityManager->getRepository('ErsBase\Entity\Country')->createQueryBuilder('n');
        $queryBuilder1->where($queryBuilder1->expr()->isNotNull('n.position'));
        $queryBuilder1->orderBy('n.position', 'ASC');
        $result1 = $queryBuilder1->getQuery()->getResult();
        
        $queryBuilder2 = $entityManager->getRepository('ErsBase\Entity\Country')->createQueryBuilder('n');
        $queryBuilder2->where($queryBuilder2->expr()->isNull('n.position'));
        $queryBuilder2->orderBy('n.name', 'ASC');
        $result2 = $queryBuilder2->getQuery()->getResult();

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
        
        $countryId = (int) $this->params()->fromRoute('id', 0);
        if (!$countryId) {
            return $this->redirect()->toRoute('admin/country');
        }
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $country = $entityManager->getRepository('ErsBase\Entity\Country')->findOneBy(array('id' => $countryId));

        $form  = new Form\Country();
        $form->bind($country);
        $form->get('submit')->setAttribute('value', 'Edit');

        $request = $this->getRequest();
        if ($request->isPost()) {
            #$form->setInputFilter($country->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                #$tax->populate($form->getData());
                #$entityManager->persist($tax);
                $country = $form->getData();
                if($country->getPosition() == 0) {
                    $country->setPosition(null);
                }
                $entityManager->persist($country);
                $entityManager->flush();

                $this->flashMessenger()->addSuccessMessage('Country has been successfully changed.');
                return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
            }
        }

        return new ViewModel(array(
            'id' => $countryId,
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
        
        $countryId = (int) $this->params()->fromRoute('id', 0);
        if (!$countryId) {
            return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
        }
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');

            if ($del == 'Yes') {
                
                $countryId = (int) $request->getPost('id');
                $tax = $entityManager->getRepository('ErsBase\Entity\Tax')
                        ->findOneBy(array('id' => $countryId));
                $entityManager->remove($tax);
                $entityManager->flush();
            }

            $this->flashMessenger()->addSuccessMessage('Country has been successfully deleted.');
            return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
        }

        return new ViewModel(array(
            'id'    => $countryId,
            'tax' => $tax = $entityManager->getRepository('ErsBase\Entity\Tax')
                ->findOneBy(array('id' => $countryId)),
            'breadcrumb' => $breadcrumb,
        ));
    }
}
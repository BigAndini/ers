<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use ErsBase\Service;

class OverviewController extends AbstractActionController {
    public function indexAction()
    {
        return new ViewModel();
    }

    public function configAction() {
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $breadcrumbService = new Service\BreadcrumbService();
        $breadcrumbService->set('tax', 'admin/overview/config');
        $breadcrumbService->set('deadline', 'admin/overview/config');
        $breadcrumbService->set('agegroup', 'admin/overview/config');
        $breadcrumbService->set('payment-type', 'admin/overview/config');
        $breadcrumbService->set('counter', 'admin/overview/config');
        $breadcrumbService->set('status', 'admin/overview/config');
        $breadcrumbService->set('product', 'admin/overview/config');
        
        return new ViewModel(array(
            'taxes' => $entityManager->getRepository('ErsBase\Entity\Tax')->findAll(),
            'deadlines' => $entityManager->getRepository('ErsBase\Entity\Deadline')->findAll(),
            'agegroups' => $entityManager->getRepository('ErsBase\Entity\Agegroup')->findAll(),
            'paymenttypes' => $entityManager->getRepository('ErsBase\Entity\PaymentType')->findAll(),
            'counters' => $entityManager->getRepository('ErsBase\Entity\Counter')->findAll(),
            'statuses' => $entityManager->getRepository('ErsBase\Entity\Status')->findAll(),
            'products' => $entityManager->getRepository('ErsBase\Entity\Product')->findAll(),
        ));
    }
}
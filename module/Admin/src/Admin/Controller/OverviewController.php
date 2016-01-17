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
        $em = $this->getServiceLocator()
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
            'taxes' => $em->getRepository("ErsBase\Entity\Tax")->findAll(),
            'deadlines' => $em->getRepository("ErsBase\Entity\Deadline")->findAll(),
            'agegroups' => $em->getRepository("ErsBase\Entity\Agegroup")->findAll(),
            'paymenttypes' => $em->getRepository("ErsBase\Entity\PaymentType")->findAll(),
            'counters' => $em->getRepository("ErsBase\Entity\Counter")->findAll(),
            'statuses' => $em->getRepository("ErsBase\Entity\Status")->findAll(),
            'products' => $em->getRepository("ErsBase\Entity\Product")->findAll(),
        ));
    }
}
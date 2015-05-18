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
use Admin\InputFilter;

class PackageController extends AbstractActionController {
    public function indexAction()
    {
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        return new ViewModel(array(
            'agegroups' => $em->getRepository("ersEntity\Entity\Agegroup")
                ->findBy(array(), array('agegroup' => 'ASC')),
        ));
    }
    
    public function detailAction() {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/order', array());
        }
        
        $forrest = new Service\BreadcrumbFactory();
        if(!$forrest->exists('package')) {
            $forrest->set('package', 'admin/package', array('action' => 'detail', 'id' => $id));
        }
        $forrest->set('item', 'admin/package', array('action' => 'detail', 'id' => $id));
        
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $package = $em->getRepository("ersEntity\Entity\Package")
                ->findOneBy(array('id' => $id));
        
        return new ViewModel(array(
            'package' => $package,
        ));
    }
    
    public function unpaidAction() {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/order', array());
        }
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $package = $em->getRepository("ersEntity\Entity\Package")
                ->findOneBy(array('id' => $id));
        
        $forrest = new Service\BreadcrumbFactory();
        if(!$forrest->exists('package')) {
            $forrest->set('package', 'admin/order');
        }
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $ret = $request->getPost('del', 'No');

            if ($ret == 'Yes') {
                $id = (int) $request->getPost('id');
                
                $package = $em->getRepository("ersEntity\Entity\Package")
                    ->findOneBy(array('id' => $id));
                
                foreach($package->getItems() as $item) {
                    $item->setStatus('ordered');
                    $em->persist($item);
                }
                
                $em->flush();
                
                $breadcrumb = $forrest->get('package');
                return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
            }
        }
        
        return new ViewModel(array(
            'package' => $package,
            'breadcrumb' => $forrest->get('package'),
        ));
    }
    
    public function paidAction() {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/order', array());
        }
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $package = $em->getRepository("ersEntity\Entity\Package")
                ->findOneBy(array('id' => $id));
        
        $forrest = new Service\BreadcrumbFactory();
        if(!$forrest->exists('package')) {
            $forrest->set('package', 'admin/order');
        }
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $ret = $request->getPost('del', 'No');

            if ($ret == 'Yes') {
                $id = (int) $request->getPost('id');
                
                $package = $em->getRepository("ersEntity\Entity\Package")
                    ->findOneBy(array('id' => $id));
                
                foreach($package->getItems() as $item) {
                    $item->setStatus('paid');
                    $em->persist($item);
                }
                
                $em->flush();
                
                $breadcrumb = $forrest->get('package');
                return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
            }
        }
        
        return new ViewModel(array(
            'package' => $package,
            'breadcrumb' => $forrest->get('package'),
        ));
    }
    
    public function cancelAction() {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/order', array());
        }
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $package = $em->getRepository("ersEntity\Entity\Package")
                ->findOneBy(array('id' => $id));
        
        $forrest = new Service\BreadcrumbFactory();
        if(!$forrest->exists('package')) {
            $forrest->set('package', 'admin/order');
        }
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $ret = $request->getPost('del', 'No');

            if ($ret == 'Yes') {
                $id = (int) $request->getPost('id');
                
                $package = $em->getRepository("ersEntity\Entity\Package")
                    ->findOneBy(array('id' => $id));
                
                foreach($package->getItems() as $item) {
                    $item->setStatus('cancelled');
                    $em->persist($item);
                }
                
                $em->flush();
                
                $breadcrumb = $forrest->get('package');
                return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
            }
        }
        
        return new ViewModel(array(
            'package' => $package,
            'breadcrumb' => $forrest->get('package'),
        ));
    }
}
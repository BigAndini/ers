<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace OnsiteReg\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use OnsiteReg\Form;

class IndexController extends AbstractActionController {
    public function indexAction() {
        $form = new Form\Search();
        return new ViewModel(array(
            'form' => $form,
        ));
    }
    public function detailAction() {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('onsite', array());
        }
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $package = $em->getRepository("ersEntity\Entity\Package")
                ->findOneBy(array('id' => $id));
        
        $forrest = new Service\BreadcrumbFactory();
        $forrest->set('order', 'onsite/package', array('action' => 'detail', 'id' => $id));
        $forrest->set('user', 'onsite/package', array('action' => 'detail', 'id' => $id));
        $forrest->set('package', 'onsite/package', array('action' => 'detail', 'id' => $id));
        $forrest->set('item', 'onsite/package', array('action' => 'detail', 'id' => $id));
        
        return new ViewModel(array(
            'package' => $package,
        ));
    }
}
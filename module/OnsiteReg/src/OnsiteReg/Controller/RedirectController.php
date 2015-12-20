<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace OnsiteReg\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class RedirectController extends AbstractActionController {
    
    public function indexAction() {
        // if not logged in or no according rights redirect to default redirect target
        if(!$this->isAllowed('redirect', 'do')) {
            error_log('unauthorized access to redirect page');
            return $this->redirect()->toRoute('user/login');
        }
        
        
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        // get the corresponding code
        $codeValue = $this->params()->fromRoute('code', '');
        /* @var $code \ersBase\Entity\Code */
        $code = $em->getRepository("ersBase\Entity\Code")
                ->findOneBy(array('value' => $codeValue));
        
        if(!$code) {
            error_log('unable to find code in system: ' . $id);
            //return $this->notFoundAction();
            $this->flashMessenger()->addErrorMessage('The code "' . $codeValue . '" could not be found in the system!');
            return $this->redirect()->toRoute('onsite');
        }
        
        $package = $code->getPackage();
        $item = $code->getItem();
        
        if(!$package && $item) {
            // if the code belongs to an item, use its containing package
            $package = $item->getPackage();
        }
        
        if($package) {
            // go to the onsite view of the package
            return $this->redirect()->toRoute('onsite/package', array('action' => 'detail', 'id' => $package->getId()));
        }
        
        // only remaining option is that the code belongs to an order
        $order = ($code->getOrders()->isEmpty() ? NULL : $code->getOrders()->first());
        if($order) {
            // perform an onsite search for the order code
            // note that for EJC 2015, order codes were not stored in QR codes, but this seems like a sensible approach
            return $this->redirect()->toRoute('onsite/search', [], ['query' => ['q' => $code->getValue()]]);
        }
        
        error_log('detected orphaned code ' . $code->getValue() . ': it has no entities associated with it');
        return $this->notFoundAction();
    }
}

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
use ersEntity\Entity;

class RedirectController extends AbstractActionController {
    const DEFAULT_REDIRECT_TARGET = 'http://ejc2015.org/volunteer/';
    
    public function indexAction() {
        // if not logged in or no according rights redirect to default redirect target
        if(!$this->isAllowed('redirect', 'do')) {
            error_log('unauthorized access to redirect page');
            return $this->redirect()->toUrl(self::DEFAULT_REDIRECT_TARGET);
        }
        
        
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        // get the corresponding code
        $codeValue = $this->params()->fromRoute('code', '');
        /* @var $code \ersEntity\Entity\Code */
        $code = $em->getRepository("ersEntity\Entity\Code")
                ->findOneBy(array('value' => $codeValue));
        
        if(!$code) {
            error_log('unable to find code in system: ' . $id);
            return $this->notFoundAction();
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
            // currently, we do not redirect anywhere for order codes
            return $this->notFoundAction();
        }
        
        error_log('detected orphaned code ' . $code->getValue() . ': it has no entities associated with it');
        return $this->notFoundAction();
    }
}

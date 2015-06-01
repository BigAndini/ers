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
    public function doAction() {
        $id = $this->params()->fromRoute('id', 0);
        /*if (!$id) {
            #return $this->redirect()->toRoute('admin/order', array());
            return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
        }*/
        
        $default_redirect_target = 'http://ejc2015.org/volunteer/';
        
        /*
         * If not logged in redirect to default redirect target
         * http://ejc2015.org/volunteer/
         */
        if(!$this->zfcUserAuthentication()->hasIdentity()) {
            return $this->redirect()->toUrl($default_redirect_target);
        }
        $user = $this->zfcUserAuthentication()->getIdentity();
        
        /*
         * If logged in check for according rights. If no right redirect to 
         * default redirect target.
         * http://ejc2015.org/volunteer/
         */
        /*$onsite_role = new Entity\Role();
        $onsite_role->setRoleId('onsite');
        $admin_role = new Entity\Role();
        $admin_role->setRoleId('admin');
        $supradm_role = new Entity\Role();
        $supradm_role->setRoleId('supradm');*/
        
        
        if(!$this->isAllowed('redirect', 'do')) {
            error_log('user is not allowed');
            return $this->redirect()->toUrl($default_redirect_target);
        }
        
        error_log('user is allowed');
        
        /*
         * check the code that was given
         */
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $code = $em->getRepository("ersEntity\Entity\Code")
                ->findOneBy(array('value' => $id));
        
        if(!$code) {
            error_log('unable to find code in system');
        }
        
        /*
         * search for a package with this code
         */
        $package = $em->getRepository("ersEntity\Entity\Package")
                ->findOneBy(array('Code_id' => $code->getId()));
        if($package) {
            error_log('found package for code '.$code->getValue());
            return $this->redirect()->toUrl($default_redirect_target);
        }
        
        
        /*
         * search for an order with this code
         */
        $order = $em->getRepository("ersEntity\Entity\Order")
                ->findOneBy(array('Code_id' => $code->getId()));
        if($order) {
            error_log('found order for code '.$code->getValue());
            return $this->redirect()->toUrl($default_redirect_target);
        }
        
        /*
         * search for an item with this code
         */
        $item = $em->getRepository("ersEntity\Entity\Item")
                ->findOneBy(array('Code_id' => $code->getId()));
        if($package) {
            error_log('found item for code '.$code->getValue());
            return $this->redirect()->toUrl($default_redirect_target);
        }
        
        error_log('unable to find any codes');
        return $this->redirect()->toUrl($default_redirect_target);
    }
}
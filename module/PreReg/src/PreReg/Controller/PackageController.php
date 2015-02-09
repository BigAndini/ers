<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace PreReg\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use PreReg\Form;
use ersEntity\Entity;

class PackageController extends AbstractActionController {
    /*
     * - Show list of participants of this session
     * - inclufde participant for which this user already bought products, if 
     *   the user is logged in.
     */
    public function indexAction()
    {  
        //get the email of the user
        $email = $this->zfcUserAuthentication()->getIdentity()->getEmail();

        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $user = $em->getRepository("ersEntity\Entity\User")->findOneBy(array('email' => $email));
        
        $packages = $em->getRepository("ersEntity\Entity\Package")->findBy(array('Participant_id' => $user->getId()));
        $orders = $em->getRepository("ersEntity\Entity\Order")->findBy(array('Purchaser_id' => $user->getId()));
        
        return new ViewModel(array(
            'user' => $user,
            'packages' => $packages,
            'orders' => $orders,
        ));
    }
    
    public function changeDataAction() {
        
    }
    public function changePasswordAction() {
        
    }
    public function packageAction() {
        
    }
    public function participantAction() {
        
    }
}
<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace PreReg\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Crypt\Password\Bcrypt;
use PreReg\Form;
use PreReg\InputFilter;
use ersEntity\Entity;

class ProfileController extends AbstractActionController {
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
        
        return new ViewModel(array(
            'user' => $user,
        ));
    }
    
    public function editAction() {
        if (!$this->zfcUserAuthentication()->hasIdentity()) {
            return $this->redirect()->toRoute('zfcuser/login');
        }
        
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $email = $this->zfcUserAuthentication()->getIdentity()->getEmail();
        $user = $em->getRepository("ersEntity\Entity\User")->findOneBy(array('email' => $email));
        
        $form = new Form\User(); 
        $request = $this->getRequest(); 
        
        $form->bind($user);
        
        if($request->isPost()) 
        {
            $inputFilter = new InputFilter\User();
            $form->setInputFilter($inputFilter->getInputFilter()); 
            $form->setData($request->getPost()); 
                
            if($form->isValid())
            { 
                $user = $form->getData();
                $em->persist($user);
                $em->flush();
                
                return $this->redirect()->toRoute('profile');
            } else {
                $logger = $this
                    ->getServiceLocator()
                    ->get('Logger');
                $logger->warn($form->getMessages());
            } 
        }
        
        return new ViewModel(array(
            'id' => $id,
            'form' => $form,
        ));
    }
    public function passwordAction() {
        if (!$this->zfcUserAuthentication()->hasIdentity()) {
            return $this->redirect()->toRoute('zfcuser/login');
        }
        
        $logger = $this
            ->getServiceLocator()
            ->get('Logger');
        
        $formClass = $this->getServiceLocator()->get('zfcuser_user_service')->getChangePasswordForm();
        $form = new $formClass('ChangePassword', $this->getServiceLocator()->get('zfcuser_module_options'));
        
        $request = $this->getRequest();
        if($request->isPost()) 
        {
            $form->setData($request->getPost()); 
            if($form->isValid())
            {
                $change = $this->getServiceLocator()->get('zfcuser_user_service')
                        ->changePassword($form->getData());
                if(!$change) {
                    $logger->warning('Unable to change password');
                }
                
                return $this->redirect()->toRoute('profile');
            } else {
                $logger = $this
                    ->getServiceLocator()
                    ->get('Logger');
                $logger->warn($form->getMessages());
            } 
        }
        
        return new ViewModel(array(
            'id' => $id,
            'form' => $form,
        ));
    }
    public function forgotPasswordAction() {
        
    }
    public function changeAction() {
        return new ViewModel(array(
            
        ));
    }
    public function packageAction() {
        
    }
    public function participantAction() {
        
    }
}
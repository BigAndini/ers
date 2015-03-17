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
use PreReg\Service;
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
    
    public function requestPasswordAction() {
        $form = new Form\RequestPassword();
        
        $logger = $this
            ->getServiceLocator()
            ->get('Logger');
        
        $sent = false;
        $request = $this->getRequest();
        if($request->isPost()) 
        {
            $form->setData($request->getPost()); 
            if($form->isValid())
            {
                $data = $form->getData();
                #$logger->info($data);
                
                $em = $this
                    ->getServiceLocator()
                    ->get('Doctrine\ORM\EntityManager');
                $user = $em->getRepository("ersEntity\Entity\User")
                        ->findOneBy(array('email' => $data['email']));
                if($user) {
                    $user->genHashKey();

                    $em->persist($user);
                    $em->flush();
                    
                    $emailService = new Service\EmailFactory();
                    #$emailService->setFrom('prereg@eja.net');
        
                    $emailService->addTo($user);
                    $emailService->setSubject('EJC Registration System: Password Request Link');

                    $viewModel = new ViewModel(array(
                        'user' => $user,
                    ));
                    $viewModel->setTemplate('email/request-password.phtml');
                    $viewRender = $this->getServiceLocator()->get('ViewRenderer');
                    $html = $viewRender->render($viewModel);

                    $emailService->setHtmlMessage($html);
                    $emailService->send();
                }
                
                $sent = true;
                #return $this->redirect()->toRoute('profile', array('action' => 'request-password'));
            } else {
                $logger->warn($form->getMessages());
            } 
        }
        
        return new ViewModel(array(
            'sent' => $sent,
            'form' => $form,
        ));
    }
    public function passwordResetAction() {
        $logger = $this
            ->getServiceLocator()
            ->get('Logger');
        
        $hashKey = $this->params()->fromRoute('hashkey', '');
        if($hashKey == '') {
            return $this->redirect()->toRoute('zfcuser/login');
        }
        $form = new Form\ResetPassword();
        
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $user = $em->getRepository("ersEntity\Entity\User")
                ->findOneBy(array('hashKey' => $hashKey));
        if(!$user) {
            return $this->redirect()->toRoute('zfcuser/login');
        }
        
        $now = new \DateTime();
        if(($user->getUpdated()->getTimestamp()+7200) >= $now->getTimestamp()) {
            return $this->redirect()->toRoute('zfcuser/login');
        }
        
        $inputFilter = new InputFilter\ResetPassword();
        $form->setInputFilter($inputFilter->getInputFilter());
        $request = $this->getRequest();
        if($request->isPost()) 
        {
            $form->setData($request->getPost()); 
            if($form->isValid())
            {
                $data = $form->getData();

                $bcrypt = new Bcrypt();
                #$bcrypt->setCost(14); // Needs to match password cost in ZfcUser options. Or better yet just pull that config setting.
                $config = $this->getServiceLocator()->get('Config');
                $bcrypt->setCost($config['zfcuser']['password_cost']);
                $password = $bcrypt->create($data['newPassword']);
                $user->setPassword($password);
                $user->setHashKey(null);
                $em->persist($user);
                $em->flush();
                
                return $this->redirect()->toRoute('zfcuser/login');
            } else {
                $logger->warn($form->getMessages());
            } 
        }
        
        return new ViewModel(array(
            'form' => $form,
            'user' => $user,
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
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
use ErsBase\Service;
use ErsBase\Entity;

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

        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $user = $entityManager->getRepository('ErsBase\Entity\User')->findOneBy(array('email' => $email));
        
        return new ViewModel(array(
            'user' => $user,
        ));
    }
    
    public function editAction() {
        if (!$this->zfcUserAuthentication()->hasIdentity()) {
            return $this->redirect()->toRoute('zfcuser/login');
        }
        
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $email = $this->zfcUserAuthentication()->getIdentity()->getEmail();
        $user = $entityManager->getRepository('ErsBase\Entity\User')->findOneBy(array('email' => $email));
        
        $form = new Form\User(); 
        $request = $this->getRequest(); 
        
        $form->bind($user);
        
        if($request->isPost()) 
        {
            #$inputFilter = new InputFilter\User();
            #$form->setInputFilter($inputFilter->getInputFilter()); 
            $form->setData($request->getPost()); 
                
            if($form->isValid())
            { 
                $user = $form->getData();
                $entityManager->persist($user);
                $entityManager->flush();
                
                return $this->redirect()->toRoute('profile');
            } else {
                $logger = $this->getServiceLocator()->get('Logger');
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
        
        $logger = $this->getServiceLocator()->get('Logger');
        
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
        
        $logger = $this->getServiceLocator()->get('Logger');
        
        $sent = false;
        $request = $this->getRequest();
        if($request->isPost()) 
        {
            $form->setData($request->getPost()); 
            if($form->isValid())
            {
                $data = $form->getData();
                
                $entityManager = $this->getServiceLocator()
                    ->get('Doctrine\ORM\EntityManager');
                $user = $entityManager->getRepository('ErsBase\Entity\User')
                        ->findOneBy(array('email' => $data['email']));
                if($user) {
                    $user->genHashKey();

                    $entityManager->persist($user);
                    $entityManager->flush();
                    
                    $emailService = $this->getServiceLocator()
                            ->get('ErsBase\Service\EmailService');
        
                    #$emailService->addTo($user);
                    $subject = _('Event Registration System: Password Request Link');

                    $viewModel = new ViewModel(array(
                        'user' => $user,
                    ));
                    $viewModel->setTemplate('email/request-password.phtml');
                    $viewRender = $this->getServiceLocator()->get('ViewRenderer');
                    $content = $viewRender->render($viewModel);

                    $settingService = $this->getServiceLocator()
                        ->get('ErsBase\Service\SettingService');

		    $recipients[] = [
			'email' => $user->getEmail(),
			'type' => 'to',
		    ];
                    
                    #$emailService->addMailToQueue($settingService->get('ers.sender_email'), $user->getEmail(), $subject, $content);
                    $emailService->addMailToQueue($settingService->get('ers.sender_email'), $recipients, $subject, $content);
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
        $logger = $this->getServiceLocator()->get('Logger');
        
        $hashkey = $this->params()->fromRoute('hashkey', '');
        if($hashkey == '') {
            $logger->warn('unable to find hashkey in route');
            return $this->redirect()->toRoute('zfcuser/login');
        }
        $form = new Form\ResetPassword();
        
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $user = $entityManager->getRepository('ErsBase\Entity\User')
                ->findOneBy(array('hashkey' => $hashkey));
        if(!$user) {
            $logger->warn('unable to find user with hash key: '.$hashkey);
            return $this->redirect()->toRoute('zfcuser/login');
        }
        
        $now = new \DateTime();
        # set time to reset password (ttrp) to 24 hours
        $ttrp=86400;
        if(($user->getUpdated()->getTimestamp()+$ttrp) <= $now->getTimestamp()) {
            $logger->warn('Too late, link is not enabled for user '.$user->getEmail().' anymore: '.($user->getUpdated()->getTimestamp()+ttrp).' >= '.$now->getTimestamp());
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
                
                $role = $entityManager->getRepository('ErsBase\Entity\Role')
                    ->findOneBy(array('roleId' => 'user'));
                if(!$user->hasRole($role)) {
                    $user->addRole($role);
                }
                
                $entityManager->persist($user);
                $entityManager->flush();
                
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
        $email = $this->zfcUserAuthentication()->getIdentity()->getEmail();
        
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $participant = $entityManager->getRepository('ErsBase\Entity\User')
            ->findOneBy(array('email' => $email));
        
        #$form = new Form\Participant(); 
        $form = $this->getServiceLocator()
                ->get('PreReg\Form\Participant');
        $request = $this->getRequest(); 
        
        $forrest = new Service\BreadcrumbService();
        if(!$forrest->exists('profile')) {
            $forrest->set('profile', 'profile');
        }
        
        $form->get('Country_id')->setValueOptions($this->getCountryOptions());
        
        $form->bind($participant);
        
        if($request->isPost()) 
        {
            #$inputFilter = new InputFilter\Participant();
            #$form->setInputFilter($inputFilter->getInputFilter()); 
            $form->setData($request->getPost()); 
                
            if($form->isValid())
            { 
                $participant = $form->getData();
                
                if($participant->getCountryId() == 0) {
                    $participant->setCountryId(null);
                    $participant->setCountry(null);
                }
                
                $entityManager->persist($participant);
                $entityManager->flush();
                
                $breadcrumb = $forrest->get('profile');
                return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
            } else {
                $logger = $this->getServiceLocator()->get('Logger');
                $logger->warn($form->getMessages());
            } 
        }
        
        $breadcrumb = $forrest->get('profile');
        return new ViewModel(array(
            'form' => $form,
            'breadcrumb' => $breadcrumb,
        ));
    }
    
    private function getCountryOptions($countryId = null) {
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $queryBuilder1 = $entityManager->getRepository('ErsBase\Entity\Country')->createQueryBuilder('n');
        $queryBuilder1->where($queryBuilder1->expr()->isNotNull('n.position'));
        $queryBuilder1->orderBy('n.position', 'ASC');
        $result1 = $queryBuilder1->getQuery()->getResult();
        
        $queryBuilder2 = $entityManager->getRepository('ErsBase\Entity\Country')->createQueryBuilder('n');
        $queryBuilder2->where($queryBuilder2->expr()->isNull('n.position'));
        $queryBuilder2->orderBy('n.name', 'ASC');
        $result2 = $queryBuilder2->getQuery()->getResult();

        $countries = array_merge($result1, $result2);
        
        $options = array();
        $selected = false;
        if($countryId == null) {
            $selected = true;
        }
        $options[] = array(
            'value' => 0,
            'label' => 'no Country',
            'selected' => $selected,
        );
        foreach($countries as $country) {
            $selected = false;
            if($countryId == $country->getId()) {
                $selected = true;
            }
            $options[] = array(
                'value' => $country->getId(),
                'label' => $country->getName(),
                'selected' => $selected,
            );
        }
        return $options;
    }
    
    public function packageAction() {
        
    }
    public function participantAction() {
        return new ViewModel();
    }
}

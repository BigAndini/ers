<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace User\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
#use StickyNotes\Model\User;
use User\Form;
use User\Model\Entity;

class UserController extends AbstractActionController {

    /*
     * Show infos for this user profile
     */
    public function indexAction()
    {
        return new ViewModel();
    }
    
    /*
     * Give two form fields one for the email address and one for the password.
     * In addition there should be a link to the forgot-password page.
     */
    public function loginAction()
    {
        $form = $this->getServiceLocator()->get('User\Form\LoginForm');
        $request = $this->getRequest();
        if ($request->isPost()) {
            $user = new Entity\User();
            $form->setInputFilter($user->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                error_log('authenticate user');
                #$product->exchangeArray($form->getData());
                #$this->getTable('Product')->save($product);

                // Redirect to list of products
                return $this->redirect()->toRoute('product');
            } else {
                $messages = $form->getMessages();
                error_log('got '.count($messages).' messages.');
                foreach($messages as $m) {
                    error_log($m);
                }
            }
        }
        
        return array(
            'form' => $form,                
        );
        /*return new ViewModel();*/
    }
    
    /*
     * shows a form where you can put in your email address. Then you will get a
     * link with a long hash where you will be redirected to the 
     * ResetPasswordAction.
     */
    public function ForgotPasswordAction()
    {
        $form = $this->getServiceLocator()->get('User\Form\ForgotPasswordForm');
        $request = $this->getRequest();
        if ($request->isPost()) {
            $user = new Entity\User();
            $form->setInputFilter($user->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                error_log('generate hash and add to user with given email');
                #$product->exchangeArray($form->getData());
                #$this->getTable('Product')->save($product);

                // Redirect to list of products
                return $this->redirect()->toRoute('product');
            } else {
                $messages = $form->getMessages();
                error_log('got '.count($messages).' messages.');
                foreach($messages as $m) {
                    error_log($m);
                }
            }
        }
        
        return array(
            'form' => $form,                
        );
        return new ViewModel();
    }
    
    /*
     * If you are here you got a mail with a password reset hash. This hash is 
     * the key to be able to reset your password. You will get two password 
     * fields to be able to set a new password and to confirm the new password.
     */
    public function ResetPasswordAction()
    {
        $hash = (int) $this->params()->fromRoute('hash', 0);
        if (!$hash) {
            return $this->redirect()->toRoute('user', array(
                'action' => 'login'
            ));
        }
        $user = $this->getTable('User')->getByField(array('hash' => $hash));
        
        $form = $this->getServiceLocator()->get('User\Form\ResetPasswordForm');
        $form->bind($user);
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($user->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                error_log('reset password');
                #$product->exchangeArray($form->getData());
                #$this->getTable('Product')->save($product);

                // Redirect to list of products
                return $this->redirect()->toRoute('product');
            } else {
                $messages = $form->getMessages();
                error_log('got '.count($messages).' messages.');
                foreach($messages as $m) {
                    error_log($m);
                }
            }
        }
        
        return array(
            'form' => $form,                
        );
        #return new ViewModel();
    }
    
    /*
     * Is this function for everybody at any time available?
     * Maybe we get spam.
     * Maybe this is a possible whole for a DoS attack.
     */
    public function registerAction()
    {
        $user = new Entity\User();
        $form = $this->getServiceLocator()->get('User\Form\RegisterForm');
        $form->bind($user);
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($user->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                error_log('save the user entity');
                #$product->exchangeArray($form->getData());
                #$this->getTable('Product')->save($product);

                // Redirect to list of products
                return $this->redirect()->toRoute('product');
            } else {
                $messages = $form->getMessages();
                error_log('got '.count($messages).' messages.');
                foreach($messages as $m) {
                    error_log($m);
                }
            }
        }
        
        return array(
            'form' => $form,                
        );
        #return new ViewModel();
    }
}
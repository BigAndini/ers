<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use ErsBase\Entity;
use Admin\Form;

class SettingController extends AbstractActionController {
    
    public function indexAction()
    {
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        return new ViewModel(array(
            'settings' => $entityManager->getRepository('ErsBase\Entity\Setting')
                ->findBy(array(), array('key' => 'ASC')),
         ));
    }

    public function addTextAction()
    {
        $setting = new Entity\Setting();
        
        $form = new Form\Setting();
        $form->bind($setting);
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $setting = $form->getData();
                
                $entityManager = $this->getServiceLocator()
                    ->get('Doctrine\ORM\EntityManager');
                
                $setting->setType('text');
                $entityManager->persist($setting);
                $entityManager->flush();

                $this->flashMessenger()->addSuccessMessage('This setting '.$setting->getKey().' has been successfully added');
                return $this->redirect()->toRoute('admin/setting');
            }
            $this->flashMessenger()->addErrorMessage($form->getMessages());
            $logger = $this->getServiceLocator()->get('Logger');
            $logger->warn($form->getMessages());
        }
        
        return new ViewModel(array(
            'form' => $form,                
        ));
    }

    public function addTextareaAction()
    {
        $setting = new Entity\Setting();
        
        $form = new Form\Setting();
        $form->bind($setting);
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $setting = $form->getData();
                
                $entityManager = $this->getServiceLocator()
                    ->get('Doctrine\ORM\EntityManager');
                
                $setting->setType('textarea');
                
                $entityManager->persist($setting);
                $entityManager->flush();

                $this->flashMessenger()->addSuccessMessage('This setting '.$setting->getKey().' has been successfully added');
                return $this->redirect()->toRoute('admin/setting');
            }
            $this->flashMessenger()->addErrorMessage($form->getMessages());
            $logger = $this->getServiceLocator()->get('Logger');
            $logger->warn($form->getMessages());
        }
        
        return new ViewModel(array(
            'form' => $form,                
        ));
    }
    
    public function editAction()
    {
        $settingId = (int) $this->params()->fromRoute('id', 0);
        if (!$settingId) {
            $this->flashMessenger()->addErrorMessage('Unable to edit setting, id is missing.');
            return $this->redirect()->toRoute('admin/setting', array(
                'action' => 'add'
            ));
        }
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $setting = $entityManager->getRepository('ErsBase\Entity\Setting')
                ->findOneBy(array('id' => $settingId));

        if($setting && $setting->getType() != '') {
            return $this->redirect()->toRoute('admin/setting', array('action' => 'edit-'.$setting->getType(), 'id' => $setting->getId()));
        } else {
            throw new Exception('unable to find setting or unable to find setting type');
        }
    }

    public function editTextAction()
    {
        $settingId = (int) $this->params()->fromRoute('id', 0);
        if (!$settingId) {
            $this->flashMessenger()->addErrorMessage('Unable to edit setting, id is missing.');
            return $this->redirect()->toRoute('admin/setting', array(
                'action' => 'add'
            ));
        }
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $setting = $entityManager->getRepository('ErsBase\Entity\Setting')
                ->findOneBy(array('id' => $settingId));

        $form = new Form\Setting();
        $form->bind($setting);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $entityManager->persist($form->getData());
                $entityManager->flush();

                $this->flashMessenger()->addSuccessMessage('The setting has been successfully changed.');
                return $this->redirect()->toRoute('admin/setting');
            } else {
                $this->flashMessenger()->addErrorMessage($form->getMessages());
            }
        }

        return new ViewModel(array(
            'id' => $settingId,
            'form' => $form,
        ));
    }
    
    public function editTextareaAction()
    {
        $settingId = (int) $this->params()->fromRoute('id', 0);
        if (!$settingId) {
            $this->flashMessenger()->addErrorMessage('Unable to edit setting, id is missing.');
            return $this->redirect()->toRoute('admin/setting', array(
                'action' => 'add'
            ));
        }
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $setting = $entityManager->getRepository('ErsBase\Entity\Setting')
                ->findOneBy(array('id' => $settingId));

        $form = new Form\Setting();
        $form->remove('value');
        $form->add(array( 
            'name' => 'value', 
            #'type' => 'Zend\Form\Element\Textarea', 
            'type' => 'CKEditorModule\Form\Element\CKEditor',
            'attributes' => array( 
                'placeholder' => 'value...',
                /*'class' => 'form-control form-element',*/
            ), 
            'options' => array( 
                'label' => 'value', 
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
                'ckeditor' => array(
                    // add any config you would normaly add via CKEDITOR.editorConfig
                    'language' => 'en',
                    #'uiColor' => '#428bca',
                ),
            ), 
        ));
        $form->bind($setting);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $entityManager->persist($form->getData());
                $entityManager->flush();

                $this->flashMessenger()->addSuccessMessage('The setting has been successfully changed.');
                return $this->redirect()->toRoute('admin/setting');
            } else {
                $this->flashMessenger()->addErrorMessage($form->getMessages());
            }
        }

        return new ViewModel(array(
            'id' => $settingId,
            'form' => $form,
        ));
    }
    
    public function deleteAction()
    {
        $settingId = (int) $this->params()->fromRoute('id', 0);
        if (!$settingId) {
            return $this->redirect()->toRoute('admin/setting');
        }
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $setting = $entityManager->getRepository('ErsBase\Entity\Setting')
                ->findOneBy(array('id' => $settingId));

        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');

            if ($del == 'Yes') {
                $settingId = (int) $request->getPost('id');
                $setting = $entityManager->getRepository('ErsBase\Entity\Setting')
                    ->findOneBy(array('id' => $settingId));
                
                $entityManager->remove($setting);
                $entityManager->flush();
                
                $this->flashMessenger()->addSuccessMessage('The setting has been successfully deleted.');
            }

            return $this->redirect()->toRoute('admin/setting');
        }

        return new ViewModel(array(
            'id'    => $settingId,
            'setting' => $setting,
        ));
    }
}
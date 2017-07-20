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

    public function addAction()
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
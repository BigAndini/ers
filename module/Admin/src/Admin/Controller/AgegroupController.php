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

class AgegroupController extends AbstractActionController {
    
    public function indexAction()
    {
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        return new ViewModel(array(
            'agegroups' => $entityManager->getRepository('ErsBase\Entity\Agegroup')
                ->findBy(array(), array('agegroup' => 'ASC')),
         ));
    }

    public function addAction()
    {
        $settingService = $this->getServiceLocator()
                            ->get('ErsBase\Service\SettingService');
        $agegroup = new Entity\Agegroup();
        $config = $this->getServiceLocator()
                ->get('Config');
		$param = [
			'fromFormat' => 'Y-m-d',
			'toFormat' => 'd.m.Y'
		];
        #$agegroup->setAgegroup($settingService->get('ers.start', 'date', $param));
		#$agegroup->getAgegroup()->modify('-8 years');
        
        $form = new Form\Agegroup();
        $form->bind($agegroup);
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            #$agegroup = new Entity\Agegroup();
            
            #$form->setInputFilter($agegroup->getInputFilter());
            $form->setData($request->getPost());
            if ($form->isValid()) {
                #$agegroup->populate($form->getData());
                $agegroup = $form->getData();
                
                $entityManager = $this->getServiceLocator()
                    ->get('Doctrine\ORM\EntityManager');
                
                $entityManager->persist($agegroup);
                $entityManager->flush();

                $this->flashMessenger()->addSuccessMessage('The agegroup '.$agegroup->getName().' has been successfully added');
                return $this->redirect()->toRoute('admin/agegroup');
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
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            $this->flashMessenger()->addErrorMessage('Unable to edit agegroup, id is missing.');
            return $this->redirect()->toRoute('admin/agegroup', array(
                'action' => 'add'
            ));
        }
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $agegroup = $entityManager->getRepository('ErsBase\Entity\Agegroup')
                ->findOneBy(array('id' => $id));

        $form = new Form\Agegroup();
        $form->bind($agegroup);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $entityManager->persist($form->getData());
                $entityManager->flush();

                $this->flashMessenger()->addSuccessMessage('The agegroup has been successfully changed.');
                return $this->redirect()->toRoute('admin/agegroup');
            } else {
                $this->flashMessenger()->addErrorMessage($form->getMessages());
            }
        }

        return new ViewModel(array(
            'id' => $id,
            'form' => $form,
        ));
    }

    public function deleteAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/agegroup');
        }
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $agegroup = $entityManager->getRepository('ErsBase\Entity\Agegroup')
                ->findOneBy(array('id' => $id));
        $productprices = $agegroup->getProductPrices();

        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');

            if ($del == 'Yes') {
                $id = (int) $request->getPost('id');
                $agegroup = $entityManager->getRepository('ErsBase\Entity\Agegroup')
                    ->findOneBy(array('id' => $id));
                foreach($agegroup->getProductPrices() as $price) {
                    $entityManager->remove($price);
                }
                $entityManager->remove($agegroup);
                $entityManager->flush();
                
                $this->flashMessenger()->addSuccessMessage('The agegroup has been successfully deleted.');
            }

            return $this->redirect()->toRoute('admin/agegroup');
        }

        return new ViewModel(array(
            'id'    => $id,
            'agegroup' => $agegroup,
            'productprices' => $productprices,
        ));
    }
}

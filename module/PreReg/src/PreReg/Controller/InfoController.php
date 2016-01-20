<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace PreReg\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use ErsBase\Service;
use PreReg\Form;

class InfoController extends AbstractActionController {
    public function indexAction() {
        $breadcrumbService = new Service\BreadcrumbService(); 
        $breadcrumbService->reset();
        $breadcrumbService->set('participant', 'product');
        
        $form = new Form\Participant(); 
        #$form->setEntityManager($em);
        $form->setServiceLocation($this->getServiceLocator());
        $optionService = $this->getServiceLocator()
                ->get('ErsBase\Service\OptionService');
        $form->get('Country_id')->setValueOptions($optionService->getCountryOptions());
        
        $form->get('submit')->setAttribute('class', 'btn btn-lg btn-primary');
        $form->get('submit')->setValue('Register now!');
        
        return new ViewModel(array(
            'ers_config' => $this->getServiceLocator()->get('Config')['ERS'],
            'form' => $form,
        ));
    }
    public function termsAction() {
        $forrest = new Service\BreadcrumbService();
        if(!$forrest->exists('terms')) {
            $forrest->set('terms', 'home');
        }
        return new ViewModel(array(
            'breadcrumb' => $forrest->get('terms'),
        ));
    }
    public function impressumAction() {
        return new ViewModel();
    }

    public function cookieAction() {
        return new ViewModel();
    }
    public function helpAction() {
        return new ViewModel();
    }
    /*
     * display long description of payment type
     */
    public function paymentAction() {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('product');
        }
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $forrest = new Service\BreadcrumbService();
        $breadcrumb = $forrest->get('paymenttype');
        
        return new ViewModel(array(
            'paymenttype' => $em->getRepository("ErsBase\Entity\PaymentType")->findOneBy(array('id' => $id)),
            'breadcrumb' => $breadcrumb,
        ));
    }
    
    private function getLanguageOptions($selected='') {
        $options = array();
        $languages = array(
            'en' => 'English',
            'de' => 'Deutsch',
            'it' => 'Italiano',
            'fr' => 'Français',
            'es' => 'Español',
        );
        $sel = false;
        if($selected == '') {
            $sel = true;
        }
        $options[] = array(
            'value' => 0,
            'label' => 'select language',
            'selected' => $sel,
        );
        foreach($languages as $key => $value) {
            $sel = false;
            if($key == $selected) {
                $sel = true;
            }
            $options[] = array(
                'value' => $key,
                'label' => $value,
                'selected' => $sel,
            );
        }
        return $options;
    }
    
    private function getAgegroupOptions($selected='') {
        $options = array();
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $agegroups = $em->getRepository("ErsBase\Entity\Agegroup")
                ->findBy(array('ticket_change' => '1'), array('agegroup' => 'ASC'));
        
        $sel = false;
        if($selected == '') {
            $sel = true;
        }
        $options[] = array(
            'value' => '',
            'label' => 'select agegroup',
            'selected' => $sel,
        );
        
        $sel = false;
        if($selected == 0) {
            $sel = true;
        }
        $options[] = array(
            'value' => 0,
            'label' => 'adult',
            'selected' => $sel,
        );
        foreach($agegroups as $agegroup) {
            $sel = false;
            if($agegroup->getId() == $selected) {
                $sel = true;
            }
            $options[] = array(
                'value' => $agegroup->getId(),
                'label' => $agegroup->getName(),
                'selected' => $sel,
            );
        }
        
        return $options;
    }
    
    public function eTicketAction() {
        $lang = $query = $this->params()->fromQuery('lang', 'en');
        $agegroup_id = (int) $query = $this->params()->fromQuery('agegroup', 0);

        $form = new Form\ETicketSelect();
        $form->get('lang')->setValueOptions($this->getLanguageOptions($lang));
        $form->get('agegroup')->setValueOptions($this->getAgegroupOptions($agegroup_id));
        
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $agegroup = $em->getRepository("ErsBase\Entity\Agegroup")
                ->findOneBy(array('id' => $agegroup_id));
        
        return new ViewModel(array(
            'form' => $form,
            'lang' => $lang,
            'agegroup' => $agegroup,
        ));
    }
}
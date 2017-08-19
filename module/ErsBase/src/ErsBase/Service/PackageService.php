<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ErsBase\Service;

use Zend\Session\Container;
use ErsBase\Entity;
use Zend\View\Model\ViewModel;

/**
 * order service
 */
class PackageService
{
    protected $_sl;
    protected $package;
    
    public function __construct() {
    }
    
    /**
     * set ServiceLocator
     * 
     * @param ServiceLocator $sl
     */
    public function setServiceLocator($sl) {
        $this->_sl = $sl;
    }
    
    /**
     * get ServiceLocator
     * 
     * @return ServiceLocator
     */
    protected function getServiceLocator() {
        return $this->_sl;
    }
    
    public function setPackage(Entity\Package $package) {
        $this->package = $package;
    }
    
    public function getPackage() {
        return $this->package;
    }
    
    public function sendEticket() {
        $logger = $this->getServiceLocator()->get('Logger');
        
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $emailService = $this->getServiceLocator()
                ->get('ErsBase\Service\EmailService');

        $package = $this->getPackage();
        $order = $package->getOrder();
        $participant = $package->getParticipant();

        $recipients = [];

        $buyer = $order->getBuyer();
        if($participant->getEmail() == '') {
            $recipients[] = [
                'email' => $buyer,
                'type' => 'to',
            ];
        } elseif($participant->getEmail() == $buyer->getEmail()) {
            $recipients[] = [
                'email' => $buyer,
                'type' => 'to',
            ];
        } else {
            $recipients[] = [
                'email' => $participant,
                'type' => 'to',
            ];
            $recipients[] = [
                'email' => $buyer,
                'type' => 'cc',
            ];
        }

        $settingService = $this->getServiceLocator()
                ->get('ErsBase\Service\SettingService');
        if($settingService->get('ers.bcc_mail') != '') {
            $recipients[] = [
                'email' => $settingService->get('ers.bcc_mail'),
                'type' => 'bcc',
            ];
        }


        $subject = "[".$settingService->get('ers.name_short')."] "._('E-Ticket für')." ".$participant->getFirstname()." ".$participant->getSurname()." (order ".$order->getCode()->getValue().")";

        $viewModel = new ViewModel(array(
            'package' => $package,
        ));
        $viewModel->setTemplate('email/eticket-participant.phtml');
        $viewRender = $this->getServiceLocator()->get('ViewRenderer');
        $html = $viewRender->render($viewModel);

        # generate e-ticket pdf
        $eticketService = $this->getServiceLocator()
            ->get('ErsBase\Service\ETicketService');

        $eticketService->setLanguage('en');
        $eticketService->setPackage($package);
        $eticketFile = $eticketService->generatePdf();

        # send out email
        $attachments = [
            $eticketFile,
        ];

        
        $shortcodeService = $this->getServiceLocator()
                ->get('ErsBase\Service\ShortcodeService');
        $shortcodeService->setText($html);
        $shortcodeService->setObject('package', $package);
        $shortcodeService->setObject('order', $package->getOrder());
        $shortcodeService->setObject('participant', $package->getParticipant());
        $shortcodeService->processFilters();
        $html = $shortcodeService->getText();
        
        $emailService->addMailToQueue(null, $recipients, $subject, $html, true, $attachments);

        $package->setTicketStatus('send_out');
        $em->persist($package);
        $em->flush();

        $logger->info('E-tickets for package '.$package->getCode()->getValue().' has been send out.');
    }
    
}

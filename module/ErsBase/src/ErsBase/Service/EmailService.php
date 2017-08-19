<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ErsBase\Service;

use Zend\Mail;
use Zend\Mime;
use Zend\View\Model\ViewModel;
use ErsBase\Entity;

/**
 * email factory.
 */
class EmailService
{
    protected $_sl;

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

    public function sendExceptionEmail(\Exception $e) {
        
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $role = $em->getRepository('ErsBase\Entity\Role')
                    ->findOneBy(array('roleId' => 'developer'));
        if(!$role) {
            return false;
        }
        $users = $role->getUsers();
        if(count($users) <= 0) {
            return false;
        }
        $recipients = [];
        foreach($users as $user) {
            $recipients[] = $user;
        }
        
        $helper = new \Zend\View\Helper\ServerUrl();
        $url = $helper->__invoke(true);
        $subject = 'An error occurred on '.$url.': '.$e->getMessage();
        
        $auth = $this->getServiceLocator()->get('zfcuser_auth_service');
        if ($auth->hasIdentity()) {
            $email = $auth->getIdentity()->getEmail();
        } else {
            $email = 'not logged in';
        }
        
        $viewModel = new ViewModel(array(
            'message' => 'An error occurred during execution',
            'exception' => $e,
            'email' => $email,
        ));
        
        $viewModel->setTemplate('email/exception.phtml');
        $viewRender = $this->getServiceLocator()->get('ViewRenderer');
        $html = $viewRender->render($viewModel);
        
        $this->addMailToQueue(null, $recipients, $subject, $html);
        
        #$this->send();
        
        return true;
    }
    
    public function sendConfirmationEmail($order_id) {
        $logger = $this->getServiceLocator()->get('Logger');
        
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $order = $em->getRepository('ErsBase\Entity\Order')
                    ->findOneBy(array('id' => $order_id));
        $buyer = $order->getBuyer();
        
        $settingService = $this->getServiceLocator()
                    ->get('ErsBase\Service\SettingService');
        
        $recipients = [];
        $recipients[] = [
            'email' => $buyer,
            'type' => 'to',
        ];
        #if($settingService->get('ers.bcc_email') != '') {
            $recipients[] = [
                'email' => $settingService->get('ers.sender_email'),
                'type' => 'bcc',
            ];    
        #}
        
        #$subject = sprintf(_('Your registration for %s (order %s)'), $config['ERS']['name_short'], $order->getCode()->getValue());
        $subject = sprintf(_('Deine Bestellung für die %s (order %s)'), $settingService->get('ers.name_short'), $order->getCode()->getValue());
        #$this->setSubject($subject);
        
        $config = $this->getServiceLocator()->get('config');
        
        $viewModel = new ViewModel(array(
            'order' => $order,
            'config' => $config,
        ));
        $viewModel->setTemplate('email/order-confirmation.phtml');
        $viewRender = $this->getServiceLocator()->get('ViewRenderer');
        $html = $viewRender->render($viewModel);
        
        #$this->setHtmlMessage($html);
        
        /*$attachments = [
            'public/Terms and Conditions ERS EN v7.pdf',
            'public/Terms and Conditions organisation EN v6.pdf',
            'public/pre-reg cover photo.png',
        ];*/
        
        
        $this->addMailToQueue(null, $recipients, $subject, $html);
        #$this->send();
        
        $logger->info('confirmation mail was send out to '.$order->getBuyer()->getEmail().' for order: '.$order->getCode()->getValue());
        
        return true;
    }
    
    public function addMailToQueue($from, $recipients, $subject, $content, $is_html = true, $attachments = array()) {
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $mailq = new Entity\Mailq();
        
        # from
        if(!$from instanceof Entity\Base\User) {
            if(is_string($from)) {
                $from = $em->getRepository('ErsBase\Entity\User')
                    ->findOneBy(['email' => $from]);
            }
            if(!$from) {
                # use default sender if sender is not set.
                $settingService = $this->getServiceLocator()
                    ->get('ErsBase\Service\SettingService');
                $from = $em->getRepository('ErsBase\Entity\User')
                    ->findOneBy(['email' => $settingService->get('ers.sender_email')]);
                if(!$from) {
                    $from = new Entity\User();
                    $from->setEmail($settingService->get('ers.sender_email'));
                    $from->setFirstname('');
                    $from->setSurname('');
                    $from->setActive(1);
                    $em->persist($from);
                    $em->flush();
                }
            }
            
        }
        
        $mailq->setFrom($from);
        
        # subject
        $mailq->setSubject($subject);
        
        # content
        $mailq->setIsHtml($is_html);
        if($mailq->getIsHtml()) {
            $mailq->setHtmlMessage($content);
        } else {
            $mailq->setTextMessage($content);
        }
        
        # attachments
        foreach($attachments as $attachment) {
            $att = $attachment;
            if(!$attachment instanceof Entity\MailAttachment) {
                $att = new Entity\MailAttachment();
                $att->setLocation($attachment);
            }
            $att->setMailq($mailq);
            $mailq->addMailAttachment($att);
        }
        
        $em->persist($mailq);
        $em->flush();
        
        # recipients
        foreach($recipients as $recipient) {
            if(is_array($recipient)) {
                $this->addUser($mailq, $recipient['email'], $recipient['type']);
            } else {
                # we do only send with to header
                $this->addUser($mailq, $recipient);
            }
        }
        
        $em->flush();
    }
    
    private function addUser($mailq, $recipient, $type = 'to') {
        $em = $this->getServiceLocator()
                ->get('Doctrine\ORM\EntityManager');
        
        if(!$recipient instanceof Entity\User) {
            $email = $recipient;
            $recipient = $em->getRepository('ErsBase\Entity\User')
                    ->findOneBy(['email' => $recipient]);
            if(!$recipient) {
                $recipient = new Entity\User();
                $recipient->setEmail($email);
                
                $em->persist($recipient);
                $em->flush();
            }
        }

        $mailqHasUser = new Entity\MailqHasUser();
        $mailqHasUser->setUser($recipient);
        $mailqHasUser->setUserId($recipient->getId());

        $mailqHasUser->setMailq($mailq);
        $mailqHasUser->setMailqId($mailq->getId());
        switch($type) {
            case 'cc':
                $mailqHasUser->setCc();
                break;
            case 'bcc':
                $mailqHasUser->setBcc();
                break;
            case 'to':
            default:
                $mailqHasUser->setTo();
                break;
        }
        
        $em->persist($mailqHasUser);
    }
    
    /*
     * TODO: check plain/text mail with attachment.
     */
    private function mailqEntityToMessage(Entity\Mailq $mailq) {
        $em = $this->getServiceLocator()
                ->get('Doctrine\ORM\EntityManager');
        
        $content  = new Mime\Message();
        
        if($mailq->getIsHtml()) {
            $content->addPart($mailq->getHtmlMessage());
        }
        $content->addPart($mailq->getTextMessage());
        
        if(count($mailq->getAttachments()) == 0) {
            $body = $content;
        } else {
            $contentPart = new Mime\Part($content->generateMessage());        
            $contentPart->setType('multipart/alternative;' . PHP_EOL . ' boundary="' . $content->getMime()->boundary() . '"');

            $body = new Mime\Message();
            $body->addPart($contentPart);
            foreach($mailq->getAttachments() as $att) {
                $body->addPart($att);
            }
        }

        $message = new Mail\Message();
        $message->setEncoding('utf-8');
        
        $message->setBody($body);

        $type = 'text/plain';
        if(count($mailq->getAttachments()) == 0) {
            if($mailq->getIsHtml()) {
                $type = 'multipart/alternative';
            }
        } else {
            // Important to get all attachments into this email.
            $type = 'multipart/mixed';
        }

        $message->getHeaders()->get('content-type')
                ->addParameter('charset', 'utf-8')
                ->setType($type);
        
        if(!$mailq->getTo()) {
            $em->remove($mailq);
            $em->flush();
            return false;
            #throw new \Exception('mail in mailq is invalid: '.$mailq->getId());
        }
        
        foreach($mailq->getTo() as $user) {
            $message->addTo($user->getEmail());
        }
        foreach($mailq->getCc() as $user) {
            $message->addCc($user->getEmail());
        }
        foreach($mailq->getBcc() as $user) {
            $message->addBcc($user->getEmail());
        }
        
        if(!$mailq->getFrom()) {
            $settingService = $this->getServiceLocator()
                    ->get('ErsBase\Service\SettingService');
            $user = $em->getRepository('ErsBase\Entity\User')
                    ->findOneBy(['email' => $settingService->get('ers.sender_email')]);
            if(!$user) {
                $user = new Entity\User();
                $user->setEmail($settingService->get('ers.sender_email'));
                $user->setFirstname('');
                $user->setSurname('');
                $user->setActive(1);
                $em->persist($user);
                $em->flush();
            }
            $mailq->setFrom($user);
        }
        
        $message->addFrom($mailq->getFrom()->getEmail());
        $message->setSubject($mailq->getSubject());
        
        return $message;
    }
    
    public function mailqWorker() {
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $limit = 10; // to run process-mailq every five minutes
        $mailqs = $em->getRepository('ErsBase\Entity\Mailq')
                ->findBy(array(), array('created' => 'ASC'), $limit);
        
        foreach($mailqs as $mailq) {
            $message = $this->mailqEntityToMessage($mailq);
            
            if(!$message) {
                continue;
            }
            
            $transport = new Mail\Transport\Sendmail();
            $transport->send($message);
            
            $em->remove($mailq);
            $em->flush();
        }
    }
}

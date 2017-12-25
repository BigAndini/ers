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
class EmailServiceOld
{
    protected $_sl;
    protected $text_message;
    protected $html_message;
    protected $attachments;
    protected $from;
    protected $to;
    protected $cc;
    protected $bcc;
    protected $subject;
    protected $is_html;
    protected $is_text;

    public function __construct() {
        $this->to = array();
        $this->cc = array();
        $this->bcc = array();
        $this->attachments = array();
        $this->setIsHtml(false);
        $this->setIsText(false);
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
    
    public function setIsHtml($is_html) {
        $this->is_html = $is_html;
        
        return $this;
    }
    
    public function getIsHtml() {
        return $this->is_html;
    }
    
    public function isHtml() {
        return $this->is_html;
    }
    
    public function setIsText($is_text) {
        $this->is_text = $is_text;
        
        return $this;
    }
    
    public function setTextMessage($text) {
        $this->text_message = new Mime\Part($text);
        $this->text_message->type        = Mime\Mime::TYPE_TEXT;
        $this->text_message->charset     = 'utf-8';
        $this->text_message->encoding    = Mime\Mime::ENCODING_QUOTEDPRINTABLE;
        $this->text_message->disposition = Mime\Mime::DISPOSITION_INLINE;
        $this->setIsText(true);
    }
    
    public function getTextMessage() {
        return $this->text_message;
    }
    
    public function isText() {
        return $this->is_text;
    }
    
    public function setHtmlMessage($markup) {
        $this->html_message = new Mime\Part($markup);
        $this->html_message->type        = Mime\Mime::TYPE_HTML;
        $this->html_message->charset     = 'utf-8';
        #$this->html_message->encoding    = Mime\Mime::ENCODING_8BIT;
        $this->html_message->encoding    = Mime\Mime::ENCODING_QUOTEDPRINTABLE;
        $this->html_message->disposition = Mime\Mime::DISPOSITION_INLINE;
        #$convert_html = mb_convert_encoding($markup, 'HTML-ENTITIES', 'UTF-8');
        $html2text = new \Html2Text\Html2Text($markup);
        $text = $html2text->getText();
        $this->setTextMessage($text);
        $this->setIsHtml(true);
    }
    
    public function getHtmlMessage() {
        return $this->html_message;
    }
    
    public function setFrom($from) {
        $this->from = $from;
    }
    
    public function getFrom() {
        return $this->from;
    }
    
    public function addTo(Entity\User $user) {
        $this->to[] = $user;
    }
    
    public function getTo() {
        return $this->to;
    }
    
    public function addCc(Entity\User $user) {
        $this->cc[] = $user;
    }
    
    public function getCc() {
        return $this->cc;
    }
    
    public function addBcc(Entity\User $user) {
        $this->bcc[] = $user;
    }
    
    public function getBcc() {
        return $this->bcc;
    }
    
    public function setSubject($subject) {
        $this->subject = $subject;
    }
    
    public function getSubject() {
        return $this->subject;
    }
    
    public function addAttachment($attachment) {
        if($attachment instanceof Mime\Part) {
            $this->attachments[] = $attachment;
        } else {
            $pathToAtt = $attachment;
            if(!file_exists($attachment)) {
                $pathToAtt = getcwd() . '/' . $attachment;
                if(!file_exists($pathToAtt)) {
                    throw new \Exception("Unable to add attachment");
                }
            }
            
            #$att = new Mime\Part(fopen($pathToAtt, 'r'));
            $att = new Mime\Part(file_get_contents($pathToAtt));
            #$attachment->type = 'image/jpeg';
            $att->type = \mime_content_type($pathToAtt);
            #$att->type = Mime\Mime::TYPE_OCTETSTREAM;
            $pattern = array(
                '/\ /'
            );
            $replace = array(
                '-',
            );
            $att->filename = preg_replace($pattern, $replace, basename($pathToAtt));
            $att->encoding    = Mime\Mime::ENCODING_BASE64;
            $att->disposition = Mime\Mime::DISPOSITION_ATTACHMENT;
            
            $this->attachments[] = $att;
        }
        
    }
    
    public function getAttachments() {
        return $this->attachments;
    }

    public function send() {
        # differ the following scenarios:
        # - plain text email
        # - plain text email with 1 or n attachments
        # - html email with text alternative
        # - html email with text alternative and 1 or n attachments
     
        $content  = new Mime\Message();
        
        $content->addPart($this->getTextMessage());
        if($this->isHtml()) {
            $content->addPart($this->getHtmlMessage());
        }
        
        if(count($this->getAttachments()) == 0) {
            $body = $content;
        } else {
            $contentPart = new Mime\Part($content->generateMessage());        
            $contentPart->setType('multipart/alternative;' . PHP_EOL . ' boundary="' . $content->getMime()->boundary() . '"');

            $body = new Mime\Message();
            $body->addPart($contentPart);
            foreach($this->getAttachments() as $att) {
                $body->addPart($att);
            }
        }

        $message = new Mail\Message();
        $message->setEncoding('utf-8');
        
        $message->setBody($body);
        if($this->isHtml()) {
            if(count($this->getAttachments()) == 0) {
                $message->getHeaders()->get('content-type')
                    ->addParameter('charset', 'utf-8')
                    ->setType('multipart/alternative');
            } else {
                $message->getHeaders()->get('content-type')
                    ->addParameter('charset', 'utf-8')
                    ->setType('multipart/mixed'); // Important to get all attachments into this email.
            }
            
        } else {
            $message->getHeaders()->get('content-type')
                ->addParameter('charset', 'utf-8')
                ->setType('text/plain');
        }
        
        foreach($this->getTo() as $user) {
            $message->addTo($user->getEmail());
        }
        foreach($this->getCc() as $user) {
            $message->addCc($user->getEmail());
        }
        foreach($this->getBcc() as $user) {
            $message->addBcc($user->getEmail());
        }
        
        $config = $this->getServiceLocator()->get('config');
        $this->setFrom($config['ERS']['sender_email']);
        
        $message->addFrom($this->getFrom());
        $message->setSubject($this->getSubject());
        
        $transport = new Mail\Transport\Sendmail();
        $transport->send($message);
    }
    
    public function sendExceptionEmail(\Exception $e) {
        $config = $this->getServiceLocator()->get('config');
        
        $this->setFrom($config['ERS']['sender_email']);
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $role = $entityManager->getRepository('ErsBase\Entity\Role')
                    ->findOneBy(array('roleId' => 'supradm'));
        $users = $role->getUsers();
        if(count($users) <= 0) {
            return false;
        }
        foreach($users as $user) {
            $this->addTo($user);
        }
        
        $helper = new \Zend\View\Helper\ServerUrl();
        $url = $helper->__invoke(true);
        $this->setSubject('An error occurred on '.$url.': '.$e->getMessage());
        
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
        
        $this->setHtmlMessage($html);
        
        $this->send();
        
        return true;
    }
    
    public function sendConfirmationEmail($order_id) {
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $order = $entityManager->getRepository('ErsBase\Entity\Order')
                    ->findOneBy(array('id' => $order_id));
        $buyer = $order->getBuyer();
        
        $config = $this->getServiceLocator()->get('config');
        
        $this->setFrom($config['ERS']['sender_email']);
        
        $this->addTo($buyer);
        
        $bcc = new Entity\User();
        $bcc->setEmail($this->getFrom());
        $this->addBcc($bcc);
        
        #$subject = sprintf(_('Your registration for %s (order %s)'), $config['ERS']['name_short'], $order->getCode()->getValue());
        $subject = sprintf(_('Deine Bestellung für die %s (order %s)'), $config['ERS']['name_short'], $order->getCode()->getValue());
        $this->setSubject($subject);
        
        $viewModel = new ViewModel(array(
            'order' => $order,
            'config' => $config,
        ));
        $viewModel->setTemplate('email/order-confirmation.phtml');
        $viewRender = $this->getServiceLocator()->get('ViewRenderer');
        $html = $viewRender->render($viewModel);
        
        $this->setHtmlMessage($html);
        
        /*$terms1 = getcwd().'/public/Terms and Conditions ERS EN v7.pdf';
        $this->addAttachment($terms1);
        
        $terms2 = getcwd().'/public/Terms and Conditions organisation EN v6.pdf';
        $this->addAttachment($terms2);
        
        $promo = getcwd().'/public/pre-reg cover photo.png';
        $this->addAttachment($promo);*/
        
        $this->send();
        
        # TODO: Create log entry that email was sent.
        $log = new Entity\Log();
        $log->setUser($order->getBuyer());
        $log->setData('confirmation mail was send out to '.$order->getBuyer()->getEmail().' for order: '.$order->getCode()->getValue());
        $entityManager->persist($log);

        $entityManager->flush();
        
        return true;
    }
    
    public function addMailToQueue($from, $recipients, $subject, $content, $is_html = true, $attachments = array()) {
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $mailq = new Entity\Mailq();
        
        # from
        if(!$from instanceof Entity\User) {
            if(!is_string($from)) {
                throw new Exception('Unable to convert from into a sender user.');
            }
            $from = $entityManager->getRepository('ErsBase\Entity\User')
                ->findOneBy(['email' => $from]);
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
        
        $entityManager->persist($mailq);
        $entityManager->flush();
        
        # recipients
        foreach($recipients as $recipient) {
            if(is_array($recipient)) {
                $this->addUser($mailq, $recipient['email'], $recipient['type']);
            } else {
                # we do only send with to header
                $this->addUser($mailq, $recipient);
            }
        }
        
        $entityManager->flush();
    }
    
    private function addUser($mailq, $recipient, $type = 'to') {
        $entityManager = $this->getServiceLocator()
                ->get('Doctrine\ORM\EntityManager');
        
        if(!$recipient instanceof Entity\User) {
            $recipient = $entityManager->getRepository('ErsBase\Entity\User')
                    ->findOneBy(['email' => $recipient]);
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
        
        $entityManager->persist($mailqHasUser);
    }
    
    /*
     * TODO: check plain/text mail with attachment.
     */
    private function mailqEntityToMessage(Entity\Mailq $mailq) {
        $entityManager = $this->getServiceLocator()
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
            $entityManager->remove($mailq);
            $entityManager->flush();
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
            $user = $entityManager->getRepository('ErsBase\Entity\User')
                    ->findOneBy(['email' => $settingService->get('ers.sender_email')]);
            if(!$user) {
                $user = new Entity\User();
                $user->setEmail($settingService->get('ers.sender_email'));
                $user->setFirstname('');
                $user->setSurname('');
                $user->setActive(1);
                $entityManager->persist($user);
                $entityManager->flush();
            }
            $mailq->setFrom($user);
        }
        
        $message->addFrom($mailq->getFrom()->getEmail());
        $message->setSubject($mailq->getSubject());
        
        return $message;
    }
    
    public function mailqWorker() {
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $limit = 50;
        $mailqs = $entityManager->getRepository('ErsBase\Entity\Mailq')
                ->findBy(array(), array('created' => 'ASC'), $limit);
        
        foreach($mailqs as $mailq) {
            $message = $this->mailqEntityToMessage($mailq);
            
            if(!$message) {
                continue;
            }
            
            $transport = new Mail\Transport\Sendmail();
            $transport->send($message);
            
            $entitaManager->remove($mailq);
            $entityManager->flush();
        }
    }
}

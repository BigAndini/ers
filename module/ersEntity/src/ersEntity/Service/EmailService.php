<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ersEntity\Service;

use Zend\Mail;
use Zend\Mime;
use Zend\View\Model\ViewModel;
use ersEntity\Entity;

/**
 * email factory.
 */
class EmailService
{
    protected $_sl;
    protected $textMessage;
    protected $htmlMessage;
    protected $attachments;
    protected $from = 'prereg@eja.net';
    protected $to;
    protected $cc;
    protected $bcc;
    protected $subject;
    protected $isHtml;
    protected $isText;

    public function __construct() {
        $this->to = array();
        $this->cc = array();
        $this->bcc = array();
        $this->attachments = array();
        $this->isHtml = false;
        $this->isText = false;
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
    
    public function setTextMessage($text) {
        $this->textMessage = new Mime\Part($text);
        $this->textMessage->type        = Mime\Mime::TYPE_TEXT;
        $this->textMessage->charset     = 'utf-8';
        $this->textMessage->encoding    = Mime\Mime::ENCODING_QUOTEDPRINTABLE;
        $this->textMessage->disposition = Mime\Mime::DISPOSITION_INLINE;
        $this->isText = true;
    }
    
    public function getTextMessage() {
        return $this->textMessage;
    }
    
    public function isTextMessage() {
        return $this->isText;
    }
    
    public function setHtmlMessage($markup) {
        $this->htmlMessage = new Mime\Part($markup);
        $this->htmlMessage->type        = Mime\Mime::TYPE_HTML;
        $this->htmlMessage->charset     = 'utf-8';
        #$this->htmlMessage->encoding    = Mime\Mime::ENCODING_8BIT;
        $this->htmlMessage->encoding    = Mime\Mime::ENCODING_QUOTEDPRINTABLE;
        $this->htmlMessage->disposition = Mime\Mime::DISPOSITION_INLINE;
        #$convert_html = mb_convert_encoding($markup, 'HTML-ENTITIES', 'UTF-8');
        $html2text = new \Html2Text\Html2Text($markup);
        $text = $html2text->getText();
        $this->setTextMessage($text);
        $this->isHtml = true;
    }
    
    public function getHtmlMessage() {
        return $this->htmlMessage;
    }
    
    public function isHtmlMessage() {
        return $this->isHtml;
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
        
        if(!$this->isHtmlMessage()) {
            $content->addPart($this->getTextMessage());
        } else {
            $content->addPart($this->getTextMessage());
            $content->addPart($this->getHtmlMessage());
        }
        
        if(count($this->getAttachments()) == 0) {
            $body = $content;
        } else {
            $contentPart = new Mime\Part($content->generateMessage());        
            $contentPart->type = 'multipart/alternative;' . PHP_EOL . ' boundary="' . $content->getMime()->boundary() . '"';

            $body = new Mime\Message();
            $body->addPart($contentPart);
            foreach($this->getAttachments() as $att) {
                $body->addPart($att);
            }
        }

        $message = new Mail\Message();
        $message->setEncoding('utf-8');
        
        $message->setBody($body);
        if($this->isHtmlMessage()) {
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
        
        $message->addFrom($this->getFrom());
        $message->setSubject($this->getSubject());
        
        $transport = new Mail\Transport\Sendmail();
        $transport->send($message);
    }
    
    public function sendExceptionEmail(\Exception $e) {
        $this->setFrom('prereg@eja.net');
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $role = $em->getRepository("ersEntity\Entity\Role")
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
        
        $viewModel = new ViewModel(array(
            'message' => 'An error occurred during execution',
            'exception' => $e,
        ));
        
        $viewModel->setTemplate('email/exception.phtml');
        $viewRender = $this->getServiceLocator()->get('ViewRenderer');
        $html = $viewRender->render($viewModel);
        
        $this->setHtmlMessage($html);
        
        $this->send();
        
        return true;
    }
    
    public function sendConfirmationEmail($order_id) {
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        #$session_order = new Container('order');
        $order = $em->getRepository("ersEntity\Entity\Order")
                    ->findOneBy(array('id' => $order_id));
        $buyer = $order->getBuyer();
        
        $this->setFrom('prereg@eja.net');
        
        $this->addTo($buyer);
        
        $bcc = new Entity\User();
        $bcc->setEmail($this->getFrom());
        $this->addBcc($bcc);
        
        $subject = "Your registration for EJC 2015 (order ".$order->getCode()->getValue().")";
        $this->setSubject($subject);
        
        $viewModel = new ViewModel(array(
            'order' => $order,
        ));
        $viewModel->setTemplate('email/order-confirmation.phtml');
        $viewRender = $this->getServiceLocator()->get('ViewRenderer');
        $html = $viewRender->render($viewModel);
        
        $this->setHtmlMessage($html);
        
        $terms1 = getcwd().'/public/Terms-and-Conditions-ERS-EN-v3.pdf';
        $terms2 = getcwd().'/public/Terms-and-Conditions-ORGA-EN-v2.pdf';
        $this->addAttachment($terms1);
        $this->addAttachment($terms2);
        
        $this->send();
        
        $orderStatus = new Entity\OrderStatus();
        $orderStatus->setOrder($order);
        $orderStatus->setValue('confirmation sent');
        $em->persist($orderStatus);
        $em->flush();
        
        return true;
    }
}

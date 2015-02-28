<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace PreReg\Service;

use Zend\Mail;
use Zend\Mime;
use ersEntity\Entity;

/**
 * Top navigation factory.
 */
class EmailFactory
{
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
    
    public function setTextMessage($text) {
        $this->textMessage = new Mime\Part($text);
        $this->textMessage->type = 'text/plain';
        $this->isText = true;
    }
    
    public function getTextMessage() {
        return $this->textMessage;
    }
    
    public function isTextMessage() {
        return $this->isText;
    }
    
    public function setHtmlMessage($html) {
        $this->htmlMessage = new Mime\Part($html);
        $this->htmlMessage->type = 'text/html';
        $this->setTextMessage(\Html2Text\Html2Text::convert($html));
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
            $pathToAtt = getcwd() . '/' . $attachment;
            if(!file_exists($pathToAtt)) {
                throw new \Exception("Unable to add attachment");
            }
            $att = new Mime\Part(fopen($pathToAtt, 'r'));
            #$attachment->type = 'image/jpeg';
            $att->type = \mime_content_type($pathToAtt);
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
            #$content->setParts(array($this->getTextMessage(), $this->getHtmlMessage()));
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
        $message->setBody($body);
        
        if($this->isHtmlMessage()) {
            $message->getHeaders()->get('content-type')
                ->addParameter('charset', 'UTF-8')
                ->setType('multipart/alternative');
        } else {
            $message->getHeaders()->get('content-type')
                ->addParameter('charset', 'UTF-8')
                ->setType('text/plain');
        }

        $transport = new Mail\Transport\Sendmail();
        $transport->send($message);
    }
    /**
     * @return string
     */
    protected function getName()
    {
        return 'top_nav';
    }
}

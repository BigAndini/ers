<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace PreReg\Service;

use Zend\Navigation\Service\AbstractNavigationFactory;

use ersEntity\Entity;

/**
 * Top navigation factory.
 */
class EmailFactory extends AbstractNavigationFactory
{
    protected $textMessage;
    protected $htmlMessage;
    protected $attachments;
    protected $from = 'prereg@eja.net';
    protected $to;
    protected $cc;
    protected $bcc;
    protected $subject;

    public function setTextMessage($text) {
        $this->textMessage = $text;
    }
    
    public function getTextMessage() {
        return $this->textMessage;
    }
    
    public function setHtmlMessage($html) {
        $this->htmlMessage = $html;
    }
    
    public function getHtmlMessage() {
        return $this->htmlMessage;
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
    
    public function addAttachment(Mime\Part $attachment) {
        $this->attachments[] = $attachment;
    }

    public function send() {
        $content  = new Mime\Message();
        
        #$textContent = 'This is the text of the email.';
        $textPart = new Mime\Part($this->getTextMessage());
        $textPart->type = 'text/plain';
        
        #$htmlMarkup = '<html><body><h1>This is the text of the email.</h1></body></html>';
        $htmlPart = new Mime\Part($this->getHtmlMessage());
        $htmlPart->type = 'text/html';
        
        $content->setParts(array($textPart, $htmlPart));

        $contentPart = new Mime\Part($content->generateMessage());        
        $contentPart->type = 'multipart/alternative;' . PHP_EOL . ' boundary="' . $content->getMime()->boundary() . '"';

        $pathToImage = getcwd() . '/public/img/logo.jpg';
        $attachment = new Mime\Part(fopen($pathToImage, 'r'));
        #$attachment->type = 'application/pdf';
        $attachment->type = 'image/jpeg';
        $attachment->filename = 'logo.jpg';
        $attachment->encoding    = Mime\Mime::ENCODING_BASE64;
        $attachment->disposition = Mime\Mime::DISPOSITION_ATTACHMENT;

        $body = new Mime\Message();
        $body->setParts(array($contentPart, $attachment));
        #$body->setParts(array($contentPart));

        $message = new Message();
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

        $transport = new Transport\Sendmail();
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

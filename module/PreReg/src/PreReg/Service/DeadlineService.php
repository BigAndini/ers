<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace PreReg\Service;

use Zend\Session\Container;
use ersEntity\Entity;

class DeadlineService
{
    protected $deadlines;
    
    public function __construct() {
        
    }
    
    public function setDeadlines($deadlines) {
        $this->deadlines = $deadlines;
    }
    
    public function getDeadline() {
        $cartContainer = new Container('cart');
        if($cartContainer->deadline instanceof Entity\Deadline) {
            return $cartContainer->deadline;
        }
        
        $ret = null;
        $now = new \DateTime();
        foreach($this->deadlines as $deadline) {
            if($now->getTimestamp() > $deadline->getDeadline()->getTimestamp()) {
                continue;
            }
            if($ret == null) {
                $ret = $deadline;
                continue;
            }
            if($deadline->getDeadline()->getTimestamp() < $ret->getDeadline()->getTimestamp()) {
                #error_log('found earlier deadline: '.$deadline->getDeadline()->format('d.m.Y H:i:s'));
                $ret = $deadline;
            }
        }
        $cartContainer->deadline = $ret;
        
        return $cartContainer->deadline;
    }
}

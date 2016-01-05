<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ErsBase\Service;

#use Zend\Session\Container;
#use ErsBase\Entity;

class DeadlineService
{
    protected $deadlines;
    protected $compareDate;
    
    public function __construct() {
        $this->compareDate = new \DateTime;
    }
    
    public function setCompareDate(\DateTime $compareDate) {
        $this->compareDate = $compareDate;
    }
    
    public function getCompareDate() {
        return $this->compareDate;
    }
    
    public function setDeadlines($deadlines) {
        $this->deadlines = $deadlines;
    }
    
    public function getDeadline() {
        $ret = null;
        $now = $this->getCompareDate();
        foreach($this->deadlines as $deadline) {
            if($now->getTimestamp() > $deadline->getDeadline()->getTimestamp()) {
                continue;
            }
            if($ret == null) {
                $ret = $deadline;
                continue;
            }
            if($deadline->getDeadline()->getTimestamp() < $ret->getDeadline()->getTimestamp()) {
                $ret = $deadline;
            }
        }
        
        return $ret;
    }
}

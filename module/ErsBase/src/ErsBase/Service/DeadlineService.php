<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ErsBase\Service;

#use Zend\Session\Container;
#use ErsBase\Entity;

class DeadlineService extends ServiceLocatorAwareService
{
    protected $deadlines = array();
    protected $mode;
    protected $serviceManager;
    protected $compareDate;
    
    public function __construct() {
        $this->compareDate = new \DateTime;
    }
    
    public function setMode($mode) {
        $this->mode = $mode;
    }
    public function getMode() {
        return $this->mode;
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
    
    public function getDeadlines() {
        if(count($this->deadlines) <= 0) {
            $entityManager = $this->getServiceLocator()
                    ->get('Doctrine\ORM\EntityManager');
            $criteria = array();
            switch($this->getMode()) {
                case 'price':
                    $criteria['price_change'] = 1;
                    break;
                case 'ticket':
                    $criteria['ticket_change'] = 1;
                    break;
                default:
                    throw new \Exception('Please set a mode for Deadline Service: price or ticket');
                    break;
            }
            $deadlines = $entityManager->getRepository('ErsBase\Entity\Deadline')
                        ->findBy($criteria);
            $this->setDeadlines($deadlines);
        }
        return $this->deadlines;
    }
    
    public function getDeadline() {
        $ret = null;
        $now = $this->getCompareDate();
        foreach($this->getDeadlines() as $deadline) {
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

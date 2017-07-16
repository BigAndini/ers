<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ErsBase\Service;

use ErsBase\Entity;

class AgegroupService
{
    protected $agegroups;
    protected $mode = '';
    protected $sm;
    
    public function __construct() {
        $this->agegroups = array();
    }
    
    public function setServiceLocator($sm) {
        $this->sm = $sm;
    }
    public function getServiceLocator() {
        return $this->sm;
    }
    
    public function setMode($mode) {
        $this->mode = $mode;
    }
    public function getMode() {
        return $this->mode;
    }
    
    public function setAgegroups($agegroups) {
        $this->agegroups = $agegroups;
    }
    
    public function getAgegroups() {
        if(count($this->agegroups) <= 0) {
            $entityManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
            $criteria = array();
            switch($this->getMode()) {
                case 'price':
                    $criteria['price_change'] = 1;
                    break;
                case 'ticket':
                    $criteria['ticket_change'] = 1;
                    break;
                default:
                    throw new \Exception('Please set a mode for Agegroup Service: price or ticket');
                    break;
            }
            $agegroups = $entityManager->getRepository('ErsBase\Entity\Agegroup')
                        ->findBy($criteria);
            $this->setAgegroups($agegroups);
        }
        return $this->agegroups;
    }
    
    public function getAgegroupByUser(Entity\User $user = null) {
        $ret = null;
        if($user == null) {
            return $ret;
        }
        $birthday = $user->getBirthday();
        if($birthday == null) {
            return $ret;
        }
        return $this->getAgegroupByDate($birthday);
    }
    
    public function getAgegroupByDate(\DateTime $date = null) {
        $ret = null;
        if($date == null) {
            return $ret;
        }
        foreach($this->getAgegroups() as $agegroup) {
            if($date->getTimestamp() < $agegroup->getAgegroup()->getTimestamp()) {
                continue;
            }
            if($ret == null) {
                $ret = $agegroup;
                continue;
            }
            if($agegroup->getAgegroup()->getTimestamp() > $ret->getAgegroup()->getTimestamp()) {
                $ret = $agegroup;
            }
        }
        
        return $ret;
    }
}

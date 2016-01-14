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
    
    public function __construct() {
        $this->agegroups = array();
    }
    
    public function setAgegroups($agegroups) {
        $this->agegroups = $agegroups;
    }
    
    public function getAgegroups() {
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
        foreach($this->agegroups as $agegroup) {
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

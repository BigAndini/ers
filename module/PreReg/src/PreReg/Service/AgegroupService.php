<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace PreReg\Service;

use Zend\Session\Container;
use ersEntity\Entity;

class AgegroupService
{
    protected $agegroups;
    
    public function __construct() {
        
    }
    
    public function setAgegroups($agegroups) {
        $this->agegroups = $agegroups;
    }
    
    public function getAgegroupByUser(Entity\User $user) {
        $ret = null;
        $birthday = $user->getBirthday();
        if($birthday == null) {
            return null;
        }
        error_log('got birthday: '.$birthday->format('d.m.Y'));
        foreach($this->agegroups as $agegroup) {
            if($birthday->getTimestamp() < $agegroup->getAgegroup()->getTimestamp()) {
                continue;
            }
            if($ret == null) {
                error_log('set first agegroup: '.$agegroup->getAgegroup()->format('d.m.Y'));
                $ret = $agegroup;
                continue;
            }
            if($agegroup->getAgegroup()->getTimestamp() > $ret->getAgegroup()->getTimestamp()) {
                error_log('found agegroup: '.$agegroup->getAgegroup()->format('d.m.Y'));
                $ret = $agegroup;
            }
        }
        
        return $ret;
    }
}

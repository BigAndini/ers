<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace PreReg\Model\Entity;

use PreReg\Model;

class Package extends Entity {
    protected $id;
    protected $Order_id;
    protected $Participant_id;
    protected $Barcode_id;
    
    protected $participant;
    protected $items;
    
    public function __construct() {
        parent::__construct();
        $this->participant = new Model\Entity\User();
        $this->items = array();
    }
    
    public function __sleep() {
        return array(
            'id',
            'Order_id',
            'Participant_id',
            'Barcode_id',
            'participant',
            'items',
            'updated',
            'created',
        );
    }
    
    public function exchangeArray($data) {
        if(is_object($data)) {
            $this->id = (!empty($data->id)) ? $data->id : null;
            $this->Order_id = (!empty($data->Order_id)) ? $data->Order_id : null;
            $this->Participant_id = (!empty($data->Participant_id)) ? $data->Participant_id : null;
            $this->Barcode_id = (!empty($data->Barcode_id)) ? $data->Barcode_id : null;
        } elseif(is_array($data)) {
            $this->id  = (!empty($data['id'])) ? $data['id'] : null;
            $this->Order_id  = (!empty($data['Order_id'])) ? $data['Order_id'] : null;
            $this->Participant_id  = (!empty($data['Participant_id'])) ? $data['Participant_id'] : null;
            $this->Barcode_id  = (!empty($data['Barcode_id'])) ? $data['Barcode_id'] : null;
        } else {
            error_log('exchangeArray: given data is either an object nor an array!');
        }
        parent::exchangeArray($data);
    }
    
    public function getParticipant() {
        return $this->participant;
    }
    public function setParticipant(Model\Entity\User $participant) {
        $this->participant = $participant;
    }
    
    public function getItems() {
        error_log('found '.count($this->items).' items');
        return $this->items;
    }
    public function getItemById($id) {
        foreach($this->items as $i) {
            if($i->getId() == $id) {
                return $i;
            }
        }
        return false;
    }
    
    public function addItem(Model\Entity\Item $item) {
        error_log('added item '.$item->getName());
        $this->items[] = $item;
    }
}
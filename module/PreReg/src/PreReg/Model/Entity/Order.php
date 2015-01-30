<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace PreReg\Model\Entity;

class Order extends Entity {
    protected $id;
    protected $Purchaser_id;
    protected $PaymentType_id;
    protected $matchKey;
    protected $invoiceDetail;
    protected $Barcode_id;
    
    protected $packages;
    
    
    public function __construct() {
        error_log('--> Generating new Order <--');
        parent::__construct();
        $this->packages = array();
        
        $purchaser = new User();
        $this->addParticipant($purchaser);
    }
    
    public function __sleep() {
        return array(
            'id',
            'Purchaser_id',
            'PaymentType_id',
            'matchKey',
            'invoiceDetail',
            'Barcode_id',
            'packages',
            'updated',
            'created',
        );
    }
    
    public function exchangeArray($data) {
        if(is_object($data)) {
            $this->id = (!empty($data->id)) ? $data->id : null;
            $this->Purchaser_id = (!empty($data->Purchaser_id)) ? $data->Purchaser_id : null;
            $this->PaymentType_id = (!empty($data->PaymentType_id)) ? $data->PaymentType_id : null;
            $this->matchKey = (!empty($data->matchKey)) ? $data->matchKey : null;
            $this->invoiceDetail = (!empty($data->invoiceDetail)) ? $data->invoiceDetail : null;
            $this->Barcode_id = (!empty($data->Barcode_id)) ? $data->Barcode_id : null;
        } elseif(is_array($data)) {
            $this->id  = (!empty($data['id'])) ? $data['id'] : null;
            $this->Purchaser_id  = (!empty($data['Purchaser_id'])) ? $data['Purchaser_id'] : null;
            $this->PaymentType_id  = (!empty($data['PaymentType_id'])) ? $data['PaymentType_id'] : null;
            $this->matchKey  = (!empty($data['matchKey'])) ? $data['matchKey'] : null;
            $this->invoiceDetail  = (!empty($data['invoiceDetail'])) ? $data['invoiceDetail'] : null;
            $this->Barcode_id  = (!empty($data['Barcode_id'])) ? $data['Barcode_id'] : null;
        } else {
            error_log('exchangeArray: given data is either an object nor an array!');
        }
        parent::exchangeArray($data);
    }
    
    public function getParticipants() {
        $participants = array();
        for($i = 0; $i < count($this->packages); $i++) {
            if($this->packages[$i]->getParticipant()->getPrename() != '' && $this->packages[$i]->getParticipant()->getSurname() != '') {
                $participants[$i] = $this->packages[$i]->getParticipant();
            }
        }
        
        return $participants;
    }
    public function addParticipant($user) {
        $package = new Package();
        $package->setParticipant($user);
        $this->packages[] = $package;
    }
    public function getParticipantById($id) {
        return $this->participants[$id];
    }
    
    public function getPackages() {
        return $this->packages;        
    }
    
    public function getItems() {
        return $this->items;
    }
    public function getItemCount() {
        return count($this->items);
    }
    public function getItemById($id) {
        if(isset($this->items[$id])) {
            return $this->items[$id];
        }
    }
    public function addItem($item, $participant_id=0) {
        if($participant_id == '') {
            $participant_id = 0;
        }
        $this->packages[$participant_id]->addItem($item);
        /*foreach($this->packages as $p) {
            error_log('Check participant_id: '.$p->getParticipant()->getId().' == '.$participant_id);
            if($p->getParticipant()->getId() == $participant_id) {
                $p->addItem($item);
                return;
            }
        }
        error_log('Unable to find package with participant_id '.$participant_id);*/
    }
    public function editItem($id, $item) {
        $this->items[$id] = $item;
    }
    public function deleteItemById($id) {
        if(isset($this->items[$id])) {
            unset($this->items[$id]);
        }
    }
}
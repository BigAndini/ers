<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Order\Model\Entity;

class Package extends Entity {
    /*
     * database fields
     */
    
    protected $id;
    protected $Order_id;
    protected $Participant_id;
    protected $Barcode_id;

    /*
     * other storage
     */
    protected $participant;
    protected $items;
    
    public function getParticipant() {
        return $this->participant;
    }
    public function setParticipant(Entity\User $Participant) {
        $this->participant = $Participant;
    }
    
    public function getItems() {
        return $this->items;
    }
    public function setItems(array $Items) {
        foreach($Items as $i) {
            if(get_class($i) === 'Entity\Item') {
                $this->addItem($i);
            }
        }
    }
    public function addItem(Entity\Item $Item) {
        $this->items[] = $Item;
    }
    public function getItem($criteria) {
        foreach($this->items as $i) {
            if($i->name === $criteria) {
                return $i;
            }
        }
        return false;
    }
}
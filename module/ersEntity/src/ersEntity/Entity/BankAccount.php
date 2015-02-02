<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ersEntity\Entity;
use Doctrine\ORM\Mapping as ORM;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

/** 
 * @ORM\Entity 
 * @ORM\HasLifecycleCallbacks()
 */
class BankAccount {
    /**
    * @ORM\Id
    * @ORM\GeneratedValue(strategy="AUTO")
    * @ORM\Column(type="integer")
    */
    protected $id;
    
    /** @ORM\Column(type="string") */
    protected $name;
    
    /** @ORM\Column(type="string") */
    protected $bank;
    
    /** @ORM\Column(type="string") */
    protected $iban;
    
    /** @ORM\Column(type="string") */
    protected $bic;
    
    /** @ORM\Column(type="string") */
    protected $kto;
    
    /** @ORM\Column(type="string") */
    protected $blz;
    
    /** @ORM\Column(type="string") */
    protected $statement_format;
    
    /** @ORM\Column(type="datetime") */
    protected $updated;
    
    /** @ORM\Column(type="datetime") */
    protected $created;
    
    /**
     * @ORM\PrePersist
     */
    public function PrePersist()
    {
        if(!isset($this->created)) {
            $this->created = new \DateTime();
        }
        $this->updated = new \DateTime();
    }
    
    // other variables
    
    protected $inputFilter;
    
    public function exchangeArray($data)
    {
        foreach($data as $k => $v) {
            if(property_exists(get_class($this), $k)) {
                $this->$k = $v;
            } else {
                /*if($k == 'Product_id') {
                    error_log(get_class().': set Product_id to id');
                    $this->id = $v;
                    continue;
                }*/
                error_log('ERROR: I do not know what to do with '.$k.' ('.$v.')');
            }
        }
    }
    
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }   
    
    // getters/setters
    
    public function getId() {
        return $this->id;
    }
    public function setId($id) {
        $this->id = $id;
    }
    
    public function getName() {
        return $this->name;
    }
    public function setName($name) {
        $this->name = $name;
    }
    
    public function getBank() {
        return $this->bank;
    }
    public function setBank($bank) {
        $this->bank = $bank;
    }
    
    public function getIban() {
        return $this->iban;
    }
    public function setIban($iban) {
        $this->iban = $iban;
    }
    
    public function getBic() {
        return $this->bic;
    }
    public function setBic($bic) {
        $this->bic = $bic;
    }
    
    public function getBlz() {
        return $this->blz;
    }
    public function setBlz($blz) {
        $this->blz = $blz;
    }
    
    public function getKto() {
        return $this->kto;
    }
    public function setKto($kto) {
        $this->kto = $kto;
    }
    
    public function getStatementFormat() {
        return json_decode($this->statement_format);
    }
    public function setStatementFormat(array $format) {
        $this->statement_format = json_encode($format);
    }
}
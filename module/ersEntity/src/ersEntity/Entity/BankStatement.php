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
class BankStatement {
    /**
    * @ORM\Id
    * @ORM\GeneratedValue(strategy="AUTO")
    * @ORM\Column(type="integer")
    */
    protected $id;

    /** @ORM\Column(type="integer") */
    protected $BankAccount_id;
    
    /** @ORM\Column(type="string", length=32) */
    protected $hash;
    
    /** @ORM\Column(type="string", length=128) */
    protected $StatementCol1;

    /** @ORM\Column(type="string", length=128) */
    protected $StatementCol2;
    
    /** @ORM\Column(type="string", length=128) */
    protected $StatementCol3;
    
    /** @ORM\Column(type="string", length=128) */
    protected $StatementCol4;
    
    /** @ORM\Column(type="string", length=128) */
    protected $StatementCol5;
    
    /** @ORM\Column(type="string", length=128) */
    protected $StatementCol6;
    
    /** @ORM\Column(type="string", length=128) */
    protected $StatementCol7;
    
    /** @ORM\Column(type="string", length=128) */
    protected $StatementCol8;
    
    /** @ORM\Column(type="string", length=128) */
    protected $StatementCol9;
    
    /** @ORM\Column(type="string", length=128) */
    protected $StatementCol10;
    
    /** @ORM\Column(type="string", length=128) */
    protected $StatementCol11;
    
    /** @ORM\Column(type="string", length=128) */
    protected $StatementCol12;
    
    /** @ORM\Column(type="string", length=128) */
    protected $StatementCol13;
    
    /** @ORM\Column(type="string", length=128) */
    protected $StatementCol14;
    
    /** @ORM\Column(type="string", length=128) */
    protected $StatementCol15;
    
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
    
    public function getStatement1() {
        return $this->StatementCol1;
    }
    public function setStatement1($Statement) {
        $this->StatementCol1 = $Statement;
    }
    
    public function getStatement2() {
        return $this->StatementCol2;
    }
    public function setStatement2($Statement) {
        $this->StatementCol2 = $Statement;
    }
    
    public function getStatement3() {
        return $this->StatementCol3;
    }
    public function setStatement3($Statement) {
        $this->StatementCol3 = $Statement;
    }
    
    public function getStatement4() {
        return $this->StatementCol4;
    }
    public function setStatement4($Statement) {
        $this->StatementCol4 = $Statement;
    }
    
    public function getStatement5() {
        return $this->StatementCol5;
    }
    public function setStatement5($Statement) {
        $this->StatementCol5 = $Statement;
    }
    
    public function getStatement6() {
        return $this->StatementCol6;
    }
    public function setStatement6($Statement) {
        $this->StatementCol6 = $Statement;
    }
    
    public function getStatement7() {
        return $this->StatementCol7;
    }
    public function setStatement7($Statement) {
        $this->StatementCol7 = $Statement;
    }
    
    public function getStatement8() {
        return $this->StatementCol8;
    }
    public function setStatement8($Statement) {
        $this->StatementCol8 = $Statement;
    }
    
    public function getStatement9() {
        return $this->StatementCol9;
    }
    public function setStatement9($Statement) {
        $this->StatementCol9 = $Statement;
    }
    
    public function getStatement10() {
        return $this->StatementCol10;
    }
    public function setStatement10($Statement) {
        $this->StatementCol10 = $Statement;
    }
    
    public function getStatement11() {
        return $this->StatementCol11;
    }
    public function setStatement11($Statement) {
        $this->StatementCol11 = $Statement;
    }
    
    public function getStatement12() {
        return $this->StatementCol12;
    }
    public function setStatement12($Statement) {
        $this->StatementCol12 = $Statement;
    }
    
    public function getStatement13() {
        return $this->StatementCol13;
    }
    public function setStatement13($Statement) {
        $this->StatementCol13 = $Statement;
    }
    
    public function getStatement14() {
        return $this->StatementCol14;
    }
    public function setStatement14($Statement) {
        $this->StatementCol14 = $Statement;
    }
    
    public function getStatement15() {
        return $this->StatementCol15;
    }
    public function setStatement15($Statement) {
        $this->StatementCol15 = $Statement;
    }
}
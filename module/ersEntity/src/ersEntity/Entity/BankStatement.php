<?php

/**
 * Auto generated by MySQL Workbench Schema Exporter.
 * Version 2.1.6-dev (doctrine2-zf2inputfilterannotation) on 2015-02-02
 * 21:38:10.
 * Goto https://github.com/johmue/mysql-workbench-schema-exporter for more
 * information.
 */

namespace ersEntity\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

/**
 * Entity\BankStatement
 *
 * @ORM\Entity()
 * @ORM\Table(name="BankStatement", indexes={@ORM\Index(name="fk_BankStatement_BankAccount1_idx", columns={"BankAccount_id"})})
 * @ORM\HasLifecycleCallbacks()
 */
class BankStatement implements InputFilterAwareInterface
{
    /**
     * Instance of InputFilterInterface.
     *
     * @var InputFilter
     */
    private $inputFilter;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="integer")
     */
    protected $BankAccount_id;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    protected $hash;
    
    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    protected $status;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $updated;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @ORM\OneToMany(targetEntity="Match", mappedBy="bankStatement")
     * @ORM\JoinColumn(name="id", referencedColumnName="BankStatement_id")
     */
    protected $matches;

    /**
     * @ORM\ManyToOne(targetEntity="BankAccount", inversedBy="bankStatements")
     * @ORM\JoinColumn(name="BankAccount_id", referencedColumnName="id")
     */
    protected $bankAccount;

    /**
     * @ORM\OneToMany(targetEntity="BankStatementCol", mappedBy="bankStatement", cascade={"persist"})
     * @ORM\JoinColumn(name="id", referencedColumnName="BankStatement_id")
     */
    protected $bankStatementCols;
    
    public function __construct()
    {
        $this->matches = new ArrayCollection();
    }
    
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
    
    /**
     * @ORM\PreUpdate
     */
    public function PreUpdate()
    {
        $this->updated = new \DateTime();
    }
    
    /**
     * Set id of this object to null if it's cloned
     */
    public function __clone() {
        $this->id = null;
    }

    /**
     * Set the value of id.
     *
     * @param integer $id
     * @return \Entity\BankStatement
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of id.
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of BankAccount_id.
     *
     * @param integer $BankAccount_id
     * @return \Entity\BankStatement
     */
    public function setBankAccountId($BankAccount_id)
    {
        $this->BankAccount_id = $BankAccount_id;

        return $this;
    }

    /**
     * Get the value of BankAccount_id.
     *
     * @return integer
     */
    public function getBankAccountId()
    {
        return $this->BankAccount_id;
    }

    /**
     * Set the value of status.
     *
     * @param string $status
     * @return \Entity\BankStatement
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get the value of status.
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }
    
    /**
     * Set the value of hash.
     *
     * @param string $hash
     * @return \Entity\BankStatement
     */
    public function setHash($hash)
    {
        $this->hash = $hash;

        return $this;
    }

    /**
     * Get the value of hash.
     *
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }
    
    /**
     * Set the value of updated.
     *
     * @param datetime $updated
     * @return \Entity\BankStatement
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get the value of updated.
     *
     * @return datetime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set the value of created.
     *
     * @param datetime $created
     * @return \Entity\BankStatement
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get the value of created.
     *
     * @return datetime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Add Match entity to collection (one to many).
     *
     * @param \Entity\Match $match
     * @return \Entity\BankStatement
     */
    public function addMatch(Match $match)
    {
        $this->matches[] = $match;

        return $this;
    }

    /**
     * Remove Match entity from collection (one to many).
     *
     * @param \Entity\Match $match
     * @return \Entity\BankStatement
     */
    public function removeMatch(Match $match)
    {
        $this->matches->removeElement($match);

        return $this;
    }

    /**
     * Get Match entity collection (one to many).
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMatches()
    {
        return $this->matches;
    }

    /**
     * Add BankStatementCol entity to collection (one to many).
     *
     * @param \Entity\BankStatementCol $bankStatementCol
     * @return \Entity\BankStatement
     */
    public function addBankStatementCol(BankStatementCol $bankStatementCol)
    {
        $this->bankStatementCols[] = $bankStatementCol;

        return $this;
    }

    /**
     * Remove BankStatementCol entity from collection (one to many).
     *
     * @param \Entity\BankStatementCol $bankStatementCol
     * @return \Entity\BankStatement
     */
    public function removeBankStatementCol(BankStatementCol $bankStatementCol)
    {
        $this->bankStatementCols->removeElement($bankStatementCol);

        return $this;
    }

    /**
     * Get BankStatementCol entity collection (one to many).
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBankStatementCols()
    {
        return $this->bankStatementCols;
    }
    
    /**
     * Get BankStatementCol entity by column number
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBankStatementColByNumber($num)
    {
        foreach($this->getBankStatementCols() as $col) {
            if($col->getColumn() == $num) {
                return $col;
            }
        }
        return false;
    }
    
    /**
     * get the amount of this statement according to the format
     */
    public function getAmount() {
        $statement_format = json_decode($this->getBankAccount()->getStatementFormat());
        return $this->getBankStatementColByNumber($statement_format->amount);
    }
    
    /**
     * Get the name of this statement according to the format
     */
    public function getName() {
        $statement_format = json_decode($this->getBankAccount()->getStatementFormat());
        return $this->getBankStatementColByNumber($statement_format->name);
    }
    /**
     * Get the code of this statement according to the format
     */
    public function getCode() {
        $statement_format = json_decode($this->getBankAccount()->getStatementFormat());
        return $this->getBankStatementColByNumber($statement_format->matchKey);
    }
    /**
     * Get the date of this statement according to the format
     */
    public function getDate() {
        $statement_format = json_decode($this->getBankAccount()->getStatementFormat());
        $datestring = $this->getBankStatementColByNumber($statement_format->date)->getValue();
        $timestamp = strtotime($datestring);
        error_log('timestamp: '.$timestamp);
        if($timestamp != false) {
            $date = new \DateTime();
            $date->setTimestamp($timestamp);
            return $date;
        } else {
            error_log('unable to convert this to time: '.$datestring);
            return false;
        }
        
    }
    
    /**
     * Set BankAccount entity (many to one).
     *
     * @param \Entity\BankAccount $bankAccount
     * @return \Entity\BankStatement
     */
    public function setBankAccount(BankAccount $bankAccount = null)
    {
        $this->bankAccount = $bankAccount;

        return $this;
    }

    /**
     * Get BankAccount entity (many to one).
     *
     * @return \Entity\BankAccount
     */
    public function getBankAccount()
    {
        return $this->bankAccount;
    }

    /**
     * Not used, Only defined to be compatible with InputFilterAwareInterface.
     * 
     * @param \Zend\InputFilter\InputFilterInterface $inputFilter
     * @throws \Exception
     */
    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used.");
    }

    /**
     * Return a for this entity configured input filter instance.
     *
     * @return InputFilterInterface
     */
    public function getInputFilter()
    {
        if ($this->inputFilter instanceof InputFilterInterface) {
            return $this->inputFilter;
        }
        $factory = new InputFactory();
        $filters = array(
            array(
                'name' => 'id',
                'required' => true,
                'filters' => array(),
                'validators' => array(),
            ),
            array(
                'name' => 'BankAccount_id',
                'required' => true,
                'filters' => array(),
                'validators' => array(),
            ),
            array(
                'name' => 'hash',
                'required' => false,
                'filters' => array(),
                'validators' => array(),
            ),
            array(
                'name' => 'updated',
                'required' => false,
                'filters' => array(),
                'validators' => array(),
            ),
            array(
                'name' => 'created',
                'required' => false,
                'filters' => array(),
                'validators' => array(),
            ),
        );
        $this->inputFilter = $factory->createInputFilter($filters);

        return $this->inputFilter;
    }

    /**
     * Populate entity with the given data.
     * The set* method will be used to set the data.
     *
     * @param array $data
     * @return boolean
     */
    public function populate(array $data = array())
    {
        foreach ($data as $field => $value) {
            $setter = sprintf('set%s', ucfirst(
                str_replace(' ', '', ucwords(str_replace('_', ' ', $field)))
            ));
            if (method_exists($this, $setter)) {
                $this->{$setter}($value);
            }
        }

        return true;
    }

    /**
     * Return a array with all fields and data.
     * Default the relations will be ignored.
     * 
     * @param array $fields
     * @return array
     */
    public function getArrayCopy(array $fields = array())
    {
        $dataFields = array('id', 'BankAccount_id', 'hash', 'updated', 'created');
        $relationFields = array('bankAccount');
        $copiedFields = array();
        foreach ($relationFields as $relationField) {
            $map = null;
            if (array_key_exists($relationField, $fields)) {
                $map = $fields[$relationField];
                $fields[] = $relationField;
                unset($fields[$relationField]);
            }
            if (!in_array($relationField, $fields)) {
                continue;
            }
            $getter = sprintf('get%s', ucfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $relationField)))));
            $relationEntity = $this->{$getter}();
            $copiedFields[$relationField] = (!is_null($map))
                ? $relationEntity->getArrayCopy($map)
                : $relationEntity->getArrayCopy();
            $fields = array_diff($fields, array($relationField));
        }
        foreach ($dataFields as $dataField) {
            if (!in_array($dataField, $fields) && !empty($fields)) {
                continue;
            }
            $getter = sprintf('get%s', ucfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $dataField)))));
            $copiedFields[$dataField] = $this->{$getter}();
        }

        return $copiedFields;
    }

    public function __sleep()
    {
        return array('id', 'BankAccount_id', 'hash', 'updated', 'created');
    }
}
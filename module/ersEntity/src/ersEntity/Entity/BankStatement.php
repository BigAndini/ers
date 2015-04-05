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
     * @ORM\Column(type="text", nullable=true)
     */
    protected $hash;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $BankStatementcol1;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $BankStatementcol2;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $BankStatementcol3;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $BankStatementcol4;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $BankStatementcol5;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $BankStatementcol6;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $BankStatementcol7;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $BankStatementcol8;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $BankStatementcol9;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $BankStatementcol10;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $BankStatementcol11;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $BankStatementcol12;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $BankStatementcol13;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $BankStatementcol14;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $BankStatementcol15;

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
     * Set the value of BankStatementcol1.
     *
     * @param string $BankStatementcol1
     * @return \Entity\BankStatement
     */
    public function setBankStatementcol1($BankStatementcol1)
    {
        $this->BankStatementcol1 = $BankStatementcol1;

        return $this;
    }

    /**
     * Get the value of BankStatementcol1.
     *
     * @return string
     */
    public function getBankStatementcol1()
    {
        return $this->BankStatementcol1;
    }

    /**
     * Set the value of BankStatementcol2.
     *
     * @param string $BankStatementcol2
     * @return \Entity\BankStatement
     */
    public function setBankStatementcol2($BankStatementcol2)
    {
        $this->BankStatementcol2 = $BankStatementcol2;

        return $this;
    }

    /**
     * Get the value of BankStatementcol2.
     *
     * @return string
     */
    public function getBankStatementcol2()
    {
        return $this->BankStatementcol2;
    }

    /**
     * Set the value of BankStatementcol3.
     *
     * @param string $BankStatementcol3
     * @return \Entity\BankStatement
     */
    public function setBankStatementcol3($BankStatementcol3)
    {
        $this->BankStatementcol3 = $BankStatementcol3;

        return $this;
    }

    /**
     * Get the value of BankStatementcol3.
     *
     * @return string
     */
    public function getBankStatementcol3()
    {
        return $this->BankStatementcol3;
    }

    /**
     * Set the value of BankStatementcol4.
     *
     * @param string $BankStatementcol4
     * @return \Entity\BankStatement
     */
    public function setBankStatementcol4($BankStatementcol4)
    {
        $this->BankStatementcol4 = $BankStatementcol4;

        return $this;
    }

    /**
     * Get the value of BankStatementcol4.
     *
     * @return string
     */
    public function getBankStatementcol4()
    {
        return $this->BankStatementcol4;
    }

    /**
     * Set the value of BankStatementcol5.
     *
     * @param string $BankStatementcol5
     * @return \Entity\BankStatement
     */
    public function setBankStatementcol5($BankStatementcol5)
    {
        $this->BankStatementcol5 = $BankStatementcol5;

        return $this;
    }

    /**
     * Get the value of BankStatementcol5.
     *
     * @return string
     */
    public function getBankStatementcol5()
    {
        return $this->BankStatementcol5;
    }

    /**
     * Set the value of BankStatementcol6.
     *
     * @param string $BankStatementcol6
     * @return \Entity\BankStatement
     */
    public function setBankStatementcol6($BankStatementcol6)
    {
        $this->BankStatementcol6 = $BankStatementcol6;

        return $this;
    }

    /**
     * Get the value of BankStatementcol6.
     *
     * @return string
     */
    public function getBankStatementcol6()
    {
        return $this->BankStatementcol6;
    }

    /**
     * Set the value of BankStatementcol7.
     *
     * @param string $BankStatementcol7
     * @return \Entity\BankStatement
     */
    public function setBankStatementcol7($BankStatementcol7)
    {
        $this->BankStatementcol7 = $BankStatementcol7;

        return $this;
    }

    /**
     * Get the value of BankStatementcol7.
     *
     * @return string
     */
    public function getBankStatementcol7()
    {
        return $this->BankStatementcol7;
    }

    /**
     * Set the value of BankStatementcol8.
     *
     * @param string $BankStatementcol8
     * @return \Entity\BankStatement
     */
    public function setBankStatementcol8($BankStatementcol8)
    {
        $this->BankStatementcol8 = $BankStatementcol8;

        return $this;
    }

    /**
     * Get the value of BankStatementcol8.
     *
     * @return string
     */
    public function getBankStatementcol8()
    {
        return $this->BankStatementcol8;
    }

    /**
     * Set the value of BankStatementcol9.
     *
     * @param string $BankStatementcol9
     * @return \Entity\BankStatement
     */
    public function setBankStatementcol9($BankStatementcol9)
    {
        $this->BankStatementcol9 = $BankStatementcol9;

        return $this;
    }

    /**
     * Get the value of BankStatementcol9.
     *
     * @return string
     */
    public function getBankStatementcol9()
    {
        return $this->BankStatementcol9;
    }

    /**
     * Set the value of BankStatementcol10.
     *
     * @param string $BankStatementcol10
     * @return \Entity\BankStatement
     */
    public function setBankStatementcol10($BankStatementcol10)
    {
        $this->BankStatementcol10 = $BankStatementcol10;

        return $this;
    }

    /**
     * Get the value of BankStatementcol10.
     *
     * @return string
     */
    public function getBankStatementcol10()
    {
        return $this->BankStatementcol10;
    }

    /**
     * Set the value of BankStatementcol11.
     *
     * @param string $BankStatementcol11
     * @return \Entity\BankStatement
     */
    public function setBankStatementcol11($BankStatementcol11)
    {
        $this->BankStatementcol11 = $BankStatementcol11;

        return $this;
    }

    /**
     * Get the value of BankStatementcol11.
     *
     * @return string
     */
    public function getBankStatementcol11()
    {
        return $this->BankStatementcol11;
    }

    /**
     * Set the value of BankStatementcol12.
     *
     * @param string $BankStatementcol12
     * @return \Entity\BankStatement
     */
    public function setBankStatementcol12($BankStatementcol12)
    {
        $this->BankStatementcol12 = $BankStatementcol12;

        return $this;
    }

    /**
     * Get the value of BankStatementcol12.
     *
     * @return string
     */
    public function getBankStatementcol12()
    {
        return $this->BankStatementcol12;
    }

    /**
     * Set the value of BankStatementcol13.
     *
     * @param string $BankStatementcol13
     * @return \Entity\BankStatement
     */
    public function setBankStatementcol13($BankStatementcol13)
    {
        $this->BankStatementcol13 = $BankStatementcol13;

        return $this;
    }

    /**
     * Get the value of BankStatementcol13.
     *
     * @return string
     */
    public function getBankStatementcol13()
    {
        return $this->BankStatementcol13;
    }

    /**
     * Set the value of BankStatementcol14.
     *
     * @param string $BankStatementcol14
     * @return \Entity\BankStatement
     */
    public function setBankStatementcol14($BankStatementcol14)
    {
        $this->BankStatementcol14 = $BankStatementcol14;

        return $this;
    }

    /**
     * Get the value of BankStatementcol14.
     *
     * @return string
     */
    public function getBankStatementcol14()
    {
        return $this->BankStatementcol14;
    }

    /**
     * Set the value of BankStatementcol15.
     *
     * @param string $BankStatementcol15
     * @return \Entity\BankStatement
     */
    public function setBankStatementcol15($BankStatementcol15)
    {
        $this->BankStatementcol15 = $BankStatementcol15;

        return $this;
    }

    /**
     * Get the value of BankStatementcol15.
     *
     * @return string
     */
    public function getBankStatementcol15()
    {
        return $this->BankStatementcol15;
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
                'name' => 'BankStatementcol1',
                'required' => false,
                'filters' => array(),
                'validators' => array(),
            ),
            array(
                'name' => 'BankStatementcol2',
                'required' => false,
                'filters' => array(),
                'validators' => array(),
            ),
            array(
                'name' => 'BankStatementcol3',
                'required' => false,
                'filters' => array(),
                'validators' => array(),
            ),
            array(
                'name' => 'BankStatementcol4',
                'required' => false,
                'filters' => array(),
                'validators' => array(),
            ),
            array(
                'name' => 'BankStatementcol5',
                'required' => false,
                'filters' => array(),
                'validators' => array(),
            ),
            array(
                'name' => 'BankStatementcol6',
                'required' => false,
                'filters' => array(),
                'validators' => array(),
            ),
            array(
                'name' => 'BankStatementcol7',
                'required' => false,
                'filters' => array(),
                'validators' => array(),
            ),
            array(
                'name' => 'BankStatementcol8',
                'required' => false,
                'filters' => array(),
                'validators' => array(),
            ),
            array(
                'name' => 'BankStatementcol9',
                'required' => false,
                'filters' => array(),
                'validators' => array(),
            ),
            array(
                'name' => 'BankStatementcol10',
                'required' => false,
                'filters' => array(),
                'validators' => array(),
            ),
            array(
                'name' => 'BankStatementcol11',
                'required' => false,
                'filters' => array(),
                'validators' => array(),
            ),
            array(
                'name' => 'BankStatementcol12',
                'required' => false,
                'filters' => array(),
                'validators' => array(),
            ),
            array(
                'name' => 'BankStatementcol13',
                'required' => false,
                'filters' => array(),
                'validators' => array(),
            ),
            array(
                'name' => 'BankStatementcol14',
                'required' => false,
                'filters' => array(),
                'validators' => array(),
            ),
            array(
                'name' => 'BankStatementcol15',
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
        $dataFields = array('id', 'BankAccount_id', 'hash', 'BankStatementcol1', 'BankStatementcol2', 'BankStatementcol3', 'BankStatementcol4', 'BankStatementcol5', 'BankStatementcol6', 'BankStatementcol7', 'BankStatementcol8', 'BankStatementcol9', 'BankStatementcol10', 'BankStatementcol11', 'BankStatementcol12', 'BankStatementcol13', 'BankStatementcol14', 'BankStatementcol15', 'updated', 'created');
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
        return array('id', 'BankAccount_id', 'hash', 'BankStatementcol1', 'BankStatementcol2', 'BankStatementcol3', 'BankStatementcol4', 'BankStatementcol5', 'BankStatementcol6', 'BankStatementcol7', 'BankStatementcol8', 'BankStatementcol9', 'BankStatementcol10', 'BankStatementcol11', 'BankStatementcol12', 'BankStatementcol13', 'BankStatementcol14', 'BankStatementcol15', 'updated', 'created');
    }
}
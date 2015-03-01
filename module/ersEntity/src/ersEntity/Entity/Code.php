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
 * Entity\Code
 *
 * @ORM\Entity()
 * @ORM\Table(name="Code")
 * @ORM\HasLifecycleCallbacks()
 */
class Code implements InputFilterAwareInterface
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
     * @ORM\Column(type="string", unique=true, length=45, nullable=true)
     */
    protected $value;
    
    /**
     * @ORM\Column(type="datetime")
     */
    protected $updated;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @ORM\OneToOne(targetEntity="Item", mappedBy="code")
     */
    protected $item;

    /**
     * @ORM\OneToMany(targetEntity="Order", mappedBy="code")
     * @ORM\JoinColumn(name="id", referencedColumnName="Code_id")
     */
    protected $orders;

    /**
     * @ORM\OneToOne(targetEntity="Package", mappedBy="code")
     */
    protected $package;
    
    protected $length;

    public function __construct()
    {
        $this->items = new ArrayCollection();
        $this->orders = new ArrayCollection();
        $this->packages = new ArrayCollection();
        
        $this->length = 6;
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
     * Set id of this object to null if it's cloned
     */
    public function __clone() {
        $this->id = null;
    }

    /**
     * Set the value of id.
     *
     * @param integer $id
     * @return \Entity\Code
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
     * Set the value of value.
     *
     * @param string $value
     * @return \Entity\Code
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get the value of value.
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }
    
    public function genCode() {
        /*
         * Alphabet for codes:
         * 0 <- O Q
         * 1 <- I J L
         * 2 <- Z
         * 3 <- E
         * 4
         * 5 <- S
         * 6 <- G
         * 7 <- T
         * 8 <- B
         * 9
         * A
         * C
         * D
         * F
         * H
         * K
         * M
         * N
         * P
         * R
         * U
         * V
         * W
         * X
         * Y
         */
        $alphabet = "0123456789ACDFGHKMNPRUVWXY";
        $memory = '';
        $n = '';
        #srand(mktime()); 
        srand(rand()*mktime());
        for ($i = 0; $i < $this->length; $i++) {
            
            while($n == '' || $memory == $alphabet[$n]) {
                $n = rand(0, strlen($alphabet)-1);
            }
            $memory = $alphabet[$n];
            $code[$i] = $alphabet[$n];
        }
        
        $this->code = implode($code).$this->genChecksum(implode($code));
        
        return $this;
    }
    private function genChecksum($code) {
        $chars = str_split($code);
        $nums = array();
        foreach($chars as $char) {
            $nums[] = ord($char);
        }
        $cross_sum = array_sum($nums);
        $checksum = $cross_sum % 100;
        return sprintf('%02d', $checksum);
    }
    
    /**
     * Check if code checksum is valid
     * 
     * @return boolean
     */
    public function checkCode() {
        $checksum = substr($this->getValue(),$this->length);
        $code = substr($this->getValue(),0,$this->length);
        if($this->genChecksum($code) == $checksum) {
            return true;
        } else {
            return false;
        }
    }

    public function normalizeText($text) {
        $text = strtoupper($text);
        $matrix = array(
            '0' => array(
                'O',
                'Q',
            ),
            '1' => array(
                'I',
                'J',
                'L',
            ),
            '2' => array(
                'Z',
            ),
            '3' => array(
                'E',
            ),
            '5' => array(
                'S',
            ),
            '6' => array(
                'G',
            ),
            '7' => array(
                'T',
            ),
            '8' => array(
                'B',
            ),
        );
        $pattern = array();
        $replace = array();
        foreach($matrix as $key => $values) {
            foreach($values as $value) {
                $pattern[] = '/'.$value.'/';
                $replace[] = $key;
            }
        }
        return preg_replace($pattern, $replace, $text);
    }

    /**
     * Set Item entity (one to one).
     *
     * @param \Entity\Item $item
     * @return \Entity\Code
     */
    public function setItem(Item $item = null)
    {
        $item->setCode($this);
        $this->item = $item;

        return $this;
    }

    /**
     * Get Item entity (one to one).
     *
     * @return \Entity\Item
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * Add Order entity to collection (one to many).
     *
     * @param \Entity\Order $order
     * @return \Entity\Code
     */
    public function addOrder(Order $order)
    {
        $this->orders[] = $order;

        return $this;
    }

    /**
     * Remove Order entity from collection (one to many).
     *
     * @param \Entity\Order $order
     * @return \Entity\Code
     */
    public function removeOrder(Order $order)
    {
        $this->orders->removeElement($order);

        return $this;
    }

    /**
     * Get Order entity collection (one to many).
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOrders()
    {
        return $this->orders;
    }

    /**
     * Set Package entity (one to one).
     *
     * @param \Entity\Package $package
     * @return \Entity\Code
     */
    public function setPackage(Package $package = null)
    {
        $package->setCode($this);
        $this->package = $package;

        return $this;
    }

    /**
     * Get Package entity (one to one).
     *
     * @return \Entity\Package
     */
    public function getPackage()
    {
        return $this->package;
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
                'name' => 'value',
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
        $dataFields = array('id', 'value');
        $relationFields = array();
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
        return array('id', 'value');
    }
}
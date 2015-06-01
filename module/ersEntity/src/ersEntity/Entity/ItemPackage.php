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
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

/**
 * Entity\ItemPackage
 *
 * @ORM\Entity()
 * @ORM\Table(name="ItemPackage", indexes={
 *  @ORM\Index(name="fk_ItemPackage_Item1_idx", columns={"SurItem_id"}), 
 *  @ORM\Index(name="fk_ItemPackage_Item2_idx", columns={"SubItem_id"})
 * })
 * @ORM\HasLifecycleCallbacks()
 */
class ItemPackage implements InputFilterAwareInterface
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
    protected $SurItem_id;

    /**
     * @ORM\Column(type="integer")
     */
    protected $SubItem_id;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $amount = 1;
    
    /**
     * @ORM\Column(type="datetime")
     */
    protected $updated;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created;
    
    /**
     * @ORM\ManyToOne(targetEntity="Item", inversedBy="itemPackageRelatedBySurItemIds")
     * @ORM\JoinColumn(name="SurItem_id", referencedColumnName="id")
     */
    protected $surItem;

    /**
     * @ORM\ManyToOne(targetEntity="Item", inversedBy="itemPackageRelatedBySubItemIds", cascade={"persist"})
     * @ORM\JoinColumn(name="SubItem_id", referencedColumnName="id")
     */
    protected $subItem;


    public function __construct()
    {
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
     * @return \Entity\ItemPackage
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
     * Set the value of SurItem_id.
     *
     * @param integer $SurItem_id
     * @return \Entity\ItemPackage
     */
    public function setSurItemId($SurItem_id)
    {
        $this->SurItem_id = $SurItem_id;

        return $this;
    }

    /**
     * Get the value of SurItem_id.
     *
     * @return integer
     */
    public function getItemId()
    {
        return $this->SurItem_id;
    }

    /**
     * Set the value of SubItem_id.
     *
     * @param integer $SubItem_id
     * @return \Entity\ItemPackage
     */
    public function setSubItemId($SubItem_id)
    {
        $this->SubItem_id = $SubItem_id;

        return $this;
    }

    /**
     * Get the value of SubItem_id.
     *
     * @return integer
     */
    public function getSubItemId()
    {
        return $this->SubItem_id;
    }

    /**
     * Set the value of amount.
     *
     * @param boolean $amount
     * @return \Entity\ItemPackage
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get the value of active.
     *
     * @return boolean
     */
    public function getAmount()
    {
        return $this->amount;
    }
    
    /**
     * Set Item entity related by `SurItem_id` (many to one).
     *
     * @param \Entity\Item $item
     * @return \Entity\ItemPackage
     */
    public function setSurItem(Item $item = null)
    {
        $this->surItem = $item;

        return $this;
    }

    /**
     * Get Item entity related by `SurItem_id` (many to one).
     *
     * @return \Entity\Item
     */
    public function getSurItem()
    {
        return $this->surItem;
    }

    /**
     * Set Item entity related by `SubItem_id` (many to one).
     *
     * @param \Entity\Item $item
     * @return \Entity\ItemPackage
     */
    public function setSubItem(Item $item = null)
    {
        $this->subItem = $item;

        return $this;
    }

    /**
     * Get Item entity related by `SubItem_id` (many to one).
     *
     * @return \Entity\Item
     */
    public function getSubItem()
    {
        return $this->subItem;
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
                'required' => false,
                'filters' => array(),
                'validators' => array(),
            ),
            array(
                'name' => 'SurItem_id',
                'required' => true,
                'filters' => array(),
                'validators' => array(),
            ),
            array(
                'name' => 'SubItem_id',
                'required' => true,
                'filters' => array(),
                'validators' => array(),
            ),
            array(
                'name' => 'amount',
                'required' => true,
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
        $dataFields = array('id', 'SurItem_id', 'SubItem_id', 'surItem', 'subItem', 'amount', 'updated', 'created');
        $relationFields = array('item', 'item');
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
        return array('id', 'SurItem_id', 'SubItem_id', 'surItem', 'subItem', 'amount', 'updated', 'created');
    }
}
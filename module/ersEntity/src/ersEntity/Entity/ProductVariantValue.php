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
 * Entity\ProductVariantValue
 *
 * @ORM\Entity()
 * @ORM\Table(name="ProductVariantValue", indexes={@ORM\Index(name="fk_ProductVariantValue_ProductVariant1_idx", columns={"ProductVariant_id"})})
 * @ORM\HasLifecycleCallbacks()
 */
class ProductVariantValue implements InputFilterAwareInterface
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
    protected $ProductVariant_id;

    /**
     * @ORM\Column(name="`order`", type="integer", nullable=true)
     */
    protected $order;

    /**
     * @ORM\Column(name="`value`", type="string", length=45, nullable=true)
     */
    protected $value;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $updated;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $created;

    /**
     * @ORM\OneToMany(targetEntity="Counter", mappedBy="productVariantValue")
     * @ORM\JoinColumn(name="id", referencedColumnName="ProductVariantValue_id")
     */
    protected $counters;

    /**
     * @ORM\ManyToOne(targetEntity="ProductVariant", inversedBy="productVariantValues")
     * @ORM\JoinColumn(name="ProductVariant_id", referencedColumnName="id")
     */
    protected $productVariant;

    public function __construct()
    {
        $this->counters = new ArrayCollection();
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
     * Set the value of id.
     *
     * @param integer $id
     * @return \Entity\ProductVariantValue
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
     * Set the value of ProductVariant_id.
     *
     * @param integer $ProductVariant_id
     * @return \Entity\ProductVariantValue
     */
    public function setProductVariantId($ProductVariant_id)
    {
        $this->ProductVariant_id = $ProductVariant_id;

        return $this;
    }

    /**
     * Get the value of ProductVariant_id.
     *
     * @return integer
     */
    public function getProductVariantId()
    {
        return $this->ProductVariant_id;
    }

    /**
     * Set the value of order.
     *
     * @param integer $order
     * @return \Entity\ProductVariantValue
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Get the value of order.
     *
     * @return integer
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Set the value of value.
     *
     * @param string $value
     * @return \Entity\ProductVariantValue
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

    /**
     * Set the value of updated.
     *
     * @param datetime $updated
     * @return \Entity\ProductVariantValue
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
     * @return \Entity\ProductVariantValue
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
     * Add Counter entity to collection (one to many).
     *
     * @param \Entity\Counter $counter
     * @return \Entity\ProductVariantValue
     */
    public function addCounter(Counter $counter)
    {
        $this->counters[] = $counter;

        return $this;
    }

    /**
     * Remove Counter entity from collection (one to many).
     *
     * @param \Entity\Counter $counter
     * @return \Entity\ProductVariantValue
     */
    public function removeCounter(Counter $counter)
    {
        $this->counters->removeElement($counter);

        return $this;
    }

    /**
     * Get Counter entity collection (one to many).
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCounters()
    {
        return $this->counters;
    }

    /**
     * Set ProductVariant entity (many to one).
     *
     * @param \Entity\ProductVariant $productVariant
     * @return \Entity\ProductVariantValue
     */
    public function setProductVariant(ProductVariant $productVariant = null)
    {
        $this->productVariant = $productVariant;

        return $this;
    }

    /**
     * Get ProductVariant entity (many to one).
     *
     * @return \Entity\ProductVariant
     */
    public function getProductVariant()
    {
        return $this->productVariant;
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
                'name' => 'ProductVariant_id',
                'required' => true,
                'filters' => array(),
                'validators' => array(),
            ),
            array(
                'name' => 'order',
                'required' => false,
                'filters' => array(),
                'validators' => array(),
            ),
            array(
                'name' => 'value',
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
        $dataFields = array('id', 'ProductVariant_id', 'order', 'value', 'updated', 'created');
        $relationFields = array('productVariant');
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
        return array('id', 'ProductVariant_id', 'order', 'value', 'updated', 'created');
    }
}
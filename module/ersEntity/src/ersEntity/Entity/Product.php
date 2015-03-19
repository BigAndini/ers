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
 * Entity\Product
 *
 * @ORM\Entity()
 * @ORM\Table(name="Product", indexes={@ORM\Index(name="fk_Product_Tax1_idx", columns={"taxId"})})
 * @ORM\HasLifecycleCallbacks()
 */
class Product implements InputFilterAwareInterface
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
    protected $taxId;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $ordering;

    /**
     * @ORM\Column(type="string", length=45)
     */
    protected $displayName;

    /**
     * @ORM\Column(type="string", length=128)
     */
    protected $shortDescription;

    /**
     * @ORM\Column(type="string", length=1000, nullable=true)
     */
    protected $longDescription;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $active;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $visible;
    
    /**
     * @ORM\Column(type="boolean")
     */
    protected $deleted;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $personalized;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $updated;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @ORM\OneToMany(targetEntity="Counter", mappedBy="product")
     * @ORM\JoinColumn(name="id", referencedColumnName="Product_id")
     */
    protected $counters;

    /**
     * @ORM\OneToMany(targetEntity="Item", mappedBy="product")
     * @ORM\JoinColumn(name="id", referencedColumnName="Product_id")
     */
    protected $items;

    /**
     * @ORM\OneToMany(targetEntity="ProductPackage", mappedBy="product")
     * @ORM\JoinColumn(name="id", referencedColumnName="Product_id")
     */
    protected $childProducts;

    /**
     * @ORM\OneToMany(targetEntity="ProductPackage", mappedBy="subproduct")
     * @ORM\JoinColumn(name="id", referencedColumnName="SubProduct_id")
     */
    protected $parentProducts;

    /**
     * @ORM\OneToMany(targetEntity="ProductPrice", mappedBy="product", cascade={"persist"})
     * @ORM\JoinColumn(name="id", referencedColumnName="Product_id")
     */
    protected $productPrices;

    /**
     * @ORM\OneToMany(targetEntity="ProductVariant", mappedBy="product", cascade={"persist"})
     * @ORM\JoinColumn(name="id", referencedColumnName="Product_id")
     * @ORM\OrderBy({"ordering" = "ASC"})
     */
    protected $productVariants;

    /**
     * @ORM\ManyToOne(targetEntity="Tax", inversedBy="products")
     * @ORM\JoinColumn(name="taxId", referencedColumnName="id")
     */
    protected $tax;

    public function __construct()
    {
        $this->counters = new ArrayCollection();
        $this->items = new ArrayCollection();
        $this->childProducts = new ArrayCollection();
        $this->parentProducts = new ArrayCollection();
        $this->productPrices = new ArrayCollection();
        $this->productVariants = new ArrayCollection();
        $this->setActive(1);
        $this->setDeleted(0);
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
        
        $prices = $this->getProductPrices();
        $this->productPrices = new ArrayCollection();
        foreach ($prices as $price) {
            $clonePrice = clone $price;
            $this->addProductPrice($clonePrice);
            $clonePrice->setProduct($this);
        }
        
        $variants = $this->getProductVariants();
        $this->productVariants = new ArrayCollection();
        foreach ($variants as $variant) {
            $cloneVariant = clone $variant;
            $this->addProductVariant($cloneVariant);
            $cloneVariant->setProduct($this);
        }
    }
    
    /**
     * Set the value of id.
     *
     * @param integer $id
     * @return \Entity\Product
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
     * Set the value of taxId.
     *
     * @param integer $taxId
     * @return \Entity\Product
     */
    public function setTaxId($taxId)
    {
        $this->taxId = $taxId;

        return $this;
    }

    /**
     * Get the value of taxId.
     *
     * @return integer
     */
    public function getTaxId()
    {
        return $this->taxId;
    }

    /**
     * Set the value of order.
     *
     * @param integer $order
     * @return \Entity\Product
     */
    public function setOrder($order)
    {
        $this->ordering = $order;

        return $this;
    }
    public function setOrdering($order)
    {
        return $this->setOrder($order);
    }

    /**
     * Get the value of order.
     *
     * @return integer
     */
    public function getOrder()
    {
        return $this->ordering;
    }
    public function getOrdering()
    {
        return $this->getOrder();
    }
    
    /**
     * Set the value of name.
     *
     * @param string $name
     * @return \Entity\Product
     */
    public function setName($name)
    {
        $this->displayName = $name;

        return $this;
    }

    /**
     * Get the value of name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->displayName;
    }

    /**
     * Set the value of shortDescription.
     *
     * @param string $shortDescription
     * @return \Entity\Product
     */
    public function setShortDescription($shortDescription)
    {
        $this->shortDescription = $shortDescription;

        return $this;
    }

    /**
     * Get the value of shortDescription.
     *
     * @return string
     */
    public function getShortDescription()
    {
        return $this->shortDescription;
    }

    /**
     * Set the value of longDescription.
     *
     * @param string $longDescription
     * @return \Entity\Product
     */
    public function setLongDescription($longDescription)
    {
        $this->longDescription = $longDescription;

        return $this;
    }

    /**
     * Get the value of longDescription.
     *
     * @return string
     */
    public function getLongDescription()
    {
        return $this->longDescription;
    }

    /**
     * Set the value of active.
     *
     * @param boolean $active
     * @return \Entity\Product
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get the value of active.
     *
     * @return boolean
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set the value of visible.
     *
     * @param boolean $visible
     * @return \Entity\Product
     */
    public function setVisible($visible)
    {
        $this->visible = $visible;

        return $this;
    }

    /**
     * Get the value of visible.
     *
     * @return boolean
     */
    public function getVisible()
    {
        return $this->visible;
    }
    
    /**
     * Set the value of deleted.
     *
     * @param boolean $deleted
     * @return \Entity\Product
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * Get the value of deleted.
     *
     * @return boolean
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * Set the value of personalized.
     *
     * @param boolean $personalized
     * @return \Entity\Product
     */
    public function setPersonalized($personalized)
    {
        $this->personalized = $personalized;
        
        return $this;
    }

    /**
     * Get the value of personalized.
     *
     * @return boolean
     */
    public function getPersonalized()
    {
        return $this->personalized;
    }

    /**
     * Set the value of updated.
     *
     * @param datetime $updated
     * @return \Entity\Product
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
     * @return \Entity\Product
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
     * @return \Entity\Product
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
     * @return \Entity\Product
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
     * Add Item entity to collection (one to many).
     *
     * @param \Entity\Item $item
     * @return \Entity\Product
     */
    public function addItem(Item $item)
    {
        $this->items[] = $item;

        return $this;
    }

    /**
     * Remove Item entity from collection (one to many).
     *
     * @param \Entity\Item $item
     * @return \Entity\Product
     */
    public function removeItem(Item $item)
    {
        $this->items->removeElement($item);

        return $this;
    }

    /**
     * Get Item entity collection (one to many).
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Add ProductPackage entity related by `Product_id` to collection (one to many).
     *
     * @param \Entity\ProductPackage $productPackage
     * @return \Entity\Product
     */
    public function addChildProduct(ProductPackage $productPackage)
    {
        $this->childProducts[] = $productPackage;

        return $this;
    }

    /**
     * Remove ProductPackage entity related by `Product_id` from collection (one to many).
     *
     * @param \Entity\ProductPackage $productPackage
     * @return \Entity\Product
     */
    public function removeChildProduct(ProductPackage $productPackage)
    {
        $this->childProducts->removeElement($productPackage);

        return $this;
    }

    /**
     * Get ProductPackage entity related by `Product_id` collection (one to many).
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChildProducts()
    {
        return $this->childProducts;
    }

    /**
     * Add ProductPackage entity related by `SubProduct_id` to collection (one to many).
     *
     * @param \Entity\ProductPackage $productPackage
     * @return \Entity\Product
     */
    public function addParentProduct(ProductPackage $productPackage)
    {
        $this->parentProducts[] = $productPackage;

        return $this;
    }

    /**
     * Remove ProductPackage entity related by `SubProduct_id` from collection (one to many).
     *
     * @param \Entity\ProductPackage $productPackage
     * @return \Entity\Product
     */
    public function removeParentProduct(ProductPackage $productPackage)
    {
        $this->parentProducts->removeElement($productPackage);

        return $this;
    }

    /**
     * Get ProductPackage entity related by `SubProduct_id` collection (one to many).
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getParentProducts()
    {
        return $this->parentProducts;
    }

    /**
     * Add ProductPrice entity to collection (one to many).
     *
     * @param \Entity\ProductPrice $productPrice
     * @return \Entity\Product
     */
    public function addProductPrice(ProductPrice $productPrice)
    {
        $this->productPrices[] = $productPrice;

        return $this;
    }
    
    /**
     * set ProductPrices
     * 
     * @param array of \Entity\ProductPrice $prices
     * @return \Entity\Product
     */
    public function setProductPrices(array $prices)
    {
        foreach($prices as $productPrice) {
            $this->addProductPrice($productPrice);
        }
        
        return $this;
    }

    /**
     * Remove ProductPrice entity from collection (one to many).
     *
     * @param \Entity\ProductPrice $productPrice
     * @return \Entity\Product
     */
    public function removeProductPrice(ProductPrice $productPrice)
    {
        $this->productPrices->removeElement($productPrice);

        return $this;
    }

    /**
     * Get ProductPrice entity collection (one to many).
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProductPrices()
    {
        if (!is_object($this->productPrices)) { 
            $this->productPrices = new ArrayCollection();
        }
        return $this->productPrices;
    }

    /**
     * Add ProductVariant entity to collection (one to many).
     *
     * @param \Entity\ProductVariant $productVariant
     * @return \Entity\Product
     */
    public function addProductVariant(ProductVariant $productVariant)
    {
        $this->productVariants[] = $productVariant;

        return $this;
    }
    
    /**
     * Set ProductVariants
     * 
     * @param array of \Entity\ProductVariant $variants
     * @return \Entity\Product
     */
    public function setProductVariants(array $variants)
    {
        foreach($variants as $productVariant) {
            $this->addProductVariant($productVariant);
        }
        
        return $this;
    }

    /**
     * Remove ProductVariant entity from collection (one to many).
     *
     * @param \Entity\ProductVariant $productVariant
     * @return \Entity\Product
     */
    public function removeProductVariant(ProductVariant $productVariant)
    {
        $this->productVariants->removeElement($productVariant);

        return $this;
    }

    /**
     * Get ProductVariant entity collection (one to many).
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProductVariants()
    {
        if (!is_object($this->productVariants)) { 
            $this->productVariants = new ArrayCollection();
        }
        return $this->productVariants;
    }

    /**
     * Set Tax entity (many to one).
     *
     * @param \Entity\Tax $tax
     * @return \Entity\Product
     */
    public function setTax(Tax $tax = null)
    {
        $this->tax = $tax;

        return $this;
    }

    /**
     * Get Tax entity (many to one).
     *
     * @return \Entity\Tax
     */
    public function getTax()
    {
        return $this->tax;
    }
    
    /**
     * Get actual price for this product
     * 
     * @return \Entity\ProductPrice
     */
    #public function getPrice()
    #{
    #    $diff = null;
    #    $now = new \DateTime();
    #    $ret = new ProductPrice();
    #    $null_ret = new ProductPrice();
    #    foreach($this->getProductPrices() as $price) {
    #        /*
    #         * continue when deadline is in the past
    #         */
    #        if($price->getDeadlineId() == null || $now->getTimestamp() > $price->getDeadline()->getDeadline()->getTimestamp()) {
    #            continue;
    #        }
    #        /*
    #         * save price when there's no deadline but the charge is greater 
    #         * than what we already have.
    #         */
    #        if($price->getDeadlineId() == null) {
    #            if($null_ret->getCharge() < $price->getCharge()) {
    #                $null_ret = $price;
    #            }
    #        }
    #
    #        $newDiff = $now->getTimestamp() - $price->getDeadline()->getDeadline()->getTimestamp();
    #        /*
    #         * If diff is not set or this deadline is nearer to now.
    #         */
    #        if(
    #            $diff == null ||
    #            ($diff - $newDiff) < 0
    #        )
    #        {
    #            $diff = $newDiff;
    #            $ret = $price;
    #        }
    #    }
    #    if($ret->getCharge() == null) {
    #        $ret = $null_ret;
    #    }
    #    return $ret;
    #}
    
    /**
     * Get prices by agegroup
     * 
     * @param \ersEntity\Entity\Agegroup $agegroup
     * @return type
     */
    public function getProductPrice(Agegroup $agegroup = null, Deadline $deadline = null, $search = true) {
        $ret = new ProductPrice();
        foreach($this->getProductPrices() as $price) {
            /* 
             * if a agegroup is given but price has none
             */
            if($price->getAgegroup() == null && $agegroup != null) {
                continue;
            }
            /* 
             * if a deadline is given but price has none
             */
            if($price->getDeadline() == null && $deadline != null) {
                continue;
            }
            /*
             * if no agegroup is given but price has one
             */
            if($price->getAgegroup() != null && $agegroup == null) {
                continue;
            }
            /*
             * if no deadline is given but price has one
             */
            if($price->getDeadline() != null && $deadline == null) {
                continue;
            }
            /*
             * if agegroup does not match
             */
            if($price->getAgegroup() != null && $agegroup != null && $price->getAgegroup()->getId() != $agegroup->getId()) {
                continue;
            }
            /*
             * if deadline does not match
             */
            if($price->getDeadline() != null && $deadline != null && $price->getDeadline()->getId() != $deadline->getId()) {
                continue;
            }
            
            /*
             * at this point we should only have the prices we want, take the highest one.
             */
            if($ret->getCharge() < $price->getCharge()) {
                $ret = $price;
            }
        }
        
        if($ret->getCharge() == null && $search) {
            /*
             * start searching only by agegroup
             */
            $ret = $this->getProductPrice($agegroup, null, false);
            if($ret->getCharge() == null) {
                $ret = $this->getProductPrice(null, null, false);
            }
        }
        
        return $ret;
    }
    
    /**
     * Get former prices for this product
     * 
     * @return array of \Entity\ProductPrice
     */
    public function getFormerPrices()
    {
        $now = new \DateTime();
        $diff = 0;
        $ret = new ArrayCollection();
        foreach($this->getProductPrices() as $price) {
            if($now > $price->getDeadline()->getDeadline()) {
                $ret[] = $price; 
            }
        }
        return $ret;
    }
    public function getFuturePrices()
    {
        $now = new \DateTime();
        $diff = 0;
        $ret = new ArrayCollection();
        foreach($this->getProductPrices() as $price) {
            if($now < $price->getDeadline()->getDeadline()) {
                $ret[] = $price; 
            }
        }
        return $ret;
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
                'filters' => array(
                    array('name' => 'Int'),
                ),
                'validators' => array(),
            ),
            array(
                'name' => 'taxId',
                'required' => true,
                'filters' => array(
                    /*array('name' => 'Int'),*/
                ),
                'validators' => array(
                    /*array(
                        'name'    => 'Digits',
                    ),*/
                ),
            ),
            array(
                'name' => 'ordering',
                'required' => false,
                'filters' => array(),
                'validators' => array(),
            ),
            array(
                'name' => 'name',
                'required' => false,
                'filters' => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 1,
                            'max'      => 45,
                        ),
                    ),
                ),
            ),
            array(
                'name' => 'shortDescription',
                'required' => false,
                'filters' => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 1,
                            'max'      => 128,
                        ),
                    ),
                ),
            ),
            array(
                'name' => 'longDescription',
                'required' => false,
                'filters' => array(
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 1,
                            'max'      => 1000,
                        ),
                    ),
                ),
            ),
            array(
                'name' => 'active',
                'required' => false,
                'filters' => array(),
                'validators' => array(),
            ),
            array(
                'name' => 'deleted',
                'required' => false,
                'filters' => array(),
                'validators' => array(),
            ),
            array(
                'name' => 'personalized',
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
        $dataFields = array('id', 'taxId', 'ordering', 'visible', 'name', 'shortDescription', 'longDescription', 'active', 'deleted', 'personalized', 'updated', 'created');
        $relationFields = array('tax');
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
        return array('id', 'taxId', 'ordering', 'visible', 'name', 'shortDescription', 'longDescription', 'active', 'deleted', 'personalized', 'updated', 'created');
    }
}
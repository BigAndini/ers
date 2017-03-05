<?php

/**
 * Auto generated by MySQL Workbench Schema Exporter.
 * Version 2.1.6-dev (doctrine2-mappedsuperclass) on 2017-03-05 16:29:23.
 * Goto https://github.com/johmue/mysql-workbench-schema-exporter for more
 * information.
 */

namespace ErsBase\Entity\Base;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * ErsBase\Entity\Base\Product
 *
 * @ORM\MappedSuperclass
 * @ORM\Table(name="`product`", indexes={@ORM\Index(name="fk_product_tax1_idx", columns={"`tax_id`"})})
 * @ORM\HasLifecycleCallbacks
 */
abstract class Product
{
    /**
     * @ORM\Id
     * @ORM\Column(name="`id`", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="`tax_id`", type="integer")
     */
    protected $tax_id;

    /**
     * @ORM\Column(name="`position`", type="integer", nullable=true)
     */
    protected $position;

    /**
     * @ORM\Column(name="`display_name`", type="string", length=45, nullable=true)
     */
    protected $display_name;

    /**
     * @ORM\Column(name="`name`", type="string", length=45, nullable=true)
     */
    protected $name;

    /**
     * @ORM\Column(name="`short_description`", type="string", length=128, nullable=true)
     */
    protected $short_description;

    /**
     * @ORM\Column(name="`long_description`", type="string", length=3000, nullable=true)
     */
    protected $long_description;

    /**
     * @ORM\Column(name="`active`", type="boolean", nullable=true)
     */
    protected $active;

    /**
     * @ORM\Column(name="`visible`", type="boolean", nullable=true)
     */
    protected $visible;

    /**
     * @ORM\Column(name="`deleted`", type="boolean", nullable=true)
     */
    protected $deleted;

    /**
     * @ORM\Column(name="`personalized`", type="boolean", nullable=true)
     */
    protected $personalized;

    /**
     * @ORM\Column(name="`ticket_template`", type="string", length=45, nullable=true)
     */
    protected $ticket_template;

    /**
     * @ORM\Column(name="`updated`", type="datetime")
     */
    protected $updated;

    /**
     * @ORM\Column(name="`created`", type="datetime")
     */
    protected $created;

    /**
     * @ORM\OneToMany(targetEntity="Item", mappedBy="product")
     * @ORM\JoinColumn(name="`id`", referencedColumnName="`Product_id`")
     */
    protected $items;

    /**
     * @ORM\OneToMany(targetEntity="ProductPackage", mappedBy="productRelatedByProductId")
     * @ORM\JoinColumn(name="`id`", referencedColumnName="`Product_id`")
     */
    protected $productPackageRelatedByProductIds;

    /**
     * @ORM\OneToMany(targetEntity="ProductPackage", mappedBy="productRelatedBySubProductId")
     * @ORM\JoinColumn(name="`id`", referencedColumnName="`SubProduct_id`")
     */
    protected $productPackageRelatedBySubProductIds;

    /**
     * @ORM\OneToMany(targetEntity="ProductPrice", mappedBy="product", cascade={"persist", "merge"})
     * @ORM\JoinColumn(name="`id`", referencedColumnName="`Product_id`")
     */
    protected $productPrices;

    /**
     * @ORM\OneToMany(targetEntity="ProductVariant", mappedBy="product", cascade={"persist", "merge"})
     * @ORM\JoinColumn(name="`id`", referencedColumnName="`Product_id`")
     * @ORM\OrderBy({"position":"ASC"})
     */
    protected $productVariants;

    /**
     * @ORM\ManyToOne(targetEntity="Tax", inversedBy="products")
     * @ORM\JoinColumn(name="`tax_id`", referencedColumnName="`id`")
     */
    protected $tax;

    public function __construct()
    {
        $this->items = new ArrayCollection();
        $this->productPackageRelatedByProductIds = new ArrayCollection();
        $this->productPackageRelatedBySubProductIds = new ArrayCollection();
        $this->productPrices = new ArrayCollection();
        $this->productVariants = new ArrayCollection();
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
     * Set the value of id.
     *
     * @param integer $id
     * @return \ErsBase\Entity\Base\Product
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
     * Set the value of tax_id.
     *
     * @param integer $tax_id
     * @return \ErsBase\Entity\Base\Product
     */
    public function setTaxId($tax_id)
    {
        $this->tax_id = $tax_id;

        return $this;
    }

    /**
     * Get the value of tax_id.
     *
     * @return integer
     */
    public function getTaxId()
    {
        return $this->tax_id;
    }

    /**
     * Set the value of position.
     *
     * @param integer $position
     * @return \ErsBase\Entity\Base\Product
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get the value of position.
     *
     * @return integer
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set the value of display_name.
     *
     * @param string $display_name
     * @return \ErsBase\Entity\Base\Product
     */
    public function setDisplayName($display_name)
    {
        $this->display_name = $display_name;

        return $this;
    }

    /**
     * Get the value of display_name.
     *
     * @return string
     */
    public function getDisplayName()
    {
        return $this->display_name;
    }

    /**
     * Set the value of name.
     *
     * @param string $name
     * @return \ErsBase\Entity\Base\Product
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the value of name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the value of short_description.
     *
     * @param string $short_description
     * @return \ErsBase\Entity\Base\Product
     */
    public function setShortDescription($short_description)
    {
        $this->short_description = $short_description;

        return $this;
    }

    /**
     * Get the value of short_description.
     *
     * @return string
     */
    public function getShortDescription()
    {
        return $this->short_description;
    }

    /**
     * Set the value of long_description.
     *
     * @param string $long_description
     * @return \ErsBase\Entity\Base\Product
     */
    public function setLongDescription($long_description)
    {
        $this->long_description = $long_description;

        return $this;
    }

    /**
     * Get the value of long_description.
     *
     * @return string
     */
    public function getLongDescription()
    {
        return $this->long_description;
    }

    /**
     * Set the value of active.
     *
     * @param boolean $active
     * @return \ErsBase\Entity\Base\Product
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
     * @return \ErsBase\Entity\Base\Product
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
     * @return \ErsBase\Entity\Base\Product
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
     * @return \ErsBase\Entity\Base\Product
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
     * Set the value of ticket_template.
     *
     * @param string $ticket_template
     * @return \ErsBase\Entity\Base\Product
     */
    public function setTicketTemplate($ticket_template)
    {
        $this->ticket_template = $ticket_template;

        return $this;
    }

    /**
     * Get the value of ticket_template.
     *
     * @return string
     */
    public function getTicketTemplate()
    {
        return $this->ticket_template;
    }

    /**
     * Set the value of updated.
     *
     * @param \DateTime $updated
     * @return \ErsBase\Entity\Base\Product
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get the value of updated.
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set the value of created.
     *
     * @param \DateTime $created
     * @return \ErsBase\Entity\Base\Product
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get the value of created.
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Add Item entity to collection (one to many).
     *
     * @param \ErsBase\Entity\Base\Item $item
     * @return \ErsBase\Entity\Base\Product
     */
    public function addItem(Item $item)
    {
        $this->items[] = $item;

        return $this;
    }

    /**
     * Remove Item entity from collection (one to many).
     *
     * @param \ErsBase\Entity\Base\Item $item
     * @return \ErsBase\Entity\Base\Product
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
     * @param \ErsBase\Entity\Base\ProductPackage $productPackage
     * @return \ErsBase\Entity\Base\Product
     */
    public function addProductPackageRelatedByProductId(ProductPackage $productPackage)
    {
        $this->productPackageRelatedByProductIds[] = $productPackage;

        return $this;
    }

    /**
     * Remove ProductPackage entity related by `Product_id` from collection (one to many).
     *
     * @param \ErsBase\Entity\Base\ProductPackage $productPackage
     * @return \ErsBase\Entity\Base\Product
     */
    public function removeProductPackageRelatedByProductId(ProductPackage $productPackage)
    {
        $this->productPackageRelatedByProductIds->removeElement($productPackage);

        return $this;
    }

    /**
     * Get ProductPackage entity related by `Product_id` collection (one to many).
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProductPackageRelatedByProductIds()
    {
        return $this->productPackageRelatedByProductIds;
    }

    /**
     * Add ProductPackage entity related by `SubProduct_id` to collection (one to many).
     *
     * @param \ErsBase\Entity\Base\ProductPackage $productPackage
     * @return \ErsBase\Entity\Base\Product
     */
    public function addProductPackageRelatedBySubProductId(ProductPackage $productPackage)
    {
        $this->productPackageRelatedBySubProductIds[] = $productPackage;

        return $this;
    }

    /**
     * Remove ProductPackage entity related by `SubProduct_id` from collection (one to many).
     *
     * @param \ErsBase\Entity\Base\ProductPackage $productPackage
     * @return \ErsBase\Entity\Base\Product
     */
    public function removeProductPackageRelatedBySubProductId(ProductPackage $productPackage)
    {
        $this->productPackageRelatedBySubProductIds->removeElement($productPackage);

        return $this;
    }

    /**
     * Get ProductPackage entity related by `SubProduct_id` collection (one to many).
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProductPackageRelatedBySubProductIds()
    {
        return $this->productPackageRelatedBySubProductIds;
    }

    /**
     * Add ProductPrice entity to collection (one to many).
     *
     * @param \ErsBase\Entity\Base\ProductPrice $productPrice
     * @return \ErsBase\Entity\Base\Product
     */
    public function addProductPrice(ProductPrice $productPrice)
    {
        $this->productPrices[] = $productPrice;

        return $this;
    }

    /**
     * Remove ProductPrice entity from collection (one to many).
     *
     * @param \ErsBase\Entity\Base\ProductPrice $productPrice
     * @return \ErsBase\Entity\Base\Product
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
        return $this->productPrices;
    }

    /**
     * Add ProductVariant entity to collection (one to many).
     *
     * @param \ErsBase\Entity\Base\ProductVariant $productVariant
     * @return \ErsBase\Entity\Base\Product
     */
    public function addProductVariant(ProductVariant $productVariant)
    {
        $this->productVariants[] = $productVariant;

        return $this;
    }

    /**
     * Remove ProductVariant entity from collection (one to many).
     *
     * @param \ErsBase\Entity\Base\ProductVariant $productVariant
     * @return \ErsBase\Entity\Base\Product
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
        return $this->productVariants;
    }

    /**
     * Set Tax entity (many to one).
     *
     * @param \ErsBase\Entity\Base\Tax $tax
     * @return \ErsBase\Entity\Base\Product
     */
    public function setTax(Tax $tax = null)
    {
        $this->tax = $tax;

        return $this;
    }

    /**
     * Get Tax entity (many to one).
     *
     * @return \ErsBase\Entity\Base\Tax
     */
    public function getTax()
    {
        return $this->tax;
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
        $dataFields = array('id', 'tax_id', 'position', 'display_name', 'name', 'short_description', 'long_description', 'active', 'visible', 'deleted', 'personalized', 'ticket_template', 'updated', 'created');
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
        return array('id', 'tax_id', 'position', 'display_name', 'name', 'short_description', 'long_description', 'active', 'visible', 'deleted', 'personalized', 'ticket_template', 'updated', 'created');
    }
}
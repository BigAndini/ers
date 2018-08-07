<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ErsBase\Stdlib\Hydrator\Strategy;

use DoctrineModule\Stdlib\Hydrator\Strategy\AbstractCollectionStrategy;
use Doctrine\Common\Collections\Collection;
use LogicException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Inflector\Inflector;

/**
 * When this strategy is used for Collections, if the new collection does not contain elements that are present in
 * the original collection, then this strategy will not remove those elements. At most, it will add new elements. For
 * instance, if the collection initially contains elements A and B, and that the new collection contains elements B
 * and C, then the final collection will contain elements A, B and C.
 *
 * This strategy is by value, this means it will use the public API (in this case, remover)
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @since   0.7.0
 * @author  Michael Gallego <mic.gallego@gmail.com>
 */
class DisallowRemoveByValue extends AbstractCollectionStrategy
{
    /**
     * {@inheritDoc}
     */
    public function hydrate($value)
    {
        // AllowRemove strategy need "adder"
        // check plural and singular "adder"
        $adder_list = array();
        $adder_list[] = 'add' . ucfirst($this->collectionName);
        if(!in_array('add' . ucfirst(Inflector::pluralize($this->collectionName)), $adder_list)) {
            $adder_list[] = 'add' . ucfirst(Inflector::pluralize($this->collectionName));
        }
        if(!in_array('add' . ucfirst(Inflector::singularize($this->collectionName)), $adder_list)) {
            $adder_list[] = 'add' . ucfirst(Inflector::singularize($this->collectionName));
        }
        
        $found_adder = false;
        foreach($adder_list as $add_method) {
            if (method_exists($this->object, $add_method)) {
                $found_adder = true;
                $adder = $add_method;
                error_log('found adder: '.$adder);
                break;
            }
        }
        
        if (!$found_adder) {
            throw new LogicException(
                sprintf(
                    'DisallowRemove strategy for DoctrineModule hydrator requires %s to
                     be defined in %s entity domain code, but it seems to be missing',
                    implode(', ', $adder_list),
                    get_class($this->object)
                )
            );
        }

        $collection = $this->getCollectionFromObjectByValue();

        if ($collection instanceof Collection) {
            $collection = $collection->toArray();
        }

        $toAdd = new ArrayCollection(array_udiff($value, $collection, array($this, 'compareObjects')));

        $this->object->$adder($toAdd);

        return $collection;
    }
}
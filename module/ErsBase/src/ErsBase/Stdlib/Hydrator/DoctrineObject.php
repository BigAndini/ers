<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ErsBase\Stdlib\Hydrator;

use DoctrineModule\Stdlib\Hydrator\DoctrineObject as OrigDoctrineObject;
use Doctrine\Common\Collections\ArrayCollection;

class DoctrineObject extends OrigDoctrineObject
{
    /**
     * take the original hydrateByValue method and change the setter generation 
     * to convert names with underscores correctly.
     *
     * @param  array  $data
     * @param  object $object
     * @throws RuntimeException
     * @return object
     */
    protected function hydrateByValue(array $data, $object)
    {
        $tryObject = $this->tryConvertArrayToObject($data, $object);
        $metadata  = $this->metadata;

        if (is_object($tryObject)) {
            $object = $tryObject;
        }

        foreach ($data as $field => $value) {
            $field  = $this->computeHydrateFieldName($field);
            $value  = $this->handleTypeConversions($value, $metadata->getTypeOfField($field));
            #$setter = 'set' . ucfirst($field);
            $setter = sprintf('set%s', ucfirst(
                str_replace(' ', '', ucwords(str_replace('_', ' ', $field)))
            ));

            if ($metadata->hasAssociation($field)) {
                $target = $metadata->getAssociationTargetClass($field);

                if ($metadata->isSingleValuedAssociation($field)) {
                    if (! method_exists($object, $setter)) {
                        continue;
                    }

                    $value = $this->toOne($target, $this->hydrateValue($field, $value, $data));

                    if (null === $value
                        && !current($metadata->getReflectionClass()->getMethod($setter)->getParameters())->allowsNull()
                    ) {
                        continue;
                    }

                    $object->$setter($value);
                } elseif ($metadata->isCollectionValuedAssociation($field)) {
                    $this->toMany($object, $field, $target, $value);
                }
            } else {
                if (! method_exists($object, $setter)) {
                    continue;
                }

                $object->$setter($this->hydrateValue($field, $value, $data));
            }
        }

        return $object;
    }
    
    /**
     * changing mechanism to generate getter to support underscored in 
     * field names.
     *
     * @param  object $object
     * @throws RuntimeException
     * @return array
     */
    protected function extractByValue($object)
    {
        $fieldNames = array_merge($this->metadata->getFieldNames(), $this->metadata->getAssociationNames());
        $methods    = get_class_methods($object);
        $filter     = $object instanceof FilterProviderInterface
            ? $object->getFilter()
            : $this->filterComposite;

        $data = array();
        foreach ($fieldNames as $fieldName) {
            if ($filter && !$filter->filter($fieldName)) {
                continue;
            }
            $getter = sprintf('get%s', ucfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $fieldName)))));
            $isser = sprintf('is%s', ucfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $fieldName)))));

            
            $dataFieldName = $this->computeExtractFieldName($fieldName);
            if (in_array($getter, $methods)) {
                #error_log('found method '.$getter);
                $value = $this->extractValue($fieldName, $object->$getter(), $object);
                /*if(get_class($value) == 'Doctrine\ORM\PersistentCollection') {
                    foreach($value as $v) {
                        error_log(get_class($v));
                    }
                }*/
                #error_log($dataFieldName);
                $data[$dataFieldName] = $value;
            } elseif (in_array($isser, $methods)) {
                $data[$dataFieldName] = $this->extractValue($fieldName, $object->$isser(), $object);
            } elseif (substr($fieldName, 0, 2) === 'is'
                && ctype_upper(substr($fieldName, 2, 1))
                && in_array($fieldName, $methods)) {
                $data[$dataFieldName] = $this->extractValue($fieldName, $object->$fieldName(), $object);
            }

            // Unknown fields are ignored
        }

        return $data;
    }

}
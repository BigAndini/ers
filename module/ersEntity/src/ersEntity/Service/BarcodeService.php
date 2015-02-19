<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ersEntity\Service;

use Doctrine\Common\Persistence\ObjectRepository;
use DoctrineModule\Validator\ObjectExists;

class BarcodeService
{
    // ...

    protected $validator;

    public function __construct(ObjectRepository $objectRepository)
    {
        $this->validator = new \DoctrineModule\Validator\ObjectExists(array(
           'object_repository' => $objectRepository,
           'fields'            => array('barcode')
        )); 
    }

    public function exists($barcode)
    {
        return $this->validator->isValid($barcode);
    }

    // ...
}
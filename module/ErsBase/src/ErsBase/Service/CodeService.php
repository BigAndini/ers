<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ErsBase\Service;

use Doctrine\Common\Persistence\ObjectRepository;
use DoctrineModule\Validator\ObjectExists;

class CodeService extends ServiceLocatorAwareService
{
    protected $validator;

    public function __construct(ObjectRepository $objectRepository)
    {
        $this->validator = new \DoctrineModule\Validator\ObjectExists(array(
           'object_repository' => $objectRepository,
           'fields'            => array('value')
        )); 
    }

    public function exists($code)
    {
        return $this->validator->isValid($code);
    }

    public function genCode() {
        $entityManager = $this->getServiceLocator()
                ->get('Doctrine\ORM\EntityManager');
        
        $code = new Entity\Code();
        $code->genCode();
        $codecheck = 1;
        while($codecheck != null) {
            $code->genCode();
            $codecheck = $entityManager->getRepository('ErsBase\Entity\Code')
                ->findOneBy(array('value' => $code->getValue()));
        }
        return $code;
    }
}
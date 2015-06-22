<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace PreReg\Service;

class TicketLimitService
{
    /* @var $em \Doctrine\ORM\EntityManager */
    private $em;
    
    public function __construct(\Doctrine\ORM\EntityManager $em) {
        $this->em = $em;
    }
    
    public function checkLimit() {
        $variantCounts = $this->em->createQueryBuilder()
                ->select('pvarval', 'COUNT(DISTINCT i.id)')
                ->from('ersEntity\Entity\ProductVariantValue', 'pvarval')
                ->join('pvarval.itemVariants', 'ivar')
                ->join('ivar.item', 'i')
                ->where("i.status != 'cancelled'")
                ->andWhere("pvarval.disabled != 1")
                //->andWhere('pvarval.capacity IS NOT NULL')
                //->andWhere('pvarval.capacity IS NOT NULL')
                ->groupBy('pvarval.id')
                ->getQuery()->getResult();
        
        foreach($variantCounts as $var) {
            $capacity = $var[0]->getCapacity();
            $warnCount = $var[0]->getWarnCount();
            $currentCount = (int)$var[1];
            
            if($currentCount >= $capacity) {
                // disable the product
            }
            else if($currentCount >= $warnCount && !$var[0]->isWarningSent()) {
                // send warning email
            }
        }
    }
}

<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ErsBase\Service;

class TicketCounterService {
    /* @var $sl \Zend\ServiceManager\ServiceLocatorInterface */

    private $sl;

    public function setServiceLocator(\Zend\ServiceManager\ServiceLocatorInterface $sl) {
        $this->sl = $sl;
    }

    public function getServiceLocator() {
        return $this->sl;
    }

    public function getCurrentItemCount(\ErsBase\Entity\Counter $counter) {
        $entityManager = $this->sl->get('Doctrine\ORM\EntityManager');
        
        $qb = $entityManager->createQueryBuilder()
                ->select('COUNT(DISTINCT i.id)')
                ->from('ErsBase\Entity\Item', 'i')
                ->join('i.status', 's', 'WITH', 's.active = 1');

        $i = 0;
        foreach ($counter->getProductVariantValues() as $variantValue) {
            $qb->join('i.itemVariants', 'ivar' . $i, 'WITH', 'ivar' . $i . '.product_variant_value_id = :pvvid' . $i);
            $qb->setParameter(':pvvid' . $i, $variantValue->getId());
            $i++;
        }

        return $qb->getQuery()->getSingleScalarResult();
    }
    
    public function checkLimits() {
        $entityManager = $this->sl->get('Doctrine\ORM\EntityManager');
        
        $counters = $entityManager->getRepository('ErsBase\Entity\Counter')
                ->findAll();

        /* @var $counter \ErsBase\Entity\Counter */
        foreach ($counters as $counter) {
            // skip counters that refer to already disabled variants
            // see the note below for why this is wrong for multiple variants per counter
            if ($counter->getProductVariantValues()->forAll(function($key, $val) { return $val->getDisabled(); })) {
                continue;
            }

            $count = $this->getCurrentItemCount($counter);

            if ($count >= $counter->getValue()) {
                $logger = $this->sl->get('Logger');
                $logger->info('Disabling variant values of counter "' . $counter->getName() . '" because ' . $counter->getValue() . ' items were reached.');

                // NOTE: Disabling all associated variant values is actually semantically wrong.
                // If an item or counter consists of more than one variant value, you have to deactivate the !!combination!! of them.
                // However, this cannot be represented yet and is also not needed for show tickets, which only have one variant value.
                foreach ($counter->getProductVariantValues() as $variantValue) {
                    $variantValue->setDisabled(1);
                    $entityManager->persist($variantValue);
                }
            }
        }

        $entityManager->flush();
    }

}

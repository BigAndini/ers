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
        $logger = $this->getServiceLocator()->get('Logger');
        
        $em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        
        $qb = $em->createQueryBuilder()
                ->select('COUNT(DISTINCT i.id)')
                ->from('ErsBase\Entity\Item', 'i')
                ->join('i.status', 's', 'WITH', 's.active = 1');

        if(count($counter->getProductVariantValues()) != 0) {
            $logger->debug(get_class().' found '.count($counter->getProductVariantValues()).' values');
            $i = 0;
            foreach ($counter->getProductVariantValues() as $productVariantValue) {
                $qb->join('i.itemVariants', 'ivar' . $i, 'WITH', 'ivar' . $i . '.product_variant_value_id = :pvvid' . $i);
                $qb->setParameter(':pvvid' . $i, $productVariantValue->getId());
                $i++;
            }
            $productVariantValueCount = $qb->getQuery()->getSingleScalarResult();
            $logger->debug(get_class().' productVariantValueCount: '.$productVariantValueCount);
            return $productVariantValueCount;
        }
        
        if(count($counter->getProductVariants()) != 0) {
            $i = 0;
            foreach ($counter->getProductVariants() as $productVariant) {
                $qb->join('i.itemVariants', 'ivar' . $i, 'WITH', 'ivar' . $i . '.product_variant_id = :pvid' . $i);
                $qb->setParameter(':pvid' . $i, $productVariant->getId());
                $i++;
            }
            $productVariantCount = $qb->getQuery()->getSingleScalarResult();
            $logger->debug(get_class().' productVariantCount: '.$productVariantCount);
            return $productVariantCount;
        }
        
        
        if(count($counter->getProducts()) != 0) {
            $i = 0;
            foreach ($counter->getProducts() as $product) {
                #$qb->join('i.itemVariants', 'ivar' . $i, 'WITH', 'ivar' . $i . '.product_id = :pid' . $i);
                $qb->andWhere($qb->expr()->eq('i.product_id', ':pid'.$i));
                $qb->setParameter(':pid' . $i, $product->getId());
                $i++;
            }
            
            $productCount = $qb->getQuery()->getSingleScalarResult();
            $logger->debug(get_class().' productCount: '.$productCount);
            return $productCount;
        }
    }
    
    public function checkLimits() {
        $em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        
        $counters = $em->getRepository('ErsBase\Entity\Counter')
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
                if($counter->getProductVariantValues() != 0) {
                    foreach ($counter->getProductVariantValues() as $productVariantValue) {
                        $productVariantValue->setDisabled(1);
                        $productVariantValue->setActive(0);
                        $em->persist($productVariantValue);
                    }
                }
                if($counter->getProductVariants() != 0) {
                    foreach ($counter->getProductVariants() as $productVariant) {
                        $productVariant->setDisabled(1);
                        $productVariant->setActive(0);
                        $em->persist($productVariant);
                    }
                }
                if($counter->getProducts() != 0) {
                    foreach ($counter->getProducts() as $productVariant) {
                        $product->setDisabled(1);
                        $product->setActive(0);
                        $em->persist($product);
                    }
                }
            }
        }

        $em->flush();
    }

}

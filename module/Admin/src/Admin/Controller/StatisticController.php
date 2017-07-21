<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class StatisticController extends AbstractActionController {
    public function indexAction() {
        return new ViewModel();
    }
    
    public function orgasAction() {
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $queryBuilder1 = $entityManager->getRepository('ErsBase\Entity\Order')->createQueryBuilder('o');
        $queryBuilder1->select(array('SUM(o.order_sum_eur) as ordersum'));
        $queryBuilder1->join('o.status', 's');
        $queryBuilder1->join('o.paymentType', 'pt');
        $queryBuilder1->where($queryBuilder1->expr()->eq('s.value', ':status'));
        #$queryBuilder1->groupBy('pt.name');
        
        $queryBuilder1->setParameter('status', 'paid');
        
        $ordersums = $queryBuilder1->getQuery()->getSingleResult();
        
        
        /* SELECT SUM( order_sum_eur ) , SUM( total_sum_eur )
            FROM `order`
            JOIN `match` ON `order`.id = `match`.order_id
            JOIN bank_statement ON bank_statement.id = `match`.bank_statement_id
            JOIN bank_account ON bank_account.id = bank_statement.bank_account_id
            WHERE bank_account.id =2
         */
        $queryBuilder2 = $entityManager->getRepository('ErsBase\Entity\Match')->createQueryBuilder('m');
        $queryBuilder2->select(array('SUM(o.order_sum_eur) as ordersum'));
        $queryBuilder2->join('m.order', 'o');
        $queryBuilder2->join('m.bankStatement', 'bs');
        $queryBuilder2->join('bs.paymentType', 'pt');
        $queryBuilder2->where($queryBuilder2->expr()->eq('pt.id', ':bank_account_id'));
        #$queryBuilder2->groupBy('pt.name');
        
        $queryBuilder2->setParameter('bank_account_id', '7');
        
        $volunteers1 = $queryBuilder2->getQuery()->getSingleResult();
        
        $queryBuilder3 = $entityManager->getRepository('ErsBase\Entity\Package')->createQueryBuilder('p');
        $queryBuilder3->select(array('COUNT(p.id) as participants'));
        $queryBuilder3->join('p.status', 's');
        $queryBuilder3->where($queryBuilder3->expr()->eq('s.value', ':status1'));
        $queryBuilder3->orWhere($queryBuilder3->expr()->eq('s.value', ':status2'));
        
        $queryBuilder3->setParameter('status1', 'paid');
        $queryBuilder3->setParameter('status2', 'ordered');
        
        $participants = $queryBuilder3->getQuery()->getSingleResult();
        
        $deadlines = $entityManager->getRepository('ErsBase\Entity\Deadline')
                ->findBy(array(), array('deadline' => 'DESC'));
        $agegroups = $entityManager->getRepository('ErsBase\Entity\Agegroup')
                ->findBy(array('price_change' => 1), array('agegroup' => 'DESC'));
        $last_agegroup = new \ErsBase\Entity\Agegroup();
        $last_agegroup->setAgegroup(new \DateTime('01.01.1000'));
        $last_agegroup->setName('adult');
        $agegroups[] = $last_agegroup;
        $participant_stats = array();
        
        foreach($deadlines as $deadline) {
            foreach($agegroups as $agegroup) {
                $queryBuilder4 = $entityManager->getRepository('ErsBase\Entity\Package')->createQueryBuilder('p');
                $queryBuilder4->select(array('COUNT(p.id) as participants', 's.value'));
                $queryBuilder4->join('p.status', 's');
                $queryBuilder4->join('p.order', 'o');
                $queryBuilder4->join('p.user', 'u');
                $queryBuilder4->where($queryBuilder4->expr()->eq('s.value', ':status1'));
                $queryBuilder4->orWhere($queryBuilder4->expr()->eq('s.value', ':status2'));
                $queryBuilder4->andWhere($queryBuilder4->expr()->between('o.created', ':date_from', ':date_to'));
                $queryBuilder4->andWhere($queryBuilder4->expr()->gt('u.birthday', ':agegroup1'));
                $queryBuilder4->groupBy('s.value');

                $queryBuilder4->setParameter('status1', 'paid');
                $queryBuilder4->setParameter('status2', 'ordered');
                $queryBuilder4->setParameter('date_from', $deadline->getDeadline());
                $queryBuilder4->setParameter('date_to', new \DateTime());
                $queryBuilder4->setParameter('agegroup1', $agegroup->getAgegroup());

                $participant_stats[$deadline->getId()][$agegroup->getName()] = $queryBuilder4->getQuery()->getResult();
            }
        }
        
        $stat_deadline = array();
        foreach($deadlines as $deadline) {
            $stat_deadline[$deadline->getId()] = $deadline;
        }
        $stat_agegroup = array();
        foreach($agegroups as $agegroup) {
            $stat_agegroup[$agegroup->getName()] = $agegroup;
        }
        
        return new ViewModel(array(
            'ordersums' => $ordersums,
            'volunteers1' => $volunteers1,
            'participants' => $participants,
            'participant_stats' => $participant_stats,
            'deadlines' => $stat_deadline,
            'agegroups' => $stat_agegroup,
        ));
    }
    
    public function ordersAction() {
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $orderSelectFields = array('COUNT(o.id) AS ordercount', 'SUM(o.order_sum) AS ordersum', 'SUM(o.total_sum) AS totalsum', 'SUM(o.order_sum_eur) AS ordersumeur', 'SUM(o.total_sum_eur) AS totalsumeur');
        
        $paymentStatusStats = $entityManager->createQueryBuilder()
                #->select(array_merge(array('o.payment_status AS label'), $orderSelectFields))
                ->select(array_merge(array('s status, s.value label'), $orderSelectFields))
                ->from('ErsBase\Entity\Status', 's')
                ->leftJoin('s.orders', 'o')
                ->groupBy('s.value', 's.id')
                ->orderBy('s.position')
                ->getQuery()->getResult();
        
        $byStatusGroups = array('active' => array(), 'inactive' => array());
        foreach($paymentStatusStats AS $statusData) {
            $group = ($statusData['status']->getActive() ? 'active' : 'inactive');
            $byStatusGroups[$group][] = $statusData;
        }
        
        $paymentTypeStats = $entityManager->createQueryBuilder()
                ->select(array_merge(array('pt.name AS label', 'c.short as currency'), $orderSelectFields))
                ->from('ErsBase\Entity\PaymentType', 'pt')
                ->join('pt.orders', 'o')
                ->join('o.status', 's', 'WITH', "s.active = 1")
                ->join('pt.currency', 'c')
                ->groupBy('pt.id')
                ->getQuery()->getResult();
        
        return new ViewModel(array(
            'stats_paymentStatusGroups' => $byStatusGroups,
            'stats_paymentTypes' => $paymentTypeStats,
            /*'orderActiveCount' => $entityManager->createQueryBuilder()
                ->select('COUNT(o.id)')
                ->from('ErsBase\Entity\Order', 'o')
                ->join('o.status', 's', 'WITH', "s.active = 1")
                ->getQuery()->getSingleScalarResult(),
            'orderInactiveCount' => $entityManager->createQueryBuilder()
                ->select('COUNT(o.id)')
                ->from('ErsBase\Entity\Order', 'o')
                ->join('o.status', 's', 'WITH', "s.active = 0")
                ->getQuery()->getSingleScalarResult(),*/
        ));
    }
    
    public function participantsAction() {
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $agegroups = $entityManager->getRepository('ErsBase\Entity\Agegroup')
                ->findAll();
        
        $queryBuilder = $entityManager->getRepository('ErsBase\Entity\Package')->createQueryBuilder('p');
        #$queryBuilder->join('p.status', 's');
        $queryBuilder->join('p.status', 's', 'WITH', "s.active = 1");
        $packages = $queryBuilder->getQuery()->getResult();
        
        $agegroupServicePrice = $this->getServiceLocator()
                ->get('ErsBase\Service\AgegroupService:price');
        $agegroupServiceTicket = $this->getServiceLocator()
                ->get('ErsBase\Service\AgegroupService:ticket');
        
        $agegroupStatsPrice = array();
        $agegroupStatsTicket = array();
        
        $defEntry = array(
            'count' => 0, # number of participants
            'onsite' => 0, # number of participants onsite
            );
        
        // initialize all groups with 0
        $agegroupStatsPrice['adult'] = $defEntry;
        $agegroupStatsTicket['adult'] = $defEntry;
        foreach($agegroupServicePrice->getAgegroups() AS $agegroup) {
            $agegroupStatsPrice[$agegroup->getName()] = $defEntry;
        }
        foreach($agegroupServiceTicket->getAgegroups() AS $agegroup) {
            $agegroupStatsTicket[$agegroup->getName()] = $defEntry;
        }
        
        $countryStats = [];
        
        foreach($packages as $package) {
            $participant = $package->getParticipant();
            $status = $package->getStatus()->getValue();
            $agPrice = $agegroupServicePrice->getAgegroupByDate($participant->getBirthday());
            $agTicket = $agegroupServiceTicket->getAgegroupByDate($participant->getBirthday());
        
            # use references to directly write to the sub-array
            $aggregate['price'] = &$agegroupStatsPrice[($agPrice ? $agPrice->getName() : 'adult')];
            $aggregate['ticket'] = &$agegroupStatsTicket[($agTicket ? $agTicket->getName() : 'adult')];
            
            $aggregate['price']['count']++;
            $aggregate['ticket']['count']++;
            
            if(empty($aggregate['price'][$status])) {
                $aggregate['price'][$status] = 0;
            }
            $aggregate['price'][$status]++;

            if(empty($aggregate['ticket'][$status])) {
                $aggregate['ticket'][$status] = 0;
            } 
            $aggregate['ticket'][$status]++;

            
            if($status == 'paid') {
                if($participant->getCountryId()) {
                    if(empty($countryStats[$participant->getCountryId()])) {
                        $countryStats[$participant->getCountryId()] = 0;
                    }
                    $countryStats[$participant->getCountryId()]++;

                } else {
                    if(empty($countryStats[0])) {
                        $countryStats[0] = 0;
                    }
                    $countryStats[0]++;

                }
            }
            
        }
        
        $queryBuilder = $entityManager->getRepository('ErsBase\Entity\Country')->createQueryBuilder('c');
        #$queryBuilder->join('p.status', 's');
        $queryBuilder->select('c.id', 'c.name');
        $dbCountries = $queryBuilder->getQuery()->getResult();
        
        $countries = [];
        foreach($dbCountries as $c) {
            $countries[$c['id']] = $c['name'];
            $count = 0;
            if(!empty($countryStats[$c['id']])) {
                $count = $countryStats[$c['id']];
            }
            $countryStatsLive2[] = [
                'id' => $c['id'],
                'name' => $c['name'],
                'count' => $count,
            ];
        }
        $countryStatsLive2[] = [
            'id' => 0,
            'name' => 'country not provided',
            'count' => $countryStats[0],
        ];
        
        uasort($countryStatsLive2, function($a, $b){ return ($b['count'] - $a['count']) ?: strcmp($a['id'], $b['id']); });
        
        /*
         * === by product type ===
         */
        
        // make sure the column we are indexing by with array_column does not have numeric keys,
        // otherwise array_merge does not do what we want (overwrite default values if present)
        $pseudoIdColumn = "CONCAT('x', prod.id) pseudoId";
        
        $productStatsBase = array_column($entityManager->createQueryBuilder()
                        ->select(
                                $pseudoIdColumn,
                                'prod.name label', 
                                'COUNT(DISTINCT u.id) AS usercount', 
                                'COUNT(i.id) itemcount')
                        ->from('ErsBase\Entity\Product', 'prod')
                        ->join('prod.items', 'i')
                        ->join('i.status', 's', 'WITH', 's.active = 1')
                        ->join('i.package', 'p')
                        ->join('p.user', 'u')
                        ->groupBy('prod.id')
                        ->orderBy('prod.position')
                        ->getQuery()->getResult(), NULL, 'pseudoId');
        
        $productStatsPaid = array_column($entityManager->createQueryBuilder()
                        ->select(
                                $pseudoIdColumn,
                                #'prod.name label', 
                                #'COUNT(DISTINCT u.id) AS usercount', 
                                'COUNT(i.id) paid')
                        ->from('ErsBase\Entity\Product', 'prod')
                        ->join('prod.items', 'i')
                        ->join('i.status', 's', 'WITH', 's.active = 1')
                        ->join('i.package', 'p')
                        ->join('p.user', 'u')
                        ->where($queryBuilder->expr()->eq('s.value', ':status'))
                        ->groupBy('prod.id')
                        ->orderBy('prod.position')
                        ->setParameter('status', 'paid')
                        ->getQuery()->getResult(), NULL, 'pseudoId');
        
        $productStatsOrdered = array_column($entityManager->createQueryBuilder()
                        ->select(
                                $pseudoIdColumn,
                                #'prod.name label', 
                                #'COUNT(DISTINCT u.id) AS usercount', 
                                'COUNT(i.id) ordered')
                        ->from('ErsBase\Entity\Product', 'prod')
                        ->join('prod.items', 'i')
                        ->join('i.status', 's', 'WITH', 's.active = 1')
                        ->join('i.package', 'p')
                        ->join('p.user', 'u')
                        ->where($queryBuilder->expr()->eq('s.value', ':status'))
                        ->groupBy('prod.id')
                        ->orderBy('prod.position')
                        ->setParameter('status', 'ordered')
                        ->getQuery()->getResult(), NULL, 'pseudoId');
        
        $productStats = array_merge_recursive(
                $productStatsBase, 
                $productStatsPaid, 
                $productStatsOrdered);
        
        /*
         * === by product variant ===
         */
        $itemsByVariantByProduct = [];
        $allProducts = $entityManager->getRepository('ErsBase\Entity\Product')->findBy([], ['position' => 'ASC']);
        /* @var $product \ErsBase\Entity\Product */
        foreach($allProducts as $product) {
            $queryBuilder = $entityManager->createQueryBuilder()
                    ->select('COUNT(i.id) itemcount')
                    ->from('ErsBase\Entity\Item', 'i')
                    #->join('i.status', 's', 'WITH', 's.active = 1')
                    ->join('i.status', 's', 'WITH', 's.value = :paid')
                    ->where('i.Product_id = :prod_id')
                    ->setParameter('paid', 'paid')
                    ->setParameter('prod_id', $product->getId());
            
            $variantNames = [];
            $variantValueIdCols = []; // list of query variables that hold the ProductVariantValue.id results
            $variantValueLabelCols = []; // list of query variables that hold the ProductVariantValue.value results
            $i = 0;
            foreach($product->getProductVariants() as $variant) {
                $variantNames[] = $variant->getName();
                
                $ivarName = 'ivar' . $i; // internal name of the "ItemVariant" entities
                $idParamName = 'variantId' . $i; // parameter to bind the variant id to
                $varValName = 'varvalue' . $i; // internal name of the "ProductVariantValue" entities
                $varValIdCol = 'valueId' . $i; // column name of the id of the ProductVariantValue
                $varValLabelCol = 'label' . $i; // column name of the string value of the ProductVariantValue
                
                $queryBuilder = $queryBuilder->join('i.itemVariants', $ivarName, 'WITH', "$ivarName.product_variant_id = :$idParamName")
                         ->join("$ivarName.productVariantValue", $varValName)
                         ->addSelect("$varValName.id $varValIdCol", "$varValName.value $varValLabelCol")
                         ->addGroupBy("$varValName.id")
                         ->addOrderBy("$varValName.position")
                         ->setParameter($idParamName, $variant->getId());
                
                $variantValueIdCols[] = $varValIdCol;
                $variantValueLabelCols[] = $varValLabelCol;
                
                $i++;
            }
            
            // skip products that don't have any variants
            if(empty($variantNames)) continue;
            
            $variantData = [];
            
            // prepopulate with default values for all variant combinations
            $allCombinations = $this->generateAllVariantCombinations($product);
            foreach($allCombinations as $combinationKey => $combinationLabels) {
               $variantData[$combinationKey] = [
                    "variantLabels" => $combinationLabels,
                    "itemCount" => 0
                ];
            }
            
            // fill with real itemcount values for present entries
            foreach($queryBuilder->getQuery()->getResult() as $row) {
                // ':'-separated list of ids as the key
                $combinationKey = ':' . implode(':', array_map(function($col) use ($row) { return $row[$col]; }, $variantValueIdCols));
                $variantData[$combinationKey]["itemCount"] = $row["itemcount"];
            }
            
            $itemsByVariantByProduct[] = [
                "productName" => $product->getName(),
                "variantNames" => $variantNames,
                "variantData" => $variantData
            ];
        }
        
        return new ViewModel(array(
            'agegroups' => $agegroups,
            'agegroupStatsPrice' => $agegroupStatsPrice,
            'agegroupStatsTicket' => $agegroupStatsTicket,
            'countries' => $countries,
            'countryStats' => $countryStatsLive2,
            'itemsByVariantByProduct' => $itemsByVariantByProduct,
            'productStats' => $productStats,
        ));
    }
    
    public function participantsOldAction() {
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $packageCount = [];
        $queryBuilder = [];
        
        $queryBuilder['all'] = $entityManager->getRepository('ErsBase\Entity\Package')->createQueryBuilder('p');
        $queryBuilder['all']->select('COUNT(p.id)');
        $packageCount['all'] = $queryBuilder['all']->getQuery()->getScalarResult();
        
        $queryBuilder['paid'] = $entityManager->getRepository('ErsBase\Entity\Package')->createQueryBuilder('p');
        $queryBuilder['paid']->select('COUNT(p.id)');
        $queryBuilder['paid']->join('p.status', 's');
        $queryBuilder['paid']->where($queryBuilder['paid']->expr()->eq('s.value', ':status'));
        $queryBuilder['paid']->setParameter('status', 'paid');
        
        $packageCount['paid'] = $queryBuilder['paid']->getQuery()->getScalarResult();
        
        $queryBuilder['onsite'] = $entityManager->getRepository('ErsBase\Entity\Package')->createQueryBuilder('p');
        $queryBuilder['onsite']->select('COUNT(p.id)');
        $queryBuilder['onsite']->join('p.status', 's');
        $queryBuilder['onsite']->where($queryBuilder['onsite']->expr()->eq('s.value', ':status'));
        $queryBuilder['onsite']->setParameter('status', 'onsite');
        
        $packageCount['onsite'] = $queryBuilder['onsite']->getQuery()->getScalarResult();
        
        
        
        /*
         * === by agegroups & country ===
         */
        $queryBuilder = $entityManager->createQueryBuilder();
        $users = $queryBuilder
                ->select(
                        'u.birthday', 
                        'c.id country_id', 
                        'c.name country_name', 
                        'SUM(i.price) ordersum',
                        'i.shipped shipped',
                        's.value status'
                        )
                ->from('ErsBase\Entity\User', 'u')
                ->leftJoin('u.country', 'c')
                ->join('u.packages', 'p')
                #->join('p.items', 'i', 'WITH', "i.status != 'cancelled' AND i.status != 'refund'")
                ->join('p.items', 'i')
                ->join('i.status', 's', 'WITH', "s.active = 1")
                ->groupBy('u.id', 'shipped', 's.value')
                ->getQuery()->getResult();
        
        $agegroupServicePrice = $this->getServiceLocator()
                ->get('ErsBase\Service\AgegroupService:price');
        $agegroupServiceTicket = $this->getServiceLocator()
                ->get('ErsBase\Service\AgegroupService:ticket');
        
        $agegroupStatsPrice = array();
        $agegroupStatsTicket = array();
        $countryStats = array();
        
        $defEntry = array(
            'count' => 0, # number of participants
            'amount' => 0, # amount of money
            'paid' => 0, # number of participants who paid
            'onsite' => 0, # number of participants onsite
            );
        
        // initialize all groups with 0
        $agegroupStatsPrice['adult'] = $defEntry;
        $agegroupStatsTicket['adult'] = $defEntry;
        foreach($agegroupServicePrice->getAgegroups() AS $agegroup) {
            $agegroupStatsPrice[$agegroup->getName()] = $defEntry;
        }
        foreach($agegroupServiceTicket->getAgegroups() AS $agegroup) {
            $agegroupStatsTicket[$agegroup->getName()] = $defEntry;
        }
        
        // calculate aggregate values
        foreach($users as $user) {
            $agPrice = $agegroupServicePrice->getAgegroupByDate($user['birthday']);
            $agTicket = $agegroupServiceTicket->getAgegroupByDate($user['birthday']);
            
            $aggregatePrice = &$agegroupStatsPrice[($agPrice ? $agPrice->getName() : 'adult')];
            $aggregateTicket = &$agegroupStatsTicket[($agTicket ? $agTicket->getName() : 'adult')];
            
            $aggregatePrice['count']++;
            $aggregatePrice['amount'] += $user['ordersum'];
            $aggregateTicket['count']++;
            $aggregateTicket['amount'] += $user['ordersum'];
            
            if($user['status'] == 'paid') {
                $aggregatePrice['paid']++;
                $aggregateTicket['paid']++;
            }
            if($user['shipped'] == 1) {
                $aggregatePrice['onsite']++;
                $aggregateTicket['onsite']++;
            } 
            
            $countryId = $user['country_id'] ?: 0;
            $countryName = $user['country_name'] ?: "unknown";
            if(!isset($countryStats[$countryId])) {
                $countryStats[$countryId] = array('name' => $countryName, 'count' => 0);
            }
            $countryStats[$countryId]['count']++;
        }
        
        // postprocess countries: sort descending by count (then by name)
        uasort($countryStats, function($a, $b){ return ($b['count'] - $a['count']) ?: strcmp($a['name'], $b['name']); });
        // move 'unknown' (ID 0) to front
        if(isset($countryStats[0])) {
            $unknownData = $countryStats[0];
            unset($countryStats[0]);
            array_unshift($countryStats, $unknownData);
        }
        
        
        
        /*
         * === by product type ===
         */
        
        // make sure the column we are indexing by with array_column does not have numeric keys,
        // otherwise array_merge does not do what we want (overwrite default values if present)
        $pseudoIdColumn = "CONCAT('x', prod.id) pseudoId";
        
        $productStatsBase = array_column($entityManager->createQueryBuilder()
                        ->select(
                                $pseudoIdColumn,
                                'prod.name label', 
                                'COUNT(DISTINCT u.id) AS usercount', 
                                'COUNT(i.id) itemcount')
                        ->from('ErsBase\Entity\Product', 'prod')
                        ->join('prod.items', 'i')
                        ->join('i.status', 's', 'WITH', 's.active = 1')
                        ->join('i.package', 'p')
                        ->join('p.user', 'u')
                        ->groupBy('prod.id')
                        ->orderBy('prod.position')
                        ->getQuery()->getResult(), NULL, 'pseudoId');
        
        $productStatsPaid = array_column($entityManager->createQueryBuilder()
                        ->select(
                                $pseudoIdColumn,
                                #'prod.name label', 
                                #'COUNT(DISTINCT u.id) AS usercount', 
                                'COUNT(i.id) paid')
                        ->from('ErsBase\Entity\Product', 'prod')
                        ->join('prod.items', 'i')
                        ->join('i.status', 's', 'WITH', 's.active = 1')
                        ->join('i.package', 'p')
                        ->join('p.user', 'u')
                        ->where($queryBuilder->expr()->eq('s.value', ':status'))
                        ->groupBy('prod.id')
                        ->orderBy('prod.position')
                        ->setParameter('status', 'paid')
                        ->getQuery()->getResult(), NULL, 'pseudoId');
        
        $productStatsOrdered = array_column($entityManager->createQueryBuilder()
                        ->select(
                                $pseudoIdColumn,
                                #'prod.name label', 
                                #'COUNT(DISTINCT u.id) AS usercount', 
                                'COUNT(i.id) ordered')
                        ->from('ErsBase\Entity\Product', 'prod')
                        ->join('prod.items', 'i')
                        ->join('i.status', 's', 'WITH', 's.active = 1')
                        ->join('i.package', 'p')
                        ->join('p.user', 'u')
                        ->where($queryBuilder->expr()->eq('s.value', ':status'))
                        ->groupBy('prod.id')
                        ->orderBy('prod.position')
                        ->setParameter('status', 'ordered')
                        ->getQuery()->getResult(), NULL, 'pseudoId');
        
        $productStats = array_merge_recursive(
                $productStatsBase, 
                $productStatsPaid, 
                $productStatsOrdered);
        
        
        /*
         * === by payment type ===
         */
        
        $pseudoIdColumn = "CONCAT('x', pt.id) pseudoId";
        
        $paymentTypeStatsBase = array_column($entityManager->createQueryBuilder()
                ->select(
                        $pseudoIdColumn,
                        'pt.name label', 
                        'COUNT(DISTINCT u.id) AS usercount', 
                        'COUNT(i.id) itemcount',
                        'SUM(i.price*i.amount) as amount')
                ->from('ErsBase\Entity\PaymentType', 'pt')
                ->join('pt.orders', 'o')
                ->join('o.packages', 'p')
                ->join('p.user', 'u')
                ->join('p.items', 'i')
                ->join('i.status', 's', 'WITH', 's.active = 1')
                ->groupBy('pt.id')
                ->orderBy('pt.position')
                ->getQuery()->getResult(), NULL, 'pseudoId');
        
        $paymentTypeStatsPaid = array_column($entityManager->createQueryBuilder()
                ->select(
                        $pseudoIdColumn,
                        'COUNT(i.id) paid')
                ->from('ErsBase\Entity\PaymentType', 'pt')
                ->join('pt.orders', 'o')
                ->join('o.packages', 'p')
                ->join('p.user', 'u')
                ->join('p.items', 'i')
                ->join('i.status', 's', 'WITH', 's.active = 1')
                ->where($queryBuilder->expr()->eq('s.value', ':status'))
                ->groupBy('pt.id')
                ->orderBy('pt.position')
                ->setParameter('status', 'paid')
                ->getQuery()->getResult(), NULL, 'pseudoId');
        
        $paymentTypeStatsOrdered = array_column($entityManager->createQueryBuilder()
                ->select(
                        $pseudoIdColumn,
                        'COUNT(i.id) ordered')
                ->from('ErsBase\Entity\PaymentType', 'pt')
                ->join('pt.orders', 'o')
                ->join('o.packages', 'p')
                ->join('p.user', 'u')
                ->join('p.items', 'i')
                ->join('i.status', 's', 'WITH', 's.active = 1')
                ->where($queryBuilder->expr()->eq('s.value', ':status'))
                ->groupBy('pt.id')
                ->orderBy('pt.position')
                ->setParameter('status', 'ordered')
                ->getQuery()->getResult(), NULL, 'pseudoId');
        
        $paymentTypeStats = array_merge_recursive(
                $paymentTypeStatsBase,
                $paymentTypeStatsPaid,
                $paymentTypeStatsOrdered);
        
        /*
         * === by paymenttype ===
         */
        
        $pseudoIdColumn = "CONCAT('x', pt.id) pseudoId";
        
        $bankAccountStatsBase = array_column($entityManager->createQueryBuilder()
                ->select(
                        $pseudoIdColumn,
                        'pt.name label', 
                        'COUNT(DISTINCT u.id) AS usercount', 
                        'COUNT(i.id) itemcount',
                        'SUM(i.price*i.amount) as amount')
                ->from('ErsBase\Entity\PaymentType', 'pt')
                ->join('pt.bankStatements', 'bs')
                ->join('bs.matches', 'm')
                ->join('m.order', 'o')
                ->join('o.packages', 'p')
                ->join('p.user', 'u')
                ->join('p.items', 'i')
                ->join('i.status', 's', 'WITH', 's.active = 1')
                ->groupBy('pt.id')
                #->orderBy('pt.position')
                ->getQuery()->getResult(), NULL, 'pseudoId');
        
        $bankAccountStatsPaid = array_column($entityManager->createQueryBuilder()
                ->select(
                        $pseudoIdColumn,
                        'COUNT(i.id) paid')
                ->from('ErsBase\Entity\PaymentType', 'pt')
                ->join('pt.bankStatements', 'bs')
                ->join('bs.matches', 'm')
                ->join('m.order', 'o')
                ->join('o.packages', 'p')
                ->join('p.user', 'u')
                ->join('p.items', 'i')
                ->join('i.status', 's', 'WITH', 's.active = 1')
                ->where($queryBuilder->expr()->eq('s.value', ':status'))
                ->groupBy('pt.id')
                #->orderBy('pt.position')
                ->setParameter('status', 'paid')
                ->getQuery()->getResult(), NULL, 'pseudoId');
        
        $bankAccountStatsOrdered = array_column($entityManager->createQueryBuilder()
                ->select(
                        $pseudoIdColumn,
                        'COUNT(i.id) ordered')
                ->from('ErsBase\Entity\PaymentType', 'pt')
                ->join('pt.bankStatements', 'bs')
                ->join('bs.matches', 'm')
                ->join('m.order', 'o')
                ->join('o.packages', 'p')
                ->join('p.user', 'u')
                ->join('p.items', 'i')
                ->join('i.status', 's', 'WITH', 's.active = 1')
                ->where($queryBuilder->expr()->eq('s.value', ':status'))
                ->groupBy('pt.id')
                #->orderBy('pt.position')
                ->setParameter('status', 'ordered')
                ->getQuery()->getResult(), NULL, 'pseudoId');
        
        $bankAccountStats = array_merge_recursive(
                $bankAccountStatsBase,
                $bankAccountStatsPaid,
                $bankAccountStatsOrdered);
        
        /*
         * === by product variant ===
         */
        $itemsByVariantByProduct = [];
        $allProducts = $entityManager->getRepository('ErsBase\Entity\Product')->findBy([], ['position' => 'ASC']);
        /* @var $product \ErsBase\Entity\Product */
        foreach($allProducts as $product) {
            $queryBuilder = $entityManager->createQueryBuilder()
                    ->select('COUNT(i.id) itemcount')
                    ->from('ErsBase\Entity\Item', 'i')
                    ->join('i.status', 's', 'WITH', 's.active = 1')
                    ->where('i.Product_id = :prod_id')
                    ->setParameter('prod_id', $product->getId());
            
            $variantNames = [];
            $variantValueIdCols = []; // list of query variables that hold the ProductVariantValue.id results
            $variantValueLabelCols = []; // list of query variables that hold the ProductVariantValue.value results
            $i = 0;
            foreach($product->getProductVariants() as $variant) {
                $variantNames[] = $variant->getName();
                
                $ivarName = 'ivar' . $i; // internal name of the "ItemVariant" entities
                $idParamName = 'variantId' . $i; // parameter to bind the variant id to
                $varValName = 'varvalue' . $i; // internal name of the "ProductVariantValue" entities
                $varValIdCol = 'valueId' . $i; // column name of the id of the ProductVariantValue
                $varValLabelCol = 'label' . $i; // column name of the string value of the ProductVariantValue
                
                $queryBuilder = $queryBuilder->join('i.itemVariants', $ivarName, 'WITH', "$ivarName.product_variant_id = :$idParamName")
                         ->join("$ivarName.productVariantValue", $varValName)
                         ->addSelect("$varValName.id $varValIdCol", "$varValName.value $varValLabelCol")
                         ->addGroupBy("$varValName.id")
                         ->addOrderBy("$varValName.position")
                         ->setParameter($idParamName, $variant->getId());
                
                $variantValueIdCols[] = $varValIdCol;
                $variantValueLabelCols[] = $varValLabelCol;
                
                $i++;
            }
            
            // skip products that don't have any variants
            if(empty($variantNames)) continue;
            
            $variantData = [];
            
            // prepopulate with default values for all variant combinations
            $allCombinations = $this->generateAllVariantCombinations($product);
            foreach($allCombinations as $combinationKey => $combinationLabels) {
               $variantData[$combinationKey] = [
                    "variantLabels" => $combinationLabels,
                    "itemCount" => 0
                ];
            }
            
            // fill with real itemcount values for present entries
            foreach($queryBuilder->getQuery()->getResult() as $row) {
                // ':'-separated list of ids as the key
                $combinationKey = ':' . implode(':', array_map(function($col) use ($row) { return $row[$col]; }, $variantValueIdCols));
                $variantData[$combinationKey]["itemCount"] = $row["itemcount"];
            }
            
            $itemsByVariantByProduct[] = [
                "productName" => $product->getName(),
                "variantNames" => $variantNames,
                "variantData" => $variantData
            ];
        }
        
        
        
        return new ViewModel(array(
            'packageCount' => $packageCount,
            'stats_agegroupPrice' => $agegroupStatsPrice,
            'stats_agegroupTicket' => $agegroupStatsTicket,
            'stats_productType' => $productStats,
            'stats_paymentType' => $paymentTypeStats,
            'stats_paymenttype' => $bankAccountStats,
            'stats_productVariant' => $itemsByVariantByProduct,
            'stats_country' => $countryStats,
        ));
    }
    
    private function generateAllVariantCombinations(\ErsBase\Entity\Product $product) {
        $results = [ '' => [] ];
        
        // produce an array of all possible combinations of variant values
        foreach ($product->getProductVariants() as $variant) {
            $newResults = [];
            // combine each entry so far with each value of the new variant
            foreach($results as $key => $tuple) {
                foreach($variant->getProductVariantValues() as $variantValue) {
                    $newKey = $key . ':' . $variantValue->getId();
                    $newTuple = $tuple;
                    $newTuple[] = $variantValue->getValue();
                    $newResults[$newKey] = $newTuple;
                }
            }
            
            $results = $newResults;
        }
        
        return $results;
    }
    
    public function paymenttypesAction() {
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $activeStats = array();
        $matchingStats = array();
        
        $paymenttypes = $entityManager->getRepository('ErsBase\Entity\PaymentType')
                ->findAll();
        
        /* @var $paymenttype \ErsBase\Entity\PaymentType */
        foreach($paymenttypes as $paymenttype) {
            $statementFormat = json_decode($paymenttype->getStatementFormat());
           
            $queryBuilder = $entityManager->createQueryBuilder()
                    ->select('COUNT(s.id) AS stmtcount', 'SUM(col.value) AS amount, MAX(s.created) AS latestentry', 'c.short as currency', 'c.factor as factor')
                    ->from('ErsBase\Entity\PaymentType', 'acc')
                    ->join('acc.bankStatements', 's', 'WITH', "s.status != 'disabled'")
                    ->join('s.bankStatementCols', 'col', 'WITH', 'col.column = :colNum')
                    ->join('acc.currency', 'c')
                    ->where('acc.id = :accountId')
                    
                    ->setParameter('accountId', $paymenttype->getId())
                    ->setParameter('colNum', $statementFormat->amount);
            
            $activeStats[$paymenttype->getName()] = $queryBuilder
                    ->getQuery()->getSingleResult();
                       
            // extend the query to only include matched statements
            $matchingStats[$paymenttype->getName()] = $queryBuilder
                    ->andWhere("s.status = 'matched'")
                    ->getQuery()->getSingleResult();
            
            // for Polish bank account: change amount from Groszy to Zloty (1 Zloty = 100 Groszy)
            if ($activeStats[$paymenttype->getName()]['currency'] == 'PLN')
                $activeStats[$paymenttype->getName()]['amount'] /= 100;
            if ($matchingStats[$paymenttype->getName()]['currency'] == 'PLN')
                $matchingStats[$paymenttype->getName()]['amount'] /= 100;
        }    
        
        return new ViewModel(array(
            'activeStats' => $activeStats,
            'matchingStats' => $matchingStats,
        ));
    }
    
    public function onsiteAction() {
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $queryBuilder = $entityManager->getRepository('ErsBase\Entity\Item')->createQueryBuilder('i');
        $queryBuilder->where("i.shipped = 1");
        $queryBuilder->andWhere($queryBuilder->expr()->orX(
                $queryBuilder->expr()->eq("i.Product_id", "1"),
                $queryBuilder->expr()->eq("i.Product_id", "4")));
        $shippedItems = $queryBuilder->getQuery()->getResult();
        
        $itemStats = array();
        foreach($shippedItems as $item) {
            if(!isset($itemStats[$item->getShippedDate()->format('Y-m-d')][$item->getShippedDate()->format('H')])) {
                $itemStats[$item->getShippedDate()->format('Y-m-d')][$item->getShippedDate()->format('H')] = 0;
            }
            $itemStats[$item->getShippedDate()->format('Y-m-d')][$item->getShippedDate()->format('H')]++;
        }
        
        return new ViewModel(array(
            'itemStats' => $itemStats,
        ));
    }
    
    public function ordersPerDayAction() {
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        

        // SELECT COUNT(*), DATE(`order`.created) 
        // FROM `order` 
        // JOIN status ON `order`.status_id=`status`.id 
        // WHERE status.value = 'ordered' 
        // GROUP BY DATE(`order`.created);
        $queryBuilder1 = $entityManager->getRepository("ErsBase\Entity\Order")
                ->createQueryBuilder('o');
        $queryBuilder1->select('COUNT(o.id) as count', 'DATE(o.created) as date');
        $queryBuilder1->join('o.status','s');
        $queryBuilder1->where($queryBuilder1->expr()->eq('s.active', 1));
        $queryBuilder1->groupBy('date');

        $orderStats = $queryBuilder1->getQuery()->getResult();
        
        return new ViewModel(array(
            'chartData' => json_encode($orderStats),
        ));
    }
    
    public function participantsPerDayAction() {
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        

        // SELECT COUNT(package.id), DATE(`order`.created) 
        // FROM `order` 
        // JOIN status ON `order`.status_id=`status`.id 
        // JOIN package ON `package`.order_id=`order`.id 
        // WHERE status.value = 'ordered' 
        // GROUP BY DATE(`order`.created);
        $queryBuilder1 = $entityManager->getRepository("ErsBase\Entity\Order")
                ->createQueryBuilder('o');
        $queryBuilder1->select('COUNT(p.id) as count', 'DATE(o.created) as date');
        $queryBuilder1->join('o.status','s');
        $queryBuilder1->join('o.packages','p');
        $queryBuilder1->where($queryBuilder1->expr()->eq('s.active', 1));
        $queryBuilder1->groupBy('date');
        
        $stats = $queryBuilder1->getQuery()->getResult();
        
        return new ViewModel(array(
            'chartData' => json_encode($stats),
        ));
    }
}
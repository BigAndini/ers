<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace OnsiteReg\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use OnsiteReg\Form;

class SearchController extends AbstractActionController {

    public function indexAction() {
        $searchQuery = trim($this->params()->fromQuery('q'));

        $packages = [];
        
        $form = new Form\Search();
        
        if (empty($searchQuery)) {
            return new ViewModel(array(
                'form' => $form,
                'searchQuery' => $searchQuery,
                'results' => $packages,
            ));
        }
        $entityManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');

        $queryBuilder = $entityManager->createQueryBuilder()
                ->select('p')
                ->from('ErsBase\Entity\Package', 'p')
                ->join('p.user', 'u') # participant
                ->join('p.code', 'pcode')
                ->join('p.order', 'o')
                ->join('o.code', 'ocode')
                ->join('o.user', 'b') # buyer
                ->orderBy('u.firstname')
                ->where('1=1');

        if (preg_match('~^\d+$~', $q)) {
            // if the entire query consists of nothing but a number, treat it as a package ID
            $queryBuilder->andWhere('p.id = :id');
            $queryBuilder->setParameter(':id', (int) $q);
        } else {
            $exprUName = $queryBuilder->expr()->concat('u.firstname', $queryBuilder->expr()->concat($queryBuilder->expr()->literal(' '), 'u.surname'));
            //$exprBName = $queryBuilder->expr()->concat('b.firstname', $queryBuilder->expr()->concat($queryBuilder->expr()->literal(' '), 'b.surname'));

            $words = preg_split('~\s+~', $searchQuery);
            $i = 0;
            foreach ($words as $word) {
                try {
                    $wordAsDate = new \DateTime($word);
                } catch (\Exception $ex) {
                    $wordAsDate = NULL;
                }

                $param = ':p' . $i;
                $paramDate = ':pd' . $i;
                $queryBuilder->andWhere(
                        $queryBuilder->expr()->orX(
                                $queryBuilder->expr()->like($exprUName, $param), //
                                $queryBuilder->expr()->like('u.email', $param), //
                                //$queryBuilder->expr()->like($exprBName, $param),
                                $queryBuilder->expr()->like('pcode.value', $param), //
                                $queryBuilder->expr()->like('ocode.value', $param), //
                                ($wordAsDate ? $queryBuilder->expr()->eq('u.birthday', $paramDate) : '1=0')
                        )
                );

                $queryBuilder->setParameter($param, '%' . $word . '%');
                if($wordAsDate) {
                    $queryBuilder->setParameter($paramDate, $wordAsDate);
                }
                $i++;
            }
        }

        $dbPackages = $queryBuilder->getQuery()->getResult();
        foreach($dbPackages as $package) {
            if($package->getItemCount() == 0) {
                continue;
            }
            $packages[] = $package;
        }

        if (count($packages) == 1) {
            return $this->redirect()->toRoute('onsite/package', array('action' => 'detail', 'id' => $packages[0]->getId()));
        }

        $form->setData($this->getRequest()->getQuery());
        
        return new ViewModel(array(
            'form' => $form,
            'searchQuery' => $searchQuery,
            'results' => $packages,
        ));
    }

}

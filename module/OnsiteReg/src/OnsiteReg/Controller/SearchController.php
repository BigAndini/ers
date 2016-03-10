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
        $q = trim($this->params()->fromQuery('q'));

        $results = [];
        if (!empty($q)) {
            $em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');

            $qb = $em->createQueryBuilder()
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
                $qb->andWhere('p.id = :id');
                $qb->setParameter(':id', (int) $q);
            } else {
                $exprUName = $qb->expr()->concat('u.firstname', $qb->expr()->concat($qb->expr()->literal(' '), 'u.surname'));
                //$exprBName = $qb->expr()->concat('b.firstname', $qb->expr()->concat($qb->expr()->literal(' '), 'b.surname'));

                $words = preg_split('~\s+~', $q);
                $i = 0;
                foreach ($words as $word) {
                    try {
                        $wordAsDate = new \DateTime($word);
                    } catch (\Exception $ex) {
                        $wordAsDate = NULL;
                    }
                    
                    $param = ':p' . $i;
                    $paramDate = ':pd' . $i;
                    $qb->andWhere(
                            $qb->expr()->orX(
                                    $qb->expr()->like($exprUName, $param), //
                                    $qb->expr()->like('u.email', $param), //
                                    //$qb->expr()->like($exprBName, $param),
                                    $qb->expr()->like('pcode.value', $param), //
                                    $qb->expr()->like('ocode.value', $param), //
                                    ($wordAsDate ? $qb->expr()->eq('u.birthday', $paramDate) : '1=0')
                            )
                    );
                    
                    $qb->setParameter($param, '%' . $word . '%');
                    if($wordAsDate)
                        $qb->setParameter($paramDate, $wordAsDate);

                    $i++;
                }
            }
            
            $results = $qb->getQuery()->getResult();

            if (count($results) == 1) {
                return $this->redirect()->toRoute('onsite/package', array('action' => 'detail', 'id' => $results[0]->getId()));
            }
        }

        $form = new Form\Search();

        //$form->setData($this->getRequest()->getQuery());
        // commented out so the form is not filled with the search query;
        // leaving the search box empty for the next search is probably a better UX

        return new ViewModel(array(
            'form' => $form,
            'query' => $q,
            'results' => $results,
        ));
    }

}

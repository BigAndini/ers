<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Admin\Form;
use ErsBase\Service;

class RefundController extends AbstractActionController {
    public function indexAction() {
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        /*
         * search for orders that do contain items in status refund
         */
        /*$qb = $entityManager->getRepository('ErsBase\Entity\Order')
                ->createQueryBuild('o');*/
        $qb = $entityManager->getRepository('ErsBase\Entity\Order')
                ->createQueryBuilder('o');
        $qb->join('o.packages', 'p');
        $qb->join('p.items', 'i');
        $qb->where("i.status = 'refund'");
        
        $orders = $qb->getQuery()->getResult();
        
        $items = $entityManager->getRepository('ErsBase\Entity\Item')
                ->findBy(array('status' => 'refund'), array('updated' => 'DESC'));
        
        return new ViewModel(array(
            'items' => $items,
            'orders' => $orders,
        ));
    }
    
    public function enterAction() {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/refund', array());
        }
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $order = $entityManager->getRepository('ErsBase\Entity\Order')
                ->findOneBy(array('id' => $id));

        #$form = $this->getServiceLocator()->get('Admin\Form\Product');
        $form = new Form\EnterRefund();
        #$form->bind($order);
        $form->get('id')->setValue($order->getId());
        $form->get('submit')->setAttribute('value', 'Enter');

        $request = $this->getRequest();
        if ($request->isPost()) {
            #$form->setInputFilter($product->getInputFilter());
            $form->setData($request->getPost());
            
            if ($form->isValid()) {
                $data = $form->getData();
                $order = $entityManager->getRepository('ErsBase\Entity\Order')
                    ->findOneBy(array('id' => $data['id']));
                $order->setRefundSum($order->getRefundSum()+$data['amount']);
                $entityManager->persist($order);
                
                if($order->getRefundSum() == $order->getPrice('refund')) {
                    $statusCancelled = $entityManager->getRepository('ErsBase\Entity\Status')
                        ->findOneBy(array('value' => 'cancelled'));
                    foreach($order->getItemsByStatus('refund') as $item) {
                        $item->setStatus($statusCancelled);
                        $entityManager->persist($item);
                    }
                }
                
                $entityManager->flush();

                $forrest = new Service\BreadcrumbService();
                if(!$forrest->exists('refund')) {
                    $forrest->set('refund', 'admin/refund');
                }
                $breadcrumb = $forrest->get('refund');
                return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
            }
        }

        return new ViewModel(array(
            'id' => $id,
            'form' => $form,
            'order' => $order,
        ));
    }
}

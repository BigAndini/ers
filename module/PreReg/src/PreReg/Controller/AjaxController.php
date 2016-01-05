<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace PreReg\Controller;

use Zend\Mvc\Controller\AbstractActionController;
#use Application\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use ErsBase\Service;

class AjaxController extends AbstractActionController
{    
    public function sessionStorageAction() {
        $logger = $this->getServiceLocator()->get('Logger');
        
        $id = $this->params()->fromRoute('name', 'fallback');
        if (!$id) {
            $logger->err('Unable to set Session Storage');
            return $this->getResponse()->setContent('');
        }
        
        $breadcrumbService = new Service\BreadcrumbService();
        $breadcrumbService->activate($id);
        
        $logger->info('activated id: '.$id);
        
        return $this->getResponse()->setContent($id);
    }
    
    public function getStorageNameAction() {
        $breadcrumbService = new Service\BreadcrumbService();
        return $this->getResponse()->setContent('<h1>'.$breadcrumbService->getId().'</h1>');
    }
}

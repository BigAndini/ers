<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ErsBase\Service;

use Doctrine\Common\Persistence\ObjectRepository;
use DoctrineModule\Validator\ObjectExists;

class ShortcodeService
{
    protected $text;
    protected $filters;
    protected $sl;
    protected $object;

    public function __construct() {
        $this->object = [];
    }
    
    public function getText() {
        return $this->text;
    }
    public function setText($text) {
        $this->text = $text;
    }

    public function setServiceLocator($sl) {
        $this->sl = $sl;
        
        return $this;
    }
    public function getServiceLocator() {
        return $this->sl;
    }
    
    public function processFilters() {
        $matches = [];
        preg_match_all('/\[([^\]]*)\]/', $this->getText(), $matches);
        
        #error_log(var_export($matches, true));
        
        if(!is_array($matches[1])) {
            # no shortcodes found
            return $this->getText();
        }

        $settingService = $this->getServiceLocator()
                ->get('ErsBase\Service\SettingService');
        $viewHelperManager = $this->getServiceLocator()->get('ViewHelperManager');
        
        $pattern = [];
        $replace = [];
        foreach($matches[1] as $value) {
            $valuePath = preg_split('/\./', $value);
            #error_log(var_export($valuePath, true));
            switch($valuePath[0]) {
                case 'ers':
                    #error_log('setting: '.$settingService->get(implode('.', $valuePath)));
                    $pattern[] = '/\['.implode('.', $valuePath).'\]/';
                    $replace[] = $settingService->get(implode('.', $valuePath));
                    break;
                case 'link':
                    $urlViewHelper = $viewHelperManager->get('url');
                    $order = $this->getObject('order');
                    $orderUrl = $urlViewHelper('order', array('action' => 'view', 'hashkey' => $order->getHashKey()), array('force_canonical' => true));;
                    $orderLink = '<a href="'.$orderUrl.'">your order</a>';

                    $pattern[] = '/\['.implode('.', $valuePath).'\]/';
                    $replace[] = $orderLink;
                    break;
                case 'partial':
                    $partialViewHelper = $viewHelperManager->get('partial');
                    $order = $this->getObject('order');
                    $resolver = $this->getServiceLocator()
                            ->get('Zend\View\Resolver\TemplatePathStack');
                    $template = 'partial/'.$valuePath[1].'.phtml';
                    if (false === $resolver->resolve($template)) {
                        throw new \Exception('Unable to find partial: '.$template);
                    }
                    $partialContent = $partialViewHelper($template, array('order' => $order));
                    
                    $pattern[] = '/\['.implode('.', $valuePath).'\]/';
                    $replace[] = $partialContent;
                    break;
                default:
                    $object = $this->getObject($valuePath[0]);
                    $method = 'get'.ucfirst($valuePath[1]);
                    if(method_exists($object, $method)) {
                        $data = $object->$method();
                        if($data instanceof \DateTime) {
                            $data = $data->format('d.m.Y');
                        }
                        #error_log('value: '.$data);
                        $pattern[] = '/\['.implode('.', $valuePath).'\]/';
                        $replace[] = $data;
                    }
            }
        }

        $text = preg_replace($pattern,$replace,$this->getText());
        $this->setText($text);
    }
    
    public function setObject($name, $object) {
        $this->object[$name] = $object;
    }
    public function getObject($name) {
        if(empty($this->object[$name])) {
           throw new \Exception('Unable to find object with name: '.$name); 
        }
        return $this->object[$name];
    }
}

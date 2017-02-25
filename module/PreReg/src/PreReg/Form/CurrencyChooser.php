<?php   

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace PreReg\Form;

use Zend\Form\Form;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\Session\Container;

class CurrencyChooser extends Form implements InputFilterProviderInterface
{    
    protected $em;
    protected $sm;
    public function getEntityManager() {
        return $this->em;
    }
    public function setEntityManager($em) {
        $this->em = $em;
    }
    
    public function setServiceLocator($sm) {
        $this->sm = $sm;
        
        return $this;
    }
    public function getServiceLocator() {
        return $this->sm;
    }


    public function __construct()
    {
        parent::__construct('currency-chooser');
        
        $this->setAttribute('method', 'post');
        
        $this->add(array(
            'name' => 'currency',
            'type'  => 'Zend\Form\Element\Select',
            'attributes' => array(
                'required' => 'required',
                'class' => 'form-control',
            ),
            'options' => array(
                /*'label' => _('Change Currency:'),
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),*/
            ),
        ));
 
        $this->add(array( 
            'name' => 'csrf', 
            'type' => 'Zend\Form\Element\Csrf', 
        )); 
        
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => _('Go'),
                'id' => 'submitbutton',
                'class' => 'btn btn-primary',
            ),
        ));
    }
    
    /**
     * Should return an array specification compatible with
     * {@link Zend\InputFilter\Factory::createInputFilter()}.
     *
     * @return array
     */
    public function getInputFilterSpecification()
    {
        return array(
            'currency' => array(
                'required' => true,
                'filters' => array( 
                    array('name' => 'Int'), 
                ), 
                'validators' => array(
                ),
            ),
            /*'price' => array(
                'required' => true,
                'validators' => array(
                    array(
                        'name' => 'Float',
                    ),
                ),
            ),*/
        );
    }
}

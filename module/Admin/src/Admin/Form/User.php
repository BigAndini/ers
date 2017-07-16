<?php   

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Form;

use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;

class User extends Form implements InputFilterProviderInterface
{
    protected $sm;
    
    public function setServiceLocator($sm) {
        $this->sm = $sm;
        
        return $this;
    }

    private function getServiceLocator() {
        return $this->sm;
    }
    
    public function __construct($sm = null)
    {
        $this->setServiceLocator($sm);
        
        parent::__construct('User');
        $this->setAttribute('method', 'post');
        
        $this->add(array(
            'name' => 'id',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
        
        $this->add(array( 
            'name' => 'firstname', 
            'type' => 'Zend\Form\Element\Text', 
            'attributes' => array( 
                'placeholder' => 'Firstname...', 
                'class' => 'form-control form-element',
            ), 
            'options' => array( 
                'label' => 'Firstname',
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ), 
        )); 
 
        $this->add(array( 
            'name' => 'surname', 
            'type' => 'Zend\Form\Element\Text', 
            'attributes' => array( 
                'placeholder' => 'Surname...', 
                'class' => 'form-control form-element',
            ), 
            'options' => array( 
                'label' => 'Surname', 
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ), 
        )); 
        
        $this->add(array( 
            'name' => 'email', 
            'type' => 'Zend\Form\Element\Text', 
            'attributes' => array( 
                'placeholder' => 'E-Mail Address...', 
                'class' => 'form-control form-element',
            ), 
            'options' => array( 
                'label' => 'E-Mail Address', 
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ), 
        )); 
        
        $this->add(array( 
            'name' => 'birthday', 
            #'type' => 'Zend\Form\Element\Date',
            #'type' => 'Zend\Form\Element\Text',
            'type' => 'PreReg\Form\Element\DateText',
            'attributes' => array( 
                'placeholder' => 'Birthday...', 
                'required' => 'required',
                'class' => 'form-control form-element datepicker',
                #'min' => '1900-01-01', 
                #'max' => 2015-08-09, 
                #'step' => '1', 
            ), 
            'options' => array( 
                'label' => 'Date of birth',
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
            ), 
        ));
        $this->get('birthday')->setFormat('d.m.Y');
 
        $this->add(array(
            'name' => 'Country_id',
            'type'  => 'Zend\Form\Element\Select',
            'attributes' => array(
                'required' => 'required',
                'class' => 'form-control form-element',
            ),
            'options' => array(
                'label' => 'Where are you from?',
                'label_attributes' => array(
                    'class'  => 'media-object',
                ),
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
                'value' => 'Go',
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
            'email' => array(
                'required' => false,
                'filters' => array( 
                    array('name' => 'StripTags'), 
                    array('name' => 'StringTrim'), 
                ), 
                'validators' => array(
                    array ( 
                        'name' => 'Callback', 
                        'options' => array(
                            'messages' => array(
                                \Zend\Validator\Callback::INVALID_VALUE => 'There is already a person with this email address.',
                            ),
                            'callback' => function($value, $context=array()) {
                                if($value == '' || $value == null) {
                                    return true;
                                }
                                
                                $entityManager = $this->getServiceLocator()
                                        ->get('Doctrine\ORM\EntityManager');
                                $user = $entityManager->getRepository('ErsBase\Entity\User')
                                        ->findOneBy(array('email' => $value));

                                # The email address is new -> ok
                                if (!$user) {
                                    return true;
                                }

                                # The email address belongs to the current user -> ok
                                if($user->getId() == $context['id']) {
                                    return true;
                                }
                                
                                # The email address belongs to another user -> not ok
                                return false;
                            },
                        ),
                    ),
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
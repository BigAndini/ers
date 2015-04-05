<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\DataTables;

use ZfTable\AbstractTable;
use ZfTable\Cell;

class Order extends AbstractTable
{

    protected $config = array(
        'name' => 'Doctrine',
        'showPagination' => true,
        'showQuickSearch' => false,
        'showItemPerPage' => true,
        'showColumnFilters' => true,
    );
    
    //Definition of headers
    protected $headers = array(
        'idcustomer' =>     array('tableAlias' => 'q', 'title' => 'Id', 'width' => '50') ,
        #'doctrine' =>       array('tableAlias' => 'q', 'title' => 'Doctrine closure' , 'filters' => 'text'),
        'Buyer_id' =>       array('tableAlias' => 'p', 'title' => 'Buyer_id' , 'filters' => 'text'),
        'PaymentType_id' => array('tableAlias' => 'p', 'title' => 'PaymentType_id' , 'filters' => 'text'),
        'Code_id' =>        array('tableAlias' => 'p', 'title' => 'Code_id' , 'filters' => 'text'),
        'product' =>        array('tableAlias' => 'p', 'title' => 'Product' , 'filters' => 'text'),
        'name' =>           array('tableAlias' => 'q', 'title' => 'Name' , 'filters' => 'text' ,'separatable' => true),
        'surname' =>        array('tableAlias' => 'q', 'title' => 'Surname' , 'filters' => 'text'),
        'street' =>         array('tableAlias' => 'q', 'title' => 'Street' , 'filters' => 'text'),
        'city' =>           array('tableAlias' => 'q', 'title' => 'City' , 'filters' => 'text' , 'separatable' => true),
        'active' =>         array('tableAlias' => 'q', 'title' => 'Active' , 'width' => 100 ),
        /*'created' =>        array('tableAlias' => 'q', 'title' => 'Created' , 'width' => 100 ),
        'updated' =>        array('tableAlias' => 'q', 'title' => 'Updated' , 'width' => 100 ),*/
    );

    public function init()
    {   
        /*$this->getHeader('created')->getCell()->addDecorator('closure', array(
            'closure' => function($context, $record) {
                return $record->created->format('d.m.Y H:i:s');
            }
        ));
        
        $this->getHeader('updated')->getCell()->addDecorator('closure', array(
            'closure' => function($context, $record) {
                return $record->updated->format('d.m.Y H:i:s');
            }
        ));*/
        
        /*$this->getHeader('doctrine')->getCell()->addDecorator('closure', array(
            'closure' => function($context, $record){
                return $record->name . ' ' . $record->surname;
            }
        ));
         
        $this->getHeader('product')->getCell()->addDecorator('closure', array(
            'closure' => function($context, $record){
            
                if(is_object($record->product)){
                    return $record->product->product;
                }else{
                    return '';
                }
            }
        ));*/
    }

    protected function initFilters($query)
    {
        if ($value = $this->getParamAdapter()->getValueOfFilter('name')) {
            $query->where("q.name like '%".$value."%' ");
        }
        if ($value = $this->getParamAdapter()->getValueOfFilter('surname')) {
            $query->where("q.surname like '%".$value."%' ");
        }
        if ($value = $this->getParamAdapter()->getValueOfFilter('doctrine')) {
            $query->where("q.name like '%".$value."%' OR q.surname like '%".$value."%' ");
        }
        if ($value = $this->getParamAdapter()->getValueOfFilter('street')) {
            $query->where("q.street like '%".$value."%' ");
        }
        if ($value = $this->getParamAdapter()->getValueOfFilter('city')) {
            $query->where("q.city like '%".$value."%' ");
        }
        if ($value = $this->getParamAdapter()->getValueOfFilter('product')) {
            $query->where("p.product like '%".$value."%' ");
        }
       
    }
}
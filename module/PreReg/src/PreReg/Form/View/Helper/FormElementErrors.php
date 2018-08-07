<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace PreReg\Form\View\Helper;

use Zend\Form\View\Helper\FormElementErrors as OriginalFormElementErrors;

class FormElementErrors extends OriginalFormElementErrors  
{
    #protected $messageCloseString     = '</li></ul>';
    protected $messageOpenFormat      = '<ul%s class="form-alert"><li >';
    #protected $messageSeparatorString = '</li><li class="form-error">';
}
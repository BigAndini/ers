<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

# copy this file to doctrine.local.php
# uncomment the return
# do additional changes in zf2logger.local.php

return array(
    'EddieJaoude\Zf2Logger' => array(

        // will add the $logger object before the current PHP error handler
        'registerErrorHandler'     => 'true', // errors logged to your writers
        'registerExceptionHandler' => 'true', // exceptions logged to your writers

        // do not log binary responses
        // mime types reference http://www.sitepoint.com/web-foundations/mime-types-complete-list/
        'doNotLog'                 => array(
            'mediaTypes' => array(
                'application/octet-stream',
                'image/png',
                'image/jpeg',
                'application/pdf'
            ),
        ),

        // multiple zend writer output & zend priority filters
        'writers' => array(
            'standard-file' => array(
                'adapter'  => '\Zend\Log\Writer\Stream',
                'options'  => array(
                    'output' => 'data/application-'.date('Y-m-d').'.log', // path to file
                ),
                // options: EMERG, ALERT, CRIT, ERR, WARN, NOTICE, INFO, DEBUG
                'filter' => \Zend\Log\Logger::INFO,
                'enabled' => true
            ),
            /*'tmp-file' => array(
                'adapter'  => '\Zend\Log\Writer\Stream',
                'options'  => array(
                    'output' => '/tmp/application-' . $_SERVER['SERVER_NAME'] . '.log', // path to file
                ),
                // options: EMERG, ALERT, CRIT, ERR, WARN, NOTICE, INFO, DEBUG
                'filter' => \Zend\Log\Logger::DEBUG,
                'enabled' => false
            ),*/
            /*'standard-output' => array(
                'adapter'  => '\Zend\Log\Writer\Stream',
                'options'  => array(
                    'output' => 'php://output'
                ),
                // options: EMERG, ALERT, CRIT, ERR, WARN, NOTICE, INFO, DEBUG
                'filter' => \Zend\Log\Logger::NOTICE,
                'enabled' => $_SERVER['APPLICATION_ENV'] == 'development' ? true : false
            ),*/
            'standard-error' => array(
                'adapter'  => '\Zend\Log\Writer\Stream',
                'options'  => array(
                    #'output' => 'php://stderr'
                    'output' => 'data/error-'.date('Y-m-d').'.log', // path to file
                ),
                // options: EMERG, ALERT, CRIT, ERR, WARN, NOTICE, INFO, DEBUG
                'filter' => \Zend\Log\Logger::NOTICE,
                'enabled' => true
            )
        )
    )
);
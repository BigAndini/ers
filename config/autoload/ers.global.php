<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

# copy this file to ers.local.php
# uncomment the return
# do additional changes in ers.local.php

/*return [
    'ERS' => [
        'sender_email'      => 'prereg@eja.net',
        'name_short'        => "EJC2016",
        'name_with_year'    => "EJC 2016",
        'name_with_number'  => "39th European Juggling Convention",
        'info_mail'         => "info@ejc2016.org",
        'website'           => "http://www.ejc2016.org",
        'website_faq'       => "http://www.ejc2017.org/en/lublin-site/teren-ejc-2017/faq/",
        'facebook'          => "https://www.facebook.com/EJC2017",
        'year'              => 2016,
        'start'             => new DateTime('2016-07-30'),
        'end'               => new DateTime('2016-08-07'),
        'registration_info' => 'http://prereg.eja.net',
        'onsitereg'         => 'https://prereg.eja.net/redirect',
        'operator'          => 'Stichting European Juggling Association',
    ],
    'environment' => 'develop',
    #'environment' => 'testing',
    #'environment' => 'production',
    'ERS\SEPA' => [
        'iban'      => 'NL84 INGB 0007 8721 92',
        'bic'       => 'INGBNL2A',
        'owner'     => 'STICHTING EUROPEAN JUGGLING ASSOCIATION',
        'bank'      => 'ING BANK N.V.',
        'country'   => 'Netherlands',
    ],
    'ERS\iPayment' => [
        'trxuser_id'    => '99999',
        'trx_currency'  => 'EUR',
        'trxpassword'   => '0',
        'sec_key'       => '1234567890',
        'action'        => 'https://ipayment.de/merchant/%account_id%/processor/2.0/',
    ],
    'orgHeiglPiwik' => [

        // Always omit a trailing slash!
        'server' => 'prereg.eja.net/analytics',
        'site_id' => 1,
    ],
];
*/
return array();
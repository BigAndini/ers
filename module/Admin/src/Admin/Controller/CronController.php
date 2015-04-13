<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use ersEntity\Entity;
use Zend\Console\Request as ConsoleRequest;

class CronController extends AbstractActionController {
    public function cronAction() {
        $request = $this->getRequest();
 
        // Make sure that we are running in a console and the user has not tricked our
        // application into running this action from a public web server.
        if (!$request instanceof ConsoleRequest){
            throw new \RuntimeException('You can only use this action from a console! Got this request from '.get_class($request));
        }
 
        // Get system service name  from console and check if the user used --verbose or -v flag
        #$doname   = $request->getParam('doname', false);
        #$verbose     = $request->getParam('verbose');
        
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $orderStatus = $em->getRepository("ersEntity\Entity\OrderStatus")
                ->findBy(array('value' => 'cron'));
        foreach($orderStatus as $status) {
            $em->remove($status);
        }
        $em->flush();
        
        $orders = $em->getRepository("ersEntity\Entity\Order")
                ->findBy(array(), array('created' => 'DESC'));
        
        $logger = $this
            ->getServiceLocator()
            ->get('Logger');
        $logger->info('We are in runCron of TestCron');
        
        $output = '';
        foreach($orders as $order) {
            if($order->hasOrderStatus('cron')) {
                continue;
            }
            $output .= $order->getCode()->getValue().PHP_EOL;
            $orderStatus = new Entity\OrderStatus();
            $orderStatus->setValue('cron');
            $orderStatus->setOrder($order);
            $em->persist($orderStatus);
            
            $em->flush();
        }
        
        $output .= 'ready!';
        /*
         * ensure a newline at the end of output.
         */
        $output .= PHP_EOL;
        return $output;
    }
    
    public function autoMatchingAction() {
        /*
         * Status of BankStatements
         * 1. unchecked
         * 2. notfound
         * 3. matched
         * 4. disabled
         */
        
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $statements = $em->getRepository("ersEntity\Entity\BankStatement")
                ->findAll();
        
        foreach($statements as $statement) {
            if($statement->getStatus() == 'matched') {
                continue;
            } elseif($statement->getStatus() == 'disabled') {
                continue;
            }
            $bankaccount = $statement->getBankAccount();
            
            $ret = $this->findCode($statement->getBankStatementcol9());
            if(is_array($ret)) {
                echo "found ".count($ret)." codes for statement: ".$statement->getId().PHP_EOL;
                $found = false;
                foreach($ret as $code) {
                    echo $code->getValue().PHP_EOL;
                    $order_code = $em->getRepository("ersEntity\Entity\Code")
                        ->findOneBy(array('value' => $code->getValue()));
                    if($order_code) {
                        echo "found code in system: ".$order_code->getValue().PHP_EOL;
                        $found = true;
                    }
                }
                if(!$found) {
                    echo "ERROR: Unable to find any code in system.".PHP_EOL;
                    echo $statement->getBankStatementcol9().PHP_EOL;
                }
            }
            echo "=============================================".PHP_EOL;
        }
    }
    
    private function findCode($string) {
        $length = 8;
        $matches = array();
        preg_match_all('/[A-Z0-9]{'.$length.'}/', $string, $matches);
        $ret = array();
        $code = new Entity\Code();
        foreach($matches as $values) {
            foreach($values as $value) {
                $code->setValue($value);
                if($code->checkCode()) {
                    
                    $ret[] = clone $code;
                }
            }
        }
        return $this->array_unique_callback($ret, function($code) { return $code->getValue(); });
    }
    
    private function array_unique_callback(array $arr, callable $callback, $strict = false) {
        return array_filter(
            $arr,
            function ($item) use ($strict, $callback) {
                static $haystack = array();
                $needle = $callback($item);
                if (in_array($needle, $haystack, $strict)) {
                    return false;
                } else {
                    $haystack[] = $needle;
                    return true;
                }
            }
        );
    }
    
    public function genUserListAction() {
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $users = $em->getRepository("ersEntity\Entity\User")
                ->findAll();
        
        $fp = fopen('/tmp/users.csv', 'w');

        foreach($users as $user) {
            if($user->getEmail() == '') {
                continue;
            }
            $tmp = array(
                'email' => $user->getEmail(),
                'firstname' => $user->getFirstname(),
                'surname' => $user->getSurname(),
            );
            fputcsv($fp, $tmp);
        }
        fclose($fp);
    }
}
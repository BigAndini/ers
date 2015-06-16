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
        
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $orderStatus = $em->getRepository("ersEntity\Entity\OrderStatus")
                ->findBy(array('value' => 'cron'));
        foreach($orderStatus as $status) {
            $em->remove($status);
        }
        $em->flush();
        
        $orders = $em->getRepository("ersEntity\Entity\Order")
                ->findBy(array(), array('created' => 'DESC'));
        
        $logger = $this->getServiceLocator()->get('Logger');
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
         * 1. new
         * 2. notfound
         * 3. matched
         * 4. disabled
         */
        
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $statements = $em->getRepository("ersEntity\Entity\BankStatement")
                ->findAll();
        
        $longest_match = 0;
        foreach($statements as $statement) {
            $time_start = microtime(true);
            if($statement->getStatus() == 'matched') {
                continue;
            } elseif($statement->getStatus() == 'disabled') {
                continue;
            }
            $bankaccount = $statement->getBankAccount();
            $statement_format = json_decode($bankaccount->getStatementFormat());
            
            # TODO: check if the statement_format is already set. If not move to next statement.
            
            if(isset($statement_format->sign->col) && isset($statement_format->sign->value) && $statement->getBankStatementColByNumber($statement_format->sign->col)->getValue() != $statement_format->sign->value) {
                # This is no positive statement. We will not check this.
                continue;
            }
            
            $ret = $this->findCodes($statement->getBankStatementColByNumber($statement_format->matchKey)->getValue());
            if(is_array($ret)) {
                $found = false;
                foreach($ret as $code) {
                    $order_code = $em->getRepository("ersEntity\Entity\Code")
                        ->findOneBy(array('value' => $code->getValue()));
                    if($order_code) {
                        $found = true;
                        $this->createMatch($statement, $order_code);
                    }
                }
                if(!$found) {
                    $ret = $this->findAllCodes($statement->getBankStatementColByNumber($statement_format->matchKey)->getValue());
                    if(is_array($ret)) {
                        $found = false;
                        foreach($ret as $code) {
                            $order_code = $em->getRepository("ersEntity\Entity\Code")
                                ->findOneBy(array('value' => $code->getValue()));
                            if($order_code) {
                                $found = true;
                                $this->createMatch($statement, $order_code);
                            }
                        }
                    }
                }
                if(!$found) {
                    echo PHP_EOL."ERROR: Unable to find any code in system.".PHP_EOL;
                    echo $statement->getBankStatementColByNumber($statement_format->matchKey)->getValue().PHP_EOL;
                }
                $time_end = microtime(true);
                $time = $time_end - $time_start;
                if($longest_match < $time) {
                    $longest_match = $time;
                }
            }
        }
        echo 'The longest match took '.$longest_match.' seconds.'.PHP_EOL;
    }
    
    private function createMatch(Entity\BankStatement $statement, Entity\Code $code) {
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $bankaccount = $statement->getBankAccount();
        $statement_format = json_decode($bankaccount->getStatementFormat());
        
        $order = $em->getRepository("ersEntity\Entity\Order")
            ->findOneBy(array('Code_id' => $code->getId()));
        $statement_amount = (float) $statement->getBankStatementColByNumber($statement_format->amount)->getValue();
        $order_amount = (float) $order->getSum();

        $statement->setStatus('matched');

        $em->persist($statement);

        $match = new Entity\Match();
        $match->setBankStatement($statement);
        $match->setOrder($order);
        $match->setStatus('active');
        $match->setComment('matched by auto-matching');

        /*
         * set andi as admin for auto-matching
         */
        $admin = $em->getRepository("ersEntity\Entity\User")
            ->findOneBy(array('id' => 1));
        $match->setAdmin($admin);

        $em->persist($match);

        if($order_amount == $statement_amount) {
            $paid = true;
            #echo "perfect match!".PHP_EOL;
            echo ".";
        } elseif($order_amount < $statement_amount) {
            $paid = true;
            #echo "overpaid, ok!".PHP_EOL;
            echo "!";
        } else {
            $paid = false;
            echo "-";
        }
        if($paid) {
            #$orderStatus = new Entity\OrderStatus();
            #$orderStatus->setOrder($order);
            #$orderStatus->setValue('paid');
            #$order->addOrderStatus($orderStatus);
            $order->setPaymentStatus('paid');
            
            foreach($order->getItems() as $item) {
                $item->setStatus('paid');
                $em->persist($item);
            }

            $em->persist($order);
            #$em->persist($orderStatus);
        } else {
            foreach($order->getItems() as $item) {
                $item->setStatus('ordered');
                $em->persist($item);
            }
        }
        $em->flush();
    }
    
    private function findCodes($string) {
        $length = 8;
        $matches = array();
        preg_match_all('/[A-Za-z0-9]{'.$length.'}/', $string, $matches);
        $ret = array();
        $code = new Entity\Code();
        foreach($matches as $values) {
            foreach($values as $value) {
                $code->setValue($value);
                if($code->checkCode()) {
                    $ret[] = clone $code;
                }
                
                $code->normalize();
                if($code->checkCode()) {
                    $ret[] = clone $code;
                }
            }
        }
        return $this->array_unique_callback($ret, function($code) { return $code->getValue(); });
    }
    
    private function findAllCodes($string) {
        $length = 8;
        $regex='/[A-Z0-9]{'.$length.'}/';
        $prepared_string = preg_replace('/\ /', '', $string);
        $matches = array();
        $codes = array();
        $ret = array();
        
        $offset = 0;
        $old_offset = 1;
        while($offset != $old_offset) {
            preg_match($regex, $prepared_string, $matches, PREG_OFFSET_CAPTURE, $offset);
            if(!isset($matches[0])) {
                break;
            }
            $codes[] = $matches[0][0];
            $old_offset = $offset;
            $offset = $matches[0][1]+1;
        }
        
        $code = new Entity\Code();
        foreach($codes as $value) {
            $code->setValue($value);
            if($code->checkCode()) {
                $ret[] = clone $code;
            }

            $code->normalize();
            if($code->checkCode()) {
                $ret[] = clone $code;
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
    
    public function removeMatchesAction() {
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $matches = $em->getRepository("ersEntity\Entity\Match")
                ->findAll();
        
        foreach($matches as $match) {
            $statement = $match->getBankStatement();
            $statement->setStatus('new');
            $em->persist($statement);
            $em->remove($match);
        }
        $em->flush();
    }
    
    public function genUserListAction() {
        $em = $this->getServiceLocator()
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
    
    public function generateEticketsAction() {
        $time_start = microtime();
        
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        #$order = $em->getRepository("ersEntity\Entity\Order")
                #->findOneBy(array('id' => '297'));
                #->findOneBy(array('id' => '12'));
                #->findOneBy(array('id' => '54'));
        
        $orders = $em->getRepository("ersEntity\Entity\Order")
                ->findAll();
        $count = 0;
        $eticketService = $this->getServiceLocator()
            ->get('PreReg\Service\ETicketService');
        
        foreach($orders as $order) {
            echo "You are using " . intval(memory_get_usage() / 1024 / 1024) ." MB". PHP_EOL;
            foreach($order->getPackages() as $package) {
                $eticketService->setPackage($package);
                $eticketService->generatePdf();
                $count++;
                $em->detach($package);
                #$em->flush();
                #$em->clear();
            }
            $em->detach($order);
        }
        echo "generated ".$count." etickets in ".(microtime()-$time_start).' $unit'.PHP_EOL;
    }
    
    public function updateOrdersAction() {
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $orders = $em->getRepository("ersEntity\Entity\Order")
                ->findAll();
                #->findBy(array('total_sum' => 0));
        #error_log('found '.count($orders).' orders');
        $count = 0;
        foreach($orders as $order) {
            #error_log($order->getId().' '.$order->getSum().' '.$order->getPrice());
            $order->setTotalSum($order->getSum());
            $order->setOrderSum($order->getPrice());
            if($order->getPaymentStatus() == 'paid') {
                $items = $order->getItems();
                #error_log('found '.count($items).' items');
                foreach($items as $item) {
                    $item->setStatus('paid');
                    $em->persist($item);
                }
            }
            $em->persist($order);
            $em->flush();
            /*if($count >= 100) {
                $em->flush();
                $count = 0;
            }*/
            $count++;
        }
    }
}
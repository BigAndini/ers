<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use ErsBase\Entity;
use ErsBase\Service;
use Zend\Console\Request as ConsoleRequest;

class CronController extends AbstractActionController {
    protected $debug = false;
    
    public function autoMatchingAction() {
        /*
         * Status of BankStatements
         * 1. new
         * 2. notfound
         * 3. matched
         * 4. disabled
         */
        
        $this->debug = false;
        
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $statements = $em->getRepository('ErsBase\Entity\BankStatement')
                ->findAll();
        
        if($this->debug) {
            echo "Phase 1: check ".count($statements)." statements".PHP_EOL.PHP_EOL;
        }
        $longest_match = 0;
        foreach($statements as $statement) {
            $time_start = microtime(true);
            if($statement->getStatus() == 'matched') {
                continue;
            } elseif($statement->getStatus() == 'disabled') {
                continue;
            }
            $bankaccount = $statement->getPaymentType();
            $statement_format = json_decode($bankaccount->getStatementFormat());
            
            # TODO: check if the statement_format is already set. If not move to next statement.
            
            if(isset($statement_format->sign->col) && isset($statement_format->sign->value) && $statement->getBankStatementColByNumber($statement_format->sign->col)->getValue() != $statement_format->sign->value) {
                # This is no positive statement. We will not check this.
                continue;
            }
            
            if(empty($statement_format->matchKey)) {
                echo 'WARNING: matchKey not set for bank account '.$bankaccount->getName().PHP_EOL;
                continue;
            }
            $ret = $this->findCodes($statement->getBankStatementColByNumber($statement_format->matchKey)->getValue());
            if(is_array($ret)) {
                $found = false;
                foreach($ret as $code) {
                    $order_code = $em->getRepository('ErsBase\Entity\Code')
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
                            $order_code = $em->getRepository('ErsBase\Entity\Code')
                                ->findOneBy(array('value' => $code->getValue()));
                            if($order_code) {
                                $found = true;
                                $this->createMatch($statement, $order_code);
                            }
                        }
                    }
                }
                if(!$found) {
                    echo "WARNING: Unable to find any code in system: ";
                    echo $statement->getBankStatementColByNumber($statement_format->matchKey)->getValue().PHP_EOL;
                }
                $time_end = microtime(true);
                $time = $time_end - $time_start;
                if($longest_match < $time) {
                    $longest_match = $time;
                }
            }
        }
        #echo 'INFO: The longest match took '.$longest_match.' seconds.'.PHP_EOL;
        
        /*
         * check status of unpaid orders
         * set orders to paid if statements have the correct value
         */
        $qb = $em->getRepository('ErsBase\Entity\Order')->createQueryBuilder('o');
        $qb->join('o.status', 's');
        $qb->where('s.value = :status');
        $qb->setParameter('status', 'ordered');
        
        $orders = $qb->getQuery()->getResult();
        
        if($this->debug) {
            echo PHP_EOL."Phase 2: check ".count($orders)." orders and set payment status.".PHP_EOL;
        }
        
        foreach($orders as $order) {
            $statement_amount = $order->getStatementAmount();
            $order_amount = $order->getSum();
            if($order_amount == $statement_amount) {
                $paid = true;
                if($this->debug) {
                    echo ".";
                }
                #echo "INFO: found match for order ".$order->getCode()->getValue()." ".$order_amount." <=> ".$statement_amount." (exact)".PHP_EOL;
            } elseif($order_amount < $statement_amount) {
                $paid = true;
                if($this->debug) {
                    echo "!";
                }
                #echo "INFO: found match for order ".$order->getCode()->getValue()." ".$order_amount." <=> ".$statement_amount." (overpaid)".PHP_EOL;
            } else {
                $paid = false;
                if($this->debug) {
                    echo "-";
                }
                #echo "INFO: found match for order ".$order->getCode()->getValue()." ".$order_amount." <=> ".$statement_amount." (partial)".PHP_EOL;
            }
            if($paid) {
                $statusPaid = $em->getRepository('ErsBase\Entity\Status')
                        ->findOneBy(array('value' => 'paid'));
                
                $order->setPaymentStatus('paid');
                $order->setStatus($statusPaid);

                foreach($order->getPackages() as $package) {
                    if($package->getStatus()->getValue() == 'ordered') {
                        $package->setStatus($statusPaid);
                        $em->persist($package);
                        foreach($package->getItems() as $item) {
                            if($item->getStatus()->getValue() == 'ordered') {
                                $item->setStatus($statusPaid);
                                $em->persist($item);
                            }
                        }
                    }
                }
                
                $em->persist($order);
            } else {
                $statusOrdered = $em->getRepository('ErsBase\Entity\Status')
                        ->findOneBy(array('value' => 'ordered'));
                foreach($order->getPackages() as $package) {
                    $package->setStatus($statusOrdered);
                    $em->persist($package);
                    foreach($package->getItems() as $item) {
                        $item->setStatus($statusOrdered);
                        $em->persist($item);
                    }
                }
                
            }
        }
        $em->flush();
        if($this->debug) {
            echo PHP_EOL.PHP_EOL."done.".PHP_EOL;
        }
    }
    
    /*
     * TODO: Move to MatchService
     */
    private function createMatch(Entity\BankStatement $statement, Entity\Code $code) {
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $bankaccount = $statement->getPaymentType();
        #$statement_format = json_decode($bankaccount->getStatementFormat());
        
        $order = $em->getRepository('ErsBase\Entity\Order')
            ->findOneBy(array('code_id' => $code->getId()));
        
        if(!$order) {
            # try to find order via package
            $package = $em->getRepository('ErsBase\Entity\Package')
                ->findOneBy(array('code_id' => $code->getId()));
            if(!$package) {
                # try to find item via package
                $item = $em->getRepository('ErsBase\Entity\Item')
                    ->findOneBy(array('code_id' => $code->getId()));
                if(!$item) {
                    echo "Unable to find neither item nor package nor order with code: ".$code->getValue().PHP_EOL;
                    exit();
                } else {
                    $order = $item->getPackage()->getOrder();
                }
            } else {
                $order = $package->getOrder();
            }
        }
        
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
        $admin = $em->getRepository('ErsBase\Entity\User')
            ->findOneBy(array('id' => 1));
        $match->setUser($admin);
        
        $order->addMatch($match);

        $em->persist($match);

        #$statement_amount = (float) $statement->getBankStatementColByNumber($statement_format->amount)->getValue();
        $statement_amount = $order->getStatementAmount();
        $order_amount = $order->getSum();
        $matchInfo = "INFO: found match for order ".$order->getCode()->getValue()." ".\number_format($order_amount, 2, ',', '.')." <=> ".\number_format($statement_amount, 2, ',', '.');
        if($order_amount == $statement_amount) {
            $paid = true;
            echo $matchInfo." (exact)".PHP_EOL;
        } elseif($order_amount < $statement_amount) {
            #$paid = true;
            $paid = false;
            echo $matchInfo." (overpaid)".PHP_EOL;
        } else {
            $paid = false;
            echo $matchInfo." (partial)".PHP_EOL;
        }
        if($paid) {
            $statusPaid = $em->getRepository('ErsBase\Entity\Status')
                    ->findOneBy(array('value' => 'paid'));
            
            $order->setPaymentStatus('paid');
            $order->setStatus($statusPaid);
        
            foreach($order->getPackages() as $package) {
                if($package->getStatus()->getValue() == 'ordered') {
                    $package->setStatus($statusPaid);
                    $em->persist($package);
                    foreach($package->getItems() as $item) {
                        if($item->getStatus()->getValue() == 'ordered') {
                            $item->setStatus($statusPaid);
                            $em->persist($item);
                        }
                    }
                }
            }
            
            $em->persist($order);
        } else {
            $statusOrdered = $em->getRepository('ErsBase\Entity\Status')
                    ->findOneBy(array('value' => 'ordered'));
            
            foreach($order->getPackages() as $package) {
                $package->setStatus($statusOrdered);
                $em->persist($package);
                foreach($package->getItems() as $item) {
                    $item->setStatus($statusOrdered);
                    $em->persist($item);
                }
            }
        }
        $em->flush();
    }
    
    /*
     * TODO: Move to MatchService
     */
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
    
    /*
     * TODO: Move to MatchService
     */
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
        
        $matches = $em->getRepository('ErsBase\Entity\Match')
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
        
        $users = $em->getRepository('ErsBase\Entity\User')
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
        
        #$order = $em->getRepository('ErsBase\Entity\Order')
                #->findOneBy(array('id' => '297'));
                #->findOneBy(array('id' => '12'));
                #->findOneBy(array('id' => '54'));
        
        $orders = $em->getRepository('ErsBase\Entity\Order')
                ->findAll();
        $count = 0;
        $eticketService = $this->getServiceLocator()
            ->get('ErsBase\Service\ETicketService');
        
        foreach($orders as $order) {
            echo "You are using " . intval(memory_get_usage() / 1024 / 1024) ." MB". PHP_EOL;
            foreach($order->getPackages() as $package) {
                $eticketService->setPackage($package);
                $eticketService->generatePdf();
                $count++;
                $em->detach($package);
            }
            $em->detach($order);
        }
        echo "generated ".$count." etickets in ".(microtime()-$time_start).' $unit'.PHP_EOL;
    }
    
    public function sendPaymentReminderAction() {
        $request = $this->getRequest();
        
        $long_real = (bool) $request->getParam('real',false);
        $short_real = (bool) $request->getParam('r',false);
        $isReal = ($long_real | $short_real);
        
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $qb1 = $em->getRepository('ErsBase\Entity\Order')->createQueryBuilder('o');
        $qb1->where($qb1->expr()->isNull('o.payment_reminder_status'));
        
        $correctOrders = $qb1->getQuery()->getResult();
        
        echo "found ".count($correctOrders)." orders to correct".PHP_EOL;
        foreach($correctOrders as $o) {
            $o->setPaymentReminderStatus(0);
            $em->persist($o);
        }
        $em->flush();
        
        #$limit = 3;
        $qb = $em->getRepository('ErsBase\Entity\Order')->createQueryBuilder('o');
        $qb->join('o.status', 's');
        $qb->where($qb->expr()->eq('s.value', ':status'));
        $qb->andWhere($qb->expr()->lt('o.created', ':paymentTarget'));
        $qb->andWhere($qb->expr()->eq('o.payment_reminder_status', ':prstatus'));
        $qb->setParameter('status', 'ordered');
        $paymentTarget = new \DateTime;
        $paymentTarget->modify( '-10 days' );
        $qb->setParameter('paymentTarget', $paymentTarget);
        $qb->setParameter('prstatus', '0');
        #$qb->setFirstResult( $offset )
        #$qb->setMaxResults( $limit );
        
        $notPaidOrders = $qb->getQuery()->getResult();
        echo count($notPaidOrders)." not paid orders found from before ".$paymentTarget->format('d.m.Y').".".PHP_EOL;
        
        if(!$isReal) {
            echo "Use -r parameter to really send out all payment reminder.".PHP_EOL;
            exit();
        }
        
        # countdown
        echo PHP_EOL;
        for($i=10; $i > 0; $i--) {
            echo "Really sending out payment reminder in... ".$i." seconds (ctrl+c to abort)   \r";
            sleep(1);
        }
        echo PHP_EOL;
        
        $config = $this->getServiceLocator()
                        ->get('config');
        
        foreach($notPaidOrders as $order) {
            # prepare email (participant, buyer)
            $emailService = $this->getServiceLocator()
                    ->get('ErsBase\Service\EmailService');
            $config = $this->getServiceLocator()
                    ->get('config');
            $emailService->setFrom($config['ERS']['info_mail']);

            /*** real buyer ***/
            #$buyer = $order->getBuyer();
            #$emailService->addTo($buyer);
            /***/
            /*** test buyer **/
            $user = new Entity\User();
            $user->setEmail('andi'.$order->getCode()->getValue().'@inbaz.org');
            $emailService->addTo($user);
            /***/

            $bcc = new Entity\User();
            $bcc->setEmail($config['ERS']['info_mail']);
            $emailService->addBcc($bcc);

            $subject = "[".$config['ERS']['name_short']."] "._('Payment reminder for your order:')." ".$order->getCode()->getValue();
            $emailService->setSubject($subject);

            $viewModel = new ViewModel(array(
                'order' => $order,
            ));
            $viewModel->setTemplate('email/payment-reminder.phtml');
            $viewRender = $this->getServiceLocator()->get('ViewRenderer');
            $html = $viewRender->render($viewModel);

            $emailService->setHtmlMessage($html);

            $emailService->send();
            
            $order->setPaymentReminderStatus($order->getPaymentReminderStatus()+1);
            $em->persist($order);
            $em->flush();
            
            echo "sent payment reminder for order ".$order->getCode()->getValue().PHP_EOL;
            \sleep(5);
        }
    }
    
    public function sendEticketsAction() {
        $request = $this->getRequest();
        
        $long_real = (bool) $request->getParam('real',false);
        $short_real = (bool) $request->getParam('r',false);
        $isReal = ($long_real | $short_real);
        
        $long_debug = (bool) $request->getParam('debug',false);
        $short_debug = (bool) $request->getParam('d',false);
        $isDebug = ($long_debug | $short_debug);
        
        $long_count = (int) $request->getParam('count',false);
        if(is_numeric($long_count) && $long_count != 0) {
            $ticket_count = $long_count;
        }
        
        $short_count = (int) $request->getParam('c',false);
        if(is_numeric($short_count) && $short_count != 0 ) {
            $ticket_count = $short_count;
        }
        
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        # correct package ticket_status
        $qb = $em->getRepository('ErsBase\Entity\Package')->createQueryBuilder('p');
        $qb->where('p.ticket_status IS NULL');
        $qb->orWhere("p.ticket_status = 'not_send'");
        $noStatusPackages = $qb->getQuery()->getResult();
        if($isDebug) {
            echo count($noStatusPackages)." packages need to be corrected.".PHP_EOL;
        }
        foreach($noStatusPackages as $package) {
            $order = $package->getOrder();
            if($order->getStatus()->getValue() == 'order pending') {
                continue;
            }
            $productId = array(
                '1' => 0,
            );
            foreach($package->getItems() as $item) {
                if($item->getStatus() == 'cancelled') {
                    continue;
                }
                if($item->getStatus() == 'transferred') {
                    continue;
                }
                if(!isset($productId[$item->getProductId()])) {
                    $productId[$item->getProductId()] = 1;
                } else {
                    $productId[$item->getProductId()]++;
                }
            }
            if($productId[1] > 1) {
                $order = $package->getOrder();
                echo "Found more than one week ticket in package ".$package->getCode()->getValue()." (order: ".$order->getCode()->getValue().").".PHP_EOL;
            }
            # if the package status is paid and
            # there is only one week ticket in this package
            # or this package doesn't contain no week ticket
            if($package->getStatus() == 'paid') {
                if($productId[1] == 1 || $productId[1] == 0) {
                    $package->setTicketStatus('can_send');
                } else {
                    $package->setTicketStatus('block_send');
                }
            # if the package is not paid
            } else {
                $package->setTicketStatus('not_send');
            }
            $em->persist($package);
        }
        $em->flush();
        
        if(empty($ticket_count) || !is_numeric($ticket_count)) {
            $ticket_count = 100;
            #$ticket_count = 3;
        }
        $can_send_packages = $em->getRepository('ErsBase\Entity\Package')
            ->findBy(array('ticket_status' => 'can_send'));
        if($isDebug) {
            echo "Can send out e-tickets for ".count($can_send_packages)." packages, will process ".$ticket_count." now.".PHP_EOL;
        }
        
        $packages = $em->getRepository('ErsBase\Entity\Package')
            ->findBy(array('ticket_status' => 'can_send'), array(), $ticket_count);
        
        if(!$isReal) {
            echo "Use -r parameter to really send out all etickets.".PHP_EOL;
            exit();
        }
        
        /*echo PHP_EOL;
        for($i=10; $i > 0; $i--) {
            echo "Really sending out e-tickets in... ".$i." seconds (ctrl+c to abort)   \r";
            sleep(1);
        }
        echo PHP_EOL;*/
        
        $config = $this->getServiceLocator()
                        ->get('config');
        
        foreach($packages as $package) {
            # prepare email (participant, buyer)
            #$emailService = new Service\EmailService();
            $emailService = $this->getServiceLocator()
                        ->get('ErsBase\Service\EmailService');
            $emailService->setFrom($config['ERS']['info_mail']);

            $order = $package->getOrder();
            $participant = $package->getParticipant();

            /*** remove last slash to comment ***/
            $buyer = $order->getBuyer();
            if($participant->getEmail() == '') {
                $emailService->addTo($buyer);
            } elseif($participant->getEmail() == $buyer->getEmail()) {
                $emailService->addTo($buyer);
            } else {
                $emailService->addTo($participant);
                $emailService->addCc($buyer);
            }
            /*** remove leading slash to comment ***/
            /*** remove last slash to comment ***
            $user = new Entity\User();
            $user->setEmail('andi'.$package->getCode()->getValue().'@inbaz.org');
            $emailService->addTo($user);
            /*** remove leading slash to comment ***/
            
            $bcc = new Entity\User();
            $bcc->setEmail($config['ERS']['info_mail']);
            $emailService->addBcc($bcc);

            $subject = "[".$config['ERS']['name_short']."] "._('E-Ticket for')." ".$participant->getFirstname()." ".$participant->getSurname()." (order ".$order->getCode()->getValue().")";
            $emailService->setSubject($subject);

            $viewModel = new ViewModel(array(
                'package' => $package,
            ));
            $viewModel->setTemplate('email/eticket-participant.phtml');
            $viewRenderer = $this->getServiceLocator()->get('ViewRenderer');
            $html = $viewRenderer->render($viewModel);

            $emailService->setHtmlMessage($html);

            # generate e-ticket pdf
            $eticketService = $this->getServiceLocator()
                ->get('ErsBase\Service\ETicketService');

            $eticketService->setLanguage('en');
            $eticketService->setPackage($package);
            $eticketFile = $eticketService->generatePdf();

            echo ob_get_clean();
            if($isDebug) {
                echo "generated e-ticket ".$eticketFile.".".PHP_EOL;
            }

            $emailService->addAttachment($eticketFile);
            
            # send out email
            $emailService->send();
            
            $package->setTicketStatus('send_out');
            $em->persist($package);
            $em->flush();
        }
    }
    
    public function emailStatusAction() {
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $users = $em->getRepository('ErsBase\Entity\User')
                ->findAll();
        
        echo "checking ".count($users)." emails".PHP_EOL;
        foreach($users as $user) {
            if($user->getEmail() == '') {
                continue;
            }
            
            echo "checking email status for ".$user->getEmail()."... ";
            $result = $this->validateEmail($user->getEmail());
            if($result) {
                echo "OK!".PHP_EOL;
                $user->setEmailStatus('ok');
            } else {
                echo "failed!".PHP_EOL;
                $user->setEmailStatus('fail');
            }
            $em->persist($user);
            $em->flush();
        }
    }
    
    private function validateEmail($email){
        list($name,$Domain) = split('@',$email);
        $result=getmxrr($Domain,$POFFS);
        if(!$result){
            $POFFS[0]=$Domain;
        }
        $timeout=5;
        $oldErrorLevel=error_reporting(!E_WARNING);
        $result=false;
        foreach($POFFS as $PO)
        {
            $sock = fsockopen($PO, 25, $errno, $errstr,  $timeout);
            if($sock){
                
                fwrite($sock, "HELO inbaz.org\n");
                $response = $this->getSockResponse($sock);
                fwrite($sock, "MAIL FROM: <prereg@inbaz.org>\n");
                $response = $this->getSockResponse($sock);
                fwrite($sock, "RCPT TO: <".$email.">\n");
                $response = $this->getSockResponse($sock);
                list($code,$msg)=explode(' ',$response);
                fwrite($sock, "RSET\n");
                $response = $this->getSockResponse($sock);
                fwrite($sock, "quit\n");
                fclose($sock);
                if ($code == '250') {
                    $result= true;
                    break;
                }
            }
        }
        error_reporting($oldErrorLevel);
        return $result;
    }
    
    private function getSockResponse($sock){
        $response="";
        while(substr($response,-2)!=="\r\n") {
            $data=fread($sock,4096);
            if($data=="")break;
            $response .=$data;
        }
        return $response;
    }
    
    public function itemAgegroupAction() {
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $qb = $em->getRepository('ErsBase\Entity\Item')->createQueryBuilder('i');
        $qb->where('i.agegroup IS NULL');
        $items = $qb->getQuery()->getResult();
        /*$items = $em->getRepository('ErsBase\Entity\Item')
                ->findAll();*/
        
        echo "checking ".count($items)." items".PHP_EOL;
        
        $count = 0;
        foreach($items as $item) {
            $package = $item->getPackage();
            
            $agegroupService = $this->getServiceLocator()
                    ->get('ErsBase\Service\AgegroupService:price');
            switch($item->getProductId()) {
                case 1:
                    # week ticket
                    $participant = $package->getParticipant();
                    $agegroup = $agegroupService->getAgegroupByDate($participant->getBirthday());
                    
                    if($agegroup) {
                        $item->setAgegroup($agegroup->getAgegroup());
                        $em->persist($item);

                        foreach($item->getChildItems() as $cItem) {
                            $cItem->setAgegroup($agegroup->getAgegroup());
                            $em->persist($cItem);
                        }
                    }
                    break;
                case 4:
                    # day ticket
                    $participant = $package->getParticipant();
                    $agegroup = $agegroupService->getAgegroupByDate($participant->getBirthday());
                    
                    if($agegroup) {
                        $item->setAgegroup($agegroup->getAgegroup());
                        $em->persist($item);
                    }
                    break;
                case 5:
                    # gala-show ticket
                    if($item->getPrice() != 0) {
                        # gala-show ticket for 0 € are handled by the week ticket case
                        echo "This is a gala show ticket for ".$item->getPrice()." € (".$item->getId().")".PHP_EOL;
                        $count += 1;
                    }
                    break;
                default:
                    echo "Don't know what to do with product id ".$item->getProductId().", yet.".PHP_EOL;
                    break;
            }
        }
        $em->flush();
        
        echo "found ".$count." items with no owner".PHP_EOL;
    }
    
    public function calcSumsAction() {
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $qb = $em->getRepository('ErsBase\Entity\Order')->createQueryBuilder('o');
        $qb->join('o.status', 's', 'WITH', 's.active = 1');
        $qb->where($qb->expr()->neq('s.value', ':status'));
        $qb->setParameter('status', 'order pending');
        
        $orders = $qb->getQuery()->getResult();
        echo 'found '.count($orders).' orders'.PHP_EOL;
        
        foreach($orders as $order) {
            #echo $order->getCode()->getValue().PHP_EOL;
            $orig_total_sum = $order->getTotalSum();
            $orig_order_sum = $order->getOrderSum();
            $order->getTotalSumEur();
            $order->getOrderSumEur();
            
            if($orig_order_sum != $order->getPrice()) {
                $order->setOrderSum($order->getPrice());
                #echo "update order sum for ".$order->getCode()->getValue().": ".$orig_order_sum." != ".$order->getPrice().PHP_EOL;
            }
            if($orig_total_sum != $order->getSum()) {
                $order->setTotalSum($order->getSum());
                #echo "update total sum for ".$order->getCode()->getValue().": ".$orig_total_sum." != ".$order->getSum().PHP_EOL;
            }
            
            $em->persist($order);
        }
        $em->flush();
    }
    
    public function cleanupUserAction() {
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $qb = $em->getRepository('ErsBase\Entity\User')->createQueryBuilder('u');
        $qb->leftJoin('u.roles', 'r');
        $qb->where($qb->expr()->isNull('r.roleId'));
        $qb->andWhere($qb->expr()->lt('u.updated', ':updated'));
        $updated = new \DateTime();
        $updated->sub(new \DateInterval('PT2H'));
        $qb->setParameter('updated', $updated);
        $users = $qb->getQuery()->getResult();
        echo 'found '.count($users).' users'.PHP_EOL;
        foreach($users as $user) {
            foreach($user->getOrders() as $order) {
                if($order->getStatus()->getValue() != 'order pending') {
                    echo "ERROR: found order ".$order->getId()." which may not be pending. (".$order->getStatus()->getValue().") (user_id: ".$user->getId().")".PHP_EOL;
                } else {
                    $em->remove($order);
                }
            }
            foreach($user->getPackages() as $package) {
                foreach($package->getItems() as $item) {
                    $em->remove($item);
                }
                $em->remove($package);
            }
            $em->remove($user);
        }
        $em->flush();
        
        
    }
    
    public function cleanupOrderAction() {
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $qb = $em->getRepository('ErsBase\Entity\Order')->createQueryBuilder('o');
        $qb->join('o.status', 's');
        $qb->where($qb->expr()->eq('s.value', ':status'));
        $qb->andWhere($qb->expr()->lt('o.updated', ':updated'));
        
        $updated = new \DateTime();
        $updated->sub(new \DateInterval('PT2H'));
        $qb->setParameter('updated', $updated);
        $qb->setParameter('status', 'order pending');
        
        $orders = $qb->getQuery()->getResult();
        echo 'found '.count($orders).' orders'.PHP_EOL;
        foreach($orders as $order) {
            $em->remove($order);
            foreach($order->getPackages() as $package) {
                $em->remove($package);
                foreach($package->getItems() as $item) {
                    $em->remove($item);
                }
            }
        }
        $em->flush();
    }
    
    public function correctStatusAction() {
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $orders = $em->getRepository('ErsBase\Entity\Order')
                ->findAll();
        $statusService = $this->getServiceLocator()
                ->get('ErsBase\Service\StatusService');
        
        foreach($orders as $order) {
            $statusService->setOrderStatus($order, $order->getStatus(), false);
            
            $order->setTotalSum($order->getSum());
            $order->setOrderSum($order->getPrice());
            $em->persist($order);
        }
        $em->flush();
    }
    
    public function correctActiveUserAction() {
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $users = $em->getRepository('ErsBase\Entity\User')
                ->findAll();
        foreach($users as $user) {
            foreach($user->getPackages() as $package) {
                $order = $package->getOrder();
                if($order->getStatus()->getActive()) {
                    $user->setActive(true);
                    $em->persist($user);
                }
            }
        }
        $em->flush();
    }
    
    public function correctPaidPackagesAction() {
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $qb = $em->getRepository('ErsBase\Entity\Package')->createQueryBuilder('p');
        $qb->join('p.status', 's');
        $qb->where($qb->expr()->eq('s.value', ':status'));
        $qb->setParameter('status', 'paid');
        
        $packages = $qb->getQuery()->getResult();
        
        foreach($packages as $package) {
            $order = $package->getOrder();
            if($order->getStatus()->getValue() != 'paid') {
                $status = $package->getStatus()->getValue();
                $package->setStatus($order->getStatus());
                echo $package->getCode()->getValue().": package status was: ".$status."; package status is: ".$package->getStatus().PHP_EOL;
                $em->persist($package);
            }
        }
        
        $em->flush();
    }
    
    public function correctOrderedPackagesAction() {
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $qb = $em->getRepository('ErsBase\Entity\Package')->createQueryBuilder('p');
        $qb->join('p.status', 's');
        $qb->where($qb->expr()->eq('s.value', ':status'));
        $qb->setParameter('status', 'ordered');
        
        $packages = $qb->getQuery()->getResult();
        
        foreach($packages as $package) {
            $order = $package->getOrder();
            if($order->getStatus()->getValue() == 'paid') {
                $status = $package->getStatus()->getValue();
                $package->setStatus($order->getStatus());
                echo $package->getCode()->getValue().": package status was: ".$status."; package status is: ".$package->getStatus().PHP_EOL;
                $em->persist($package);
            }
        }
        
        $em->flush();
    }
    
    public function correctItemStatusAction() {
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $items = $em->getRepository('ErsBase\Entity\Item')->findAll();
        
        foreach($items as $item) {
            $package = $item->getPackage();
            if($item->getStatus()->getValue() == 'ordered' || $item->getStatus()->getValue() == 'paid' || $item->getStatus()->getValue() == 'order pending') {
                $status = $item->getStatus();
                $item->setStatus($package->getStatus());
                echo $item->getCode()->getValue().": item status was: ".$status."; item status is: ".$item->getStatus().PHP_EOL;
                $em->persist($item);
            }
        }
        
        $em->flush();
    }
    
    public function sorryEticketSepaAction() {
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $qb = $em->getRepository('ErsBase\Entity\Package')->createQueryBuilder('p');
        $qb->join('p.status', 's');
        $qb->join('p.order', 'o');
        $qb->join('o.paymentType', 'pt');
        $qb->where($qb->expr()->eq('p.ticket_status', ':ticket_status'));
        $qb->andWhere($qb->expr()->eq('s.value', ':status'));
        $qb->andWhere($qb->expr()->eq('pt.id', ':payment_type'));
        
        $qb->setParameter('ticket_status', 'send_out');
        $qb->setParameter('status', 'ordered');
        $qb->setParameter('payment_type', '1');
        
        $packages = $qb->getQuery()->getResult();
        echo "found ".count($packages)." packages.".PHP_EOL;
        
        $config = $emailService = $this->getServiceLocator()
                        ->get('config');
        
        foreach($packages as $package) {
            # prepare email (participant, buyer)
            #$emailService = new Service\EmailService();
            $emailService = $this->getServiceLocator()
                        ->get('ErsBase\Service\EmailService');
            $emailService->setFrom($config['ERS']['info_mail']);

            $order = $package->getOrder();
            $participant = $package->getParticipant();

            $buyer = $order->getBuyer();
            $emailService->addTo($buyer);
            /*if($participant->getEmail() == '') {
                $emailService->addTo($buyer);
            } elseif($participant->getEmail() == $buyer->getEmail()) {
                $emailService->addTo($buyer);
            } else {
                $emailService->addTo($participant);
                $emailService->addCc($buyer);
            }*/
            /*$user = new Entity\User();
            $user->setEmail('andi@inbaz.org');
            $emailService->addTo($user);*/
            
            $bcc = new Entity\User();
            $bcc->setEmail($config['ERS']['info_mail']);
            $emailService->addBcc($bcc);

            $subject = "[".$config['ERS']['name_short']."] Your E-Ticket is not valid for ".$participant->getFirstname()." ".$participant->getSurname()." (order ".$order->getCode()->getValue().")";
            $emailService->setSubject($subject);

            $viewModel = new ViewModel(array(
                'package' => $package,
                'config' => $this->getServiceLocator()->get('config'),
            ));
            $viewModel->setTemplate('email/eticket-not-valid-sepa.phtml');
            $viewRenderer = $this->getServiceLocator()->get('ViewRenderer');
            $html = $viewRenderer->render($viewModel);

            $emailService->setHtmlMessage($html);
            
            # send out email
            $emailService->send();
            echo "email sent to ".$buyer->getEmail().".".PHP_EOL;
            
            $package->setTicketStatus('not_send');
            $em->persist($package);
            $em->flush();
        }
    }
    public function sorryEticketCcAction() {
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $qb = $em->getRepository('ErsBase\Entity\Package')->createQueryBuilder('p');
        $qb->join('p.status', 's');
        $qb->join('p.order', 'o');
        $qb->join('o.paymentType', 'pt');
        $qb->where($qb->expr()->eq('p.ticket_status', ':ticket_status'));
        $qb->andWhere($qb->expr()->eq('s.value', ':status'));
        $qb->andWhere($qb->expr()->eq('pt.id', ':payment_type'));
        
        $qb->setParameter('ticket_status', 'send_out');
        $qb->setParameter('status', 'ordered');
        $qb->setParameter('payment_type', '2');
        
        $packages = $qb->getQuery()->getResult();
        echo "found ".count($packages)." packages.".PHP_EOL;
        
        $config = $emailService = $this->getServiceLocator()
                        ->get('config');
        
        foreach($packages as $package) {
            # prepare email (participant, buyer)
            #$emailService = new Service\EmailService();
            $emailService = $this->getServiceLocator()
                        ->get('ErsBase\Service\EmailService');
            $emailService->setFrom($config['ERS']['info_mail']);

            $order = $package->getOrder();
            $participant = $package->getParticipant();

            $buyer = $order->getBuyer();
            $emailService->addTo($buyer);
            /*if($participant->getEmail() == '') {
                $emailService->addTo($buyer);
            } elseif($participant->getEmail() == $buyer->getEmail()) {
                $emailService->addTo($buyer);
            } else {
                $emailService->addTo($participant);
                $emailService->addCc($buyer);
            }*/
            /*$user = new Entity\User();
            $user->setEmail('andi@inbaz.org');
            $emailService->addTo($user);*/
            
            $bcc = new Entity\User();
            $bcc->setEmail($config['ERS']['info_mail']);
            $emailService->addBcc($bcc);

            $subject = "[".$config['ERS']['name_short']."] Your E-Ticket is not valid for ".$participant->getFirstname()." ".$participant->getSurname()." (order ".$order->getCode()->getValue().")";
            $emailService->setSubject($subject);

            $viewModel = new ViewModel(array(
                'package' => $package,
                'config' => $this->getServiceLocator()->get('config'),
            ));
            $viewModel->setTemplate('email/eticket-not-valid-cc.phtml');
            $viewRenderer = $this->getServiceLocator()->get('ViewRenderer');
            $html = $viewRenderer->render($viewModel);

            $emailService->setHtmlMessage($html);
            
            # send out email
            $emailService->send();
            echo "email sent to ".$buyer->getEmail().".".PHP_EOL;
            
            $package->setTicketStatus('not_send');
            $em->persist($package);
            $em->flush();
        }
    }
}
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
use Zend\Stdlib\Parameters;

class CronController extends AbstractActionController {
    protected $debug = false;
    protected $params;

    public function setParams($params = array()) {
        #$this->params = new Parameters();
        $this->params = $params;
        #var_export($this->params);
    }
    
    public function getParam($name, $default = null)
    {
        #return $this->params->get($name, $default);
    }
    
    public function consoledefaultAction() {
        $this->request = $this->getRequest();
        $params = $this->getRequest()->getParams()->toArray();
        $methodName = array_shift($params);
        
        $this->setParams($params);
        
        $method = $this->getMethodFromAction($methodName);
        if(!method_exists($this, $method)) {
            throw new \Exception('Unable to find method: '.$method);
        }
        
        $this->$method();
    }
    
    public function autoMatchingAction() {
        /*
         * Status of BankStatements
         * 1. new
         * 2. notfound
         * 3. matched
         * 4. disabled
         */
        
        $this->debug = true;
        
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $statements = $entityManager->getRepository('ErsBase\Entity\BankStatement')
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
                    $order_code = $entityManager->getRepository('ErsBase\Entity\Code')
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
                            $order_code = $entityManager->getRepository('ErsBase\Entity\Code')
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
        $queryBuilder = $entityManager->getRepository('ErsBase\Entity\Order')->createQueryBuilder('o');
        $queryBuilder->join('o.status', 's');
        $queryBuilder->where('s.value = :status');
        $queryBuilder->setParameter('status', 'ordered');
        
        $orders = $queryBuilder->getQuery()->getResult();
        
        if($this->debug) {
            echo PHP_EOL."Phase 2: check ".count($orders)." orders and set payment status.".PHP_EOL;
        }
        
        $statusPaid = $entityManager->getRepository('ErsBase\Entity\Status')
                        ->findOneBy(array('value' => 'paid'));
        $statusPartlyPaid = $entityManager->getRepository('ErsBase\Entity\Status')
                        ->findOneBy(array('value' => 'partly paid'));
        $statusOverpaid = $entityManager->getRepository('ErsBase\Entity\Status')
                        ->findOneBy(array('value' => 'overpaid'));
        $statusOrdered = $entityManager->getRepository('ErsBase\Entity\Status')
                        ->findOneBy(array('value' => 'ordered'));
        
        foreach($orders as $order) {
            $statement_amount = $order->getStatementAmount();
            $order_amount = $order->getSum();
            if($order_amount == $statement_amount) {
                
                $order->setPaymentStatus('paid');
                $order->setStatus($statusPaid);

                foreach($order->getPackages() as $package) {
                    if($package->getStatus()->getValue() == 'ordered') {
                        $package->setStatus($statusPaid);
                        $entityManager->persist($package);
                        foreach($package->getItems() as $item) {
                            if($item->getStatus()->getValue() == 'ordered') {
                                $item->setStatus($statusPaid);
                                $entityManager->persist($item);
                            }
                        }
                    }
                }
                
                $entityManager->persist($order);
                
                $paid = true;
                if($this->debug) {
                    echo ".";
                }
                #echo "INFO: found match for order ".$order->getCode()->getValue()." ".$order_amount." <=> ".$statement_amount." (exact)".PHP_EOL;
            } elseif($order_amount < $statement_amount) {
                
                $order->setPaymentStatus('overpaid');
                $order->setStatus($statusOverpaid);

                foreach($order->getPackages() as $package) {
                    if($package->getStatus()->getValue() == 'ordered') {
                        $package->setStatus($statusPaid);
                        $entityManager->persist($package);
                        foreach($package->getItems() as $item) {
                            if($item->getStatus()->getValue() == 'ordered') {
                                $item->setStatus($statusOverpaid);
                                $entityManager->persist($item);
                            }
                        }
                    }
                }
                
                $entityManager->persist($order);
                
                $paid = true;
                if($this->debug) {
                    echo "!";
                }
                #echo "INFO: found match for order ".$order->getCode()->getValue()." ".$order_amount." <=> ".$statement_amount." (overpaid)".PHP_EOL;
            } else {
                $order->setPaymentStatus('unpaid');
                $order->setStatus($statusOrdered);

                foreach($order->getPackages() as $package) {
                    $package->setStatus($statusOrdered);
                    $entityManager->persist($package);
                    foreach($package->getItems() as $item) {
                        $item->setStatus($statusOrdered);
                        $entityManager->persist($item);
                    }
                }
                
                $entityManager->persist($order);
                
                $paid = false;
                if($this->debug) {
                    echo "-";
                }
                #echo "INFO: found match for order ".$order->getCode()->getValue()." ".$order_amount." <=> ".$statement_amount." (partial)".PHP_EOL;
            }
        }
        $entityManager->flush();
        if($this->debug) {
            echo PHP_EOL.PHP_EOL."done.".PHP_EOL;
        }
    }
    
    /*
     * TODO: Move to MatchService
     */
    private function createMatch(Entity\BankStatement $statement, Entity\Code $code) {
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $order = $entityManager->getRepository('ErsBase\Entity\Order')
            ->findOneBy(array('code_id' => $code->getId()));
        
        if(!$order) {
            # try to find order via package
            $package = $entityManager->getRepository('ErsBase\Entity\Package')
                ->findOneBy(array('code_id' => $code->getId()));
            if(!$package) {
                # try to find item via package
                $item = $entityManager->getRepository('ErsBase\Entity\Item')
                    ->findOneBy(array('code_id' => $code->getId()));
                if(!$item) {
                    throw new \Exception("Unable to find neither item nor package nor order with code: ".$code->getValue());
                }
                #$order = $item->getPackage()->getOrder();
                $package = $item->getPackage();
            }
            $order = $package->getOrder();
        }
        
        $statement->setStatus('matched');

        $entityManager->persist($statement);

        $match = new Entity\Match();
        $match->setBankStatement($statement);
        $match->setOrder($order);
        $match->setStatus('active');
        $match->setComment('matched by auto-matching');

        /*
         * set andi as admin for auto-matching
         */
        $admin = $entityManager->getRepository('ErsBase\Entity\User')
            ->findOneBy(array('id' => 1));
        $match->setUser($admin);
        
        $order->addMatch($match);

        $entityManager->persist($match);

        #$statement_amount = (float) $statement->getBankStatementColByNumber($statement_format->amount)->getValue();
        $statement_amount = $order->getStatementAmount();
        $order_amount = $order->getSum();
        $matchInfo = "INFO: found match for order ".$order->getCode()->getValue()." ".\number_format($order_amount, 2, ',', '.')." <=> ".\number_format($statement_amount, 2, ',', '.');
        
        
        $statusPaid = $entityManager->getRepository('ErsBase\Entity\Status')
                        ->findOneBy(array('value' => 'paid'));

        if(!$statusPaid) {
            throw new \Exception('Unable to find status paid, please create this status.');
        }
        $statusPartlyPaid = $entityManager->getRepository('ErsBase\Entity\Status')
                        ->findOneBy(array('value' => 'partly paid'));
        if(!$statusPartlyPaid) {
            throw new \Exception('Unable to find status partly paid, please create this status.');
        }
        $statusOverpaid = $entityManager->getRepository('ErsBase\Entity\Status')
                        ->findOneBy(array('value' => 'overpaid'));
        if(!$statusOverpaid) {
            throw new \Exception('Unable to find status overpaid, please create this status.');
        }
        $statusOrdered = $entityManager->getRepository('ErsBase\Entity\Status')
                        ->findOneBy(array('value' => 'ordered'));
        if(!$statusOrdered) {
            throw new \Exception('Unable to find status ordered, please create this status.');
        }
        
        if($order_amount == $statement_amount) {
            $order->setPaymentStatus('paid');
            $order->setStatus($statusPaid);
        
            foreach($order->getPackages() as $package) {
                $package->setStatus($statusPaid);
                $entityManager->persist($package);
                foreach($package->getItems() as $item) {
                    $item->setStatus($statusPaid);
                    $entityManager->persist($item);
                }
            }
            
            $entityManager->persist($order);
            
            $paid = true;
            echo $matchInfo." (exact)".PHP_EOL;
        } elseif($order_amount < $statement_amount) {
            $order->setPaymentStatus('overpaid');
            $order->setStatus($statusOverpaid);
        
            foreach($order->getPackages() as $package) {
                $package->setStatus($statusOverpaid);
                $entityManager->persist($package);
                foreach($package->getItems() as $item) {
                    $item->setStatus($statusOverpaid);
                    $entityManager->persist($item);
                }
            }
            
            $entityManager->persist($order);
            
            #$paid = true;
            $paid = false;
            echo $matchInfo." (overpaid)".PHP_EOL;
        } else {
            $order->setStatus($statusOrdered);
            foreach($order->getPackages() as $package) {
                $package->setStatus($statusOrdered);
                $entityManager->persist($package);
                foreach($package->getItems() as $item) {
                    $item->setStatus($statusOrdered);
                    $entityManager->persist($item);
                }
            }
            
            $paid = false;
            echo $matchInfo." (partial)".PHP_EOL;
        }

        $entityManager->flush();
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
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $matches = $entityManager->getRepository('ErsBase\Entity\Match')
                ->findAll();
        
        foreach($matches as $match) {
            $statement = $match->getBankStatement();
            $statement->setStatus('new');
            $entityManager->persist($statement);
            $entityManager->remove($match);
        }
        $entityManager->flush();
    }
    
    public function genUserListAction() {
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $users = $entityManager->getRepository('ErsBase\Entity\User')
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
        
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        #$order = $entityManager->getRepository('ErsBase\Entity\Order')
                #->findOneBy(array('id' => '297'));
                #->findOneBy(array('id' => '12'));
                #->findOneBy(array('id' => '54'));
        
        $orders = $entityManager->getRepository('ErsBase\Entity\Order')
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
                $entityManager->detach($package);
            }
            $entityManager->detach($order);
        }
        echo "generated ".$count." etickets in ".(microtime()-$time_start).' $unit'.PHP_EOL;
    }
    
    public function sendPaymentReminderAction() {
        $request = $this->getRequest();
        
        $long_real = (bool) $request->getParam('real',false);
        $short_real = (bool) $request->getParam('r',false);
        $isReal = ($long_real | $short_real);
        
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $queryBuilder1 = $entityManager->getRepository('ErsBase\Entity\Order')->createQueryBuilder('o');
        $queryBuilder1->where($queryBuilder1->expr()->isNull('o.payment_reminder_status'));
        
        $correctOrders = $queryBuilder1->getQuery()->getResult();
        
        echo "found ".count($correctOrders)." orders to correct".PHP_EOL;
        foreach($correctOrders as $o) {
            $o->setPaymentReminderStatus(0);
            $entityManager->persist($o);
        }
        $entityManager->flush();
        
        #$limit = 3;
        $queryBuilder = $entityManager->getRepository('ErsBase\Entity\Order')->createQueryBuilder('o');
        $queryBuilder->join('o.status', 's');
        $queryBuilder->where($queryBuilder->expr()->eq('s.value', ':status'));
        $queryBuilder->andWhere($queryBuilder->expr()->lt('o.created', ':paymentTarget'));
        $queryBuilder->andWhere($queryBuilder->expr()->eq('o.payment_reminder_status', ':prstatus'));
        $queryBuilder->setParameter('status', 'ordered');
        $paymentTarget = new \DateTime;

        $paymentTarget->modify( '-20 days' );
        $queryBuilder->setParameter('paymentTarget', $paymentTarget);
        $queryBuilder->setParameter('prstatus', '0');
        #$queryBuilder->setFirstResult( $offset )
        #$queryBuilder->setMaxResults( $limit );
        
        $notPaidOrders = $queryBuilder->getQuery()->getResult();
        echo count($notPaidOrders)." not paid orders found from before ".$paymentTarget->format('d.m.Y').".".PHP_EOL;
        
        #if(!$isReal) {
        #    echo "Use -r parameter to really send out all payment reminder.".PHP_EOL;
        #    exit();
        #}
        
        # countdown
        echo PHP_EOL;
        for($i=10; $i > 0; $i--) {
            echo "Really sending out payment reminder in... ".$i." seconds (ctrl+c to abort)   \r";
            sleep(1);
        }
        echo PHP_EOL;
        
        foreach($notPaidOrders as $order) {
            $orderService = $this->getServiceLocator()
                    ->get('ErsBase\Service\OrderService');
            $orderService->setOrder($order);
            $orderService->sendPaymentReminder();

            if($config['environment'] == 'production') {
                /*** real buyer ***/
                $buyer = $order->getBuyer();
                $emailService>addTo($buyer);
                /***/
            } else {
                /*** test buyer **/
                $user = new Entity\User();
                $user->setEmail('andi'.$order->getCode()->getValue().'@inbaz.org');
                $emailService>addTo($user);
                /***/
            }
            
            $bcc = new Entity\User();
            $bcc->setEmail($config['ERS']['info_mail']);
            $emailService>addBcc($bcc);

            $subject = "[".$config['ERS']['name_short']."] "._('Payment reminder for your order:')." ".$order->getCode()->getValue();
            $emailService>setSubject($subject);

            $viewModel = new ViewModel(array(
                'order' => $order,
            ));
            $viewModel->setTemplate('email/payment-reminder.phtml');
            $viewRender = $this->getServiceLocator()->get('ViewRenderer');
            $html = $viewRender->render($viewModel);

            $emailService>setHtmlMessage($html);

            $emailService>send();
            
            $order->setPaymentReminderStatus($order->getPaymentReminderStatus()+1);
            $entityManager->persist($order);
            $entityManager->flush();
            
            echo "sent payment reminder for order ".$order->getCode()->getValue().PHP_EOL;
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
        
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        # correct package ticket_status
        $queryBuilder = $entityManager->getRepository('ErsBase\Entity\Package')->createQueryBuilder('p');
        $queryBuilder->where('p.ticket_status IS NULL');
        $queryBuilder->orWhere("p.ticket_status = 'not_send'");
        $noStatusPackages = $queryBuilder->getQuery()->getResult();
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
            #if($package->getStatus() == 'paid') {
            if($package->getStatus()->getValid()) {
                if($productId[1] == 1 || $productId[1] == 0) {
                    $package->setTicketStatus('can_send');
                } else {
                    $package->setTicketStatus('block_send');
                }
            # if the package is not paid
            } else {
                $package->setTicketStatus('not_send');
            }
            $entityManager->persist($package);
        }
        $entityManager->flush();
        
        $settingService = $this->getServiceLocator()
                ->get('ErsBase\Service\SettingService');
        
        $ticket_count = 5;
        if($settingService->get('ers.send_ticket_count') != '' && is_numeric($settingService->get('ers.send_ticket_count'))) {
            $ticket_count = $settingService->get('ers.send_ticket_count');
        }

        /*if(empty($ticket_count) || !is_numeric($ticket_count)) {
            #$ticket_count = 100;
            $ticket_count = 5;
        }*/
       
        $can_send_packages = $entityManager->getRepository('ErsBase\Entity\Package')

            ->findBy(array('ticket_status' => 'can_send'));
        if($isDebug) {
            echo "Can send out e-tickets for ".count($can_send_packages)." packages, will process ".$ticket_count." now.".PHP_EOL;
        }
        
        $packages = $entityManager->getRepository('ErsBase\Entity\Package')
            ->findBy(array('ticket_status' => 'can_send'), array(), $ticket_count);
        
        #if(!$isReal) {
        #    echo "Use -r parameter to really send out all etickets.".PHP_EOL;
        #    exit();
        #}
        
        /*echo PHP_EOL;
        for($i=10; $i > 0; $i--) {
            echo "Really sending out e-tickets in... ".$i." seconds (ctrl+c to abort)   \r";
            sleep(1);
        }
        echo PHP_EOL;*/
        
        #exit();
        foreach($packages as $package) {
            # prepare email (participant, buyer)
            #$emailService = new Service\EmailService();
            #$emailService = $this->getServiceLocator()
            #            ->get('ErsBase\Service\EmailService');
            #$emailService>setFrom($config['ERS']['info_mail']);

            #$order = $package->getOrder();
            #$participant = $package->getParticipant();

            #if($config['environment'] == 'production') {
            #    /*** remove last slash to comment ***/
            #    $buyer = $order->getBuyer();
            #    if($participant->getEmail() == '') {
            #        $emailService>addTo($buyer);
            #    } elseif($participant->getEmail() == $buyer->getEmail()) {
            #        $emailService>addTo($buyer);
            #    } else {
            #        $emailService>addTo($participant);
            #        $emailService>addCc($buyer);
            #    }
            #    /*** remove leading slash to comment ***/
            #} else {
            #    /*** remove last slash to comment ***/
            #    $user = new Entity\User();
            #    $user->setEmail('andi'.$package->getCode()->getValue().'@inbaz.org');
            #    $emailService>addTo($user);
            #    /*** remove leading slash to comment ***/
            #}
            
            $packageService = $this->getServiceLocator()
                    ->get('ErsBase\Service\PackageService');
            $packageService->setPackage($package);
            $packageService->sendEticket();
        }
    }
    
    public function emailStatusAction() {
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $users = $entityManager->getRepository('ErsBase\Entity\User')
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
            $entityManager->persist($user);
            $entityManager->flush();
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
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $queryBuilder = $entityManager->getRepository('ErsBase\Entity\Item')->createQueryBuilder('i');
        $queryBuilder->where('i.agegroup IS NULL');
        $items = $queryBuilder->getQuery()->getResult();
        /*$items = $entityManager->getRepository('ErsBase\Entity\Item')
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
                        $entityManager->persist($item);

                        foreach($item->getChildItems() as $cItem) {
                            $cItem->setAgegroup($agegroup->getAgegroup());
                            $entityManager->persist($cItem);
                        }
                    }
                    break;
                case 4:
                    # day ticket
                    $participant = $package->getParticipant();
                    $agegroup = $agegroupService->getAgegroupByDate($participant->getBirthday());
                    
                    if($agegroup) {
                        $item->setAgegroup($agegroup->getAgegroup());
                        $entityManager->persist($item);
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
        $entityManager->flush();
        
        echo "found ".$count." items with no owner".PHP_EOL;
    }
    
    public function calcSumsAction() {
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $queryBuilder = $entityManager->getRepository('ErsBase\Entity\Order')->createQueryBuilder('o');
        $queryBuilder->join('o.status', 's', 'WITH', 's.active = 1');
        $queryBuilder->where($queryBuilder->expr()->neq('s.value', ':status'));
        $queryBuilder->setParameter('status', 'order pending');
        
        $orders = $queryBuilder->getQuery()->getResult();
        #echo 'found '.count($orders).' orders'.PHP_EOL;
        
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
            
            $entityManager->persist($order);
        }
        $entityManager->flush();
    }
    
    public function cleanupUserAction() {
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $queryBuilder = $entityManager->getRepository('ErsBase\Entity\User')->createQueryBuilder('u');
        $queryBuilder->leftJoin('u.roles', 'r');
        $queryBuilder->where($queryBuilder->expr()->isNull('r.roleId'));
        $queryBuilder->andWhere($queryBuilder->expr()->lt('u.updated', ':updated'));
        $updated = new \DateTime();
        $updated->sub(new \DateInterval('PT2H'));
        $queryBuilder->setParameter('updated', $updated);
        $users = $queryBuilder->getQuery()->getResult();
        echo 'found '.count($users).' users'.PHP_EOL;
        foreach($users as $user) {
            foreach($user->getOrders() as $order) {
                if($order->getStatus()->getValue() != 'order pending') {
                    echo "ERROR: found order ".$order->getId()." which may not be pending. (".$order->getStatus()->getValue().") (user_id: ".$user->getId().")".PHP_EOL;
                } else {
                    $entityManager->remove($order);
                }
            }
            foreach($user->getPackages() as $package) {
                foreach($package->getItems() as $item) {
                    $entityManager->remove($item);
                }
                $entityManager->remove($package);
            }
            $entityManager->remove($user);
        }
        $entityManager->flush();
        
        
    }
    
    public function cleanupOrderAction() {
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $queryBuilder = $entityManager->getRepository('ErsBase\Entity\Order')->createQueryBuilder('o');
        $queryBuilder->join('o.status', 's');
        $queryBuilder->where($queryBuilder->expr()->eq('s.value', ':status'));
        $queryBuilder->andWhere($queryBuilder->expr()->lt('o.updated', ':updated'));
        
        $updated = new \DateTime();
        $updated->sub(new \DateInterval('PT8H'));
        $queryBuilder->setParameter('updated', $updated);
        $queryBuilder->setParameter('status', 'order pending');
        
        $orders = $queryBuilder->getQuery()->getResult();
        echo 'found '.count($orders).' orders'.PHP_EOL;
        foreach($orders as $order) {
            $entityManager->remove($order);
            foreach($order->getPackages() as $package) {
                $entityManager->remove($package);
                foreach($package->getItems() as $item) {
                    $entityManager->remove($item);
                }
            }
        }
        $entityManager->flush();
    }
    
    public function correctStatusAction() {
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $orders = $entityManager->getRepository('ErsBase\Entity\Order')
                ->findAll();
        $statusService = $this->getServiceLocator()
                ->get('ErsBase\Service\StatusService');
        
        gc_enable();
        
        foreach($orders as $order) {
            $statusService->setOrderStatus($order, $order->getStatus(), false);
            
            $order->setTotalSum($order->getSum());
            $order->setOrderSum($order->getPrice());
            $entityManager->persist($order);
            $entityManager->flush();
            $order = null;
            gc_collect_cycles();
        }
    }
    
    public function correctPackageStatusAction() {
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $packages = $entityManager->getRepository('ErsBase\Entity\Package')
                ->findAll();
        $statusService = $this->getServiceLocator()
                ->get('ErsBase\Service\StatusService');
        
        foreach($packages as $package) {
            $statusService->setPackageStatus($package, $package->getStatus(), true);
        }
    }
    
    public function correctPackagesInPaidOrdersAction() {
        # correct-packages-in-paid-orders
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        # correct from package side
        $queryBuilder1 = $entityManager->getRepository('ErsBase\Entity\Package')->createQueryBuilder('p');
        $queryBuilder1->join('p.status', 's');
        $queryBuilder1->where($queryBuilder1->expr()->eq('s.value', ':status'));
        $queryBuilder1->setParameter('status', 'ordered');
        
        $packages = $queryBuilder1->getQuery()->getResult();
        
        $statusService = $this->getServiceLocator()
                ->get('ErsBase\Service\StatusService');
        
        echo "checking ".count($packages)." packages".PHP_EOL;
        $count = 0;
        foreach($packages as $package) {
            $order = $package->getOrder();
            $statusService->setPackageStatus($package, $order->getStatus(), false);
            if($order->getStatus()->getValue() == 'paid') {
                #$package->setStatus($order->getStatus());
                $statusService->setPackageStatus($package, $order->getStatus(), false);
                #$entityManager->persist($package);
                $count++;
            }
        }
        $entityManager->flush();
        echo "corrected ".$count." packages".PHP_EOL;
        
        
        # correct from order side
        $queryBuilder2 = $entityManager->getRepository('ErsBase\Entity\Order')->createQueryBuilder('o');
        $queryBuilder2->join('o.status', 's');
        $queryBuilder2->where($queryBuilder2->expr()->eq('s.value', ':status'));
        $queryBuilder2->setParameter('status', 'paid');
        
        
        $orders = $queryBuilder2->getQuery()->getResult();
        
        echo "checking ".count($orders)." orders".PHP_EOL;
        $count = [];
        foreach($orders as $order) {
            foreach($order->getPackages() as $package) {
                $status = $package->getStatus()->getValue();
                if(empty($count[$status])) {
                    $count[$status] = 1;
                } else {
                    $count[$status]++;
                }
                
                if($status == 'ordered') {
                    $package->setStatus($order->getStatus());
                    $entityManager->persist($package);
                }
            }
        }
        $entityManager->flush();
        var_export($count);
    }
    
    public function correctActiveUserAction() {
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $users = $entityManager->getRepository('ErsBase\Entity\User')
                ->findAll();
        foreach($users as $user) {
            foreach($user->getPackages() as $package) {
                $order = $package->getOrder();
                if($order->getStatus()->getActive()) {
                    $user->setActive(true);
                    $entityManager->persist($user);
                }
            }
        }
        $entityManager->flush();
    }
    
    public function correctPaidPackagesAction() {
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $queryBuilder = $entityManager->getRepository('ErsBase\Entity\Package')->createQueryBuilder('p');
        $queryBuilder->join('p.status', 's');
        $queryBuilder->where($queryBuilder->expr()->eq('s.value', ':status'));
        $queryBuilder->setParameter('status', 'paid');
        
        $packages = $queryBuilder->getQuery()->getResult();
        
        foreach($packages as $package) {
            $order = $package->getOrder();
            if($order->getStatus()->getValue() != 'paid') {
                $status = $package->getStatus()->getValue();
                $package->setStatus($order->getStatus());
                echo $package->getCode()->getValue().": package status was: ".$status."; package status is: ".$package->getStatus().PHP_EOL;
                $entityManager->persist($package);
            }
        }
        
        $entityManager->flush();
    }
    
    public function correctOrderedPackagesAction() {
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $queryBuilder = $entityManager->getRepository('ErsBase\Entity\Package')->createQueryBuilder('p');
        $queryBuilder->join('p.status', 's');
        $queryBuilder->where($queryBuilder->expr()->eq('s.value', ':status'));
        $queryBuilder->setParameter('status', 'ordered');
        
        $packages = $queryBuilder->getQuery()->getResult();
        
        foreach($packages as $package) {
            $order = $package->getOrder();
            if($order->getStatus()->getValue() == 'paid') {
                $status = $package->getStatus()->getValue();
                $package->setStatus($order->getStatus());
                echo $package->getCode()->getValue().": package status was: ".$status."; package status is: ".$package->getStatus().PHP_EOL;
                $entityManager->persist($package);
            }
        }
        
        $entityManager->flush();
    }
    
    public function correctItemStatusAction() {
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $items = $entityManager->getRepository('ErsBase\Entity\Item')->findAll();
        
        foreach($items as $item) {
            $package = $item->getPackage();
            if($item->getStatus()->getValue() == 'ordered' || $item->getStatus()->getValue() == 'paid' || $item->getStatus()->getValue() == 'order pending') {
                $item->setStatus($package->getStatus());
                #echo $item->getCode()->getValue().": item status was: ".$status."; item status is: ".$item->getStatus().PHP_EOL;
                $entityManager->persist($item);
            }
        }
        
        $entityManager->flush();
    }
    
    public function overpaidOrdersAction() {
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $orders = $entityManager->getRepository('ErsBase\Entity\Order')
                ->findAll();
        
        $overpaid = [];
        foreach($orders as $order) {
            if($order->getSum() < $order->getStatementAmount()) {
                $overpaid[] = $order;
                echo $order->getCode()->getValue().' '.\number_format($order->getSum(), 2, ',', '.').' < '.\number_format($order->getStatementAmount(), 2, ',', '.').PHP_EOL;
            }
        }
    }
    
    public function processMailqAction() {
        $emailService = $entityManager = $this->getServiceLocator()
            ->get('ErsBase\Service\EmailService');
        
        $emailService->mailqWorker();
    }
    
    public function insertTestMail2Action() {
        
        $emailService = $this->getServiceLocator()
                ->get('ErsBase\Service\EmailService');
        
        $from = 'anmeldung@circulum.de';
        $recipients = [
            'andi@inbaz.org'
        ];
        $subject = 'This is a testmail';
        $content = '<h1>This is html content</h1>';
        
        $emailService>addMailToQueue($from, $recipients, $subject, $content);
        #$emailService>addMailToQueue($from, $recipients, $subject, $content, $is_html = true, $attachments = array());
    }
    
    
    public function insertTestMail3Action() {
        
        $emailService = $this->getServiceLocator()
                ->get('ErsBase\Service\EmailService');
        
        $from = 'anmeldung@circulum.de';
        $recipients = [
            'andi@inbaz.org'
        ];
        $subject = 'This is a testmail';
        $content = '<h1>This is html content</h1>';
        
        $attachments = [
            'public/Terms and Conditions ERS EN v7.pdf',
            'public/Terms and Conditions organisation EN v6.pdf',
        ];
        
        $emailService>addMailToQueue($from, $recipients, $subject, $content, true, $attachments);
        #$emailService>addMailToQueue($from, $recipients, $subject, $content, $is_html = true, $attachments = array());
    }
    
    public function insertTestMailAction() {
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $mailq = new Entity\Mailq();
        
        $user = $entityManager->getRepository('ErsBase\Entity\User')
                ->findOneBy(['email' => 'andi@inbaz.org']);
        
        $mailq->setFrom($user);
        
        $att = new Entity\MailAttachment();
        $att->setLocation('public/Terms and Conditions ERS EN v7.pdf');
        $att->setMailq($mailq);
        $mailq->addMailAttachment($att);
        
        $mailq->setSubject('Testmail');
        #$mailq->setTextMessage('This is a text message.');
        $mailq->setHtmlMessage('<h1>This is a text message.</h1>');
        $mailq->setIsHtml(true);
        
        $entityManager->persist($mailq);
        $entityManager->flush();
        
        $emailService = $this->getServiceLocator()
                ->get('ErsBase\Service\EmailService');
        
        $from = 'andi@inbaz.org';
        $recipients = [
            'andi@sixhop.net',
            'andi@eja.net'
        ];
        $recipients = [
            [
                'email' => 'andi1@inbaz.org',
                'type' => 'to',
            ],
            [
                'email' => 'andi2@inbaz.org',
                'type' => 'cc',
            ],
            [
                'email' => 'ers@inbaz.org',
                'type' => 'bcc',
            ],
        ];
        $content = 'This is text content';
        $content = '<h1>This is html content</h1>';
        $is_html = true;
        $emailService>addMailToQueue(
                $from,
                $recipients,
                $content,
                $is_html
                );
        
        $mailqHasTo = new Entity\MailqHasUser();
        $mailqHasTo->setUser($user);
        $mailqHasTo->setUserId($user->getId());
        
        $mailqHasTo->setMailq($mailq);
        $mailqHasTo->setMailqId($mailq->getId());
        $mailqHasTo->setTo();
        
        $entityManager->persist($mailqHasTo);
        $entityManager->flush();
    }
}

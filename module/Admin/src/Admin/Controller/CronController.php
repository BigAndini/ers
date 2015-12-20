<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use ersBase\Entity;
use ersBase\Service;
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
        
        $orderStatus = $em->getRepository("ersBase\Entity\OrderStatus")
                ->findBy(array('value' => 'cron'));
        foreach($orderStatus as $status) {
            $em->remove($status);
        }
        $em->flush();
        
        $orders = $em->getRepository("ersBase\Entity\Order")
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
        
        $statements = $em->getRepository("ersBase\Entity\BankStatement")
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
                    $order_code = $em->getRepository("ersBase\Entity\Code")
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
                            $order_code = $em->getRepository("ersBase\Entity\Code")
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
        
        /*
         * check status of unpaid orders
         */
        
        #TODO: find order which contain items in status != paid and != cancelled and != refund
        $orders = $em->getRepository("ersBase\Entity\Order")
                ->findBy(array('payment_status' => 'unpaid'));
        foreach($orders as $order) {
            $statement_amount = $order->getStatementAmount();
            $order_amount = $order->getSum();
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
        }
        $em->flush();
        echo PHP_EOL."checked ".count($orders)." orders and set status.".PHP_EOL;
    }
    
    private function createMatch(Entity\BankStatement $statement, Entity\Code $code) {
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $bankaccount = $statement->getBankAccount();
        #$statement_format = json_decode($bankaccount->getStatementFormat());
        
        $order = $em->getRepository("ersBase\Entity\Order")
            ->findOneBy(array('Code_id' => $code->getId()));
        
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
        $admin = $em->getRepository("ersBase\Entity\User")
            ->findOneBy(array('id' => 1));
        $match->setAdmin($admin);
        
        $order->addMatch($match);

        $em->persist($match);

        #$statement_amount = (float) $statement->getBankStatementColByNumber($statement_format->amount)->getValue();
        $statement_amount = $order->getStatementAmount();
        $order_amount = $order->getSum();
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
        
        $matches = $em->getRepository("ersBase\Entity\Match")
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
        
        $users = $em->getRepository("ersBase\Entity\User")
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
        
        #$order = $em->getRepository("ersBase\Entity\Order")
                #->findOneBy(array('id' => '297'));
                #->findOneBy(array('id' => '12'));
                #->findOneBy(array('id' => '54'));
        
        $orders = $em->getRepository("ersBase\Entity\Order")
                ->findAll();
        $count = 0;
        $eticketService = $this->getServiceLocator()
            ->get('ersBase\Service\ETicketService');
        
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
        
        $orders = $em->getRepository("ersBase\Entity\Order")
                ->findAll();
        $count = 0;
        foreach($orders as $order) {
            $order->setTotalSum($order->getSum());
            $order->setOrderSum($order->getPrice());
            if($order->getPaymentStatus() == 'paid') {
                $items = $order->getItems();
                foreach($items as $item) {
                    $item->setStatus('paid');
                    $em->persist($item);
                }
            }
            $em->persist($order);
            $em->flush();
            $count++;
        }
    }
    
    public function sendUEticketsAction() {
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        # find user under 18
        $qb = $em->getRepository("ersBase\Entity\User")->createQueryBuilder('u');
        $qb->where("u.birthday > '1997-08-01'");
        $users = $qb->getQuery()->getResult();
        echo "found ".count($users)." users under 18.".PHP_EOL;
        
        $package_counter = 0;
        foreach($users as $user) {
            # find package in status send_out -> can_send
            $packages = $user->getPackages();
            foreach($packages as $package) {
                if($package->getTicketStatus() != 'send_out') {
                    continue;
                }
                $package_counter++;
                
                # prepare email (participant, buyer)
                $emailService = new Service\EmailService();
                $emailService->setFrom('prereg@eja.net');

                $order = $package->getOrder();
                $participant = $package->getParticipant();

                $buyer = $order->getBuyer();
                if($participant->getEmail() == '') {
                    $emailService->addTo($buyer);
                } elseif($participant->getEmail() == $buyer->getEmail()) {
                    $emailService->addTo($buyer);
                } else {
                    $emailService->addTo($participant);
                    $emailService->addCc($buyer);
                }
                /*$user = new Entity\User();
                $user->setEmail('andi@inbaz.org');
                $emailService->addTo($user);*/

                $bcc = new Entity\User();
                $bcc->setEmail('prereg@eja.net');
                $emailService->addBcc($bcc);

                $subject = "[EJC 2015] Updated E-Ticket for ".$participant->getFirstname()." ".$participant->getSurname()." (order ".$order->getCode()->getValue().")";
                $emailService->setSubject($subject);

                $viewModel = new ViewModel(array(
                    'package' => $package,
                ));
                $viewModel->setTemplate('email/eticket-participant_u16_u18.phtml');
                $viewRenderer = $this->getServiceLocator()->get('ViewRenderer');
                $html = $viewRenderer->render($viewModel);

                $emailService->setHtmlMessage($html);

                # generate e-ticket pdf
                $eticketService = $this->getServiceLocator()
                    ->get('ersBase\Service\ETicketService');

                $eticketService->setLanguage('en');
                $eticketService->setPackage($package);
                $eticketFile = $eticketService->generatePdf();

                echo ob_get_clean();
                echo "generated e-ticket ".$eticketFile.".".PHP_EOL;

                $emailService->addAttachment($eticketFile);

                #$terms1 = getcwd().'/public/Terms-and-Conditions-ERS-EN-v4.pdf';
                #$terms2 = getcwd().'/public/Terms-and-Conditions-ORGA-EN-v2.pdf';
                #$emailService->addAttachment($terms1);
                #$emailService->addAttachment($terms2);

                # send out email
                $emailService->send();
                
                $package->setTicketStatus('send_out2');
                $em->persist($package);
                $em->flush();
            }
        }
        #echo "found ".$package_counter." packages to resend.".PHP_EOL;
    }
    
    public function sendEticketsAction() {
        $request = $this->getRequest();
        
        $long_real = (bool) $request->getParam('real',false);
        $short_real = (bool) $request->getParam('r',false);
        $isReal = ($long_real | $short_real);
        
        #$long_count = (int) $request->getParam('count',false);
        #$short_count = (int) $request->getParam('c',false);
        #$ticket_count = $long_count + $short_count;
        
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        # correct package ticket_status
        $qb = $em->getRepository("ersBase\Entity\Package")->createQueryBuilder('p');
        $qb->where('p.ticket_status IS NULL');
        $qb->orWhere("p.ticket_status = 'not_send'");
        $noStatusPackages = $qb->getQuery()->getResult();
        echo count($noStatusPackages)." packages need to be corrected.".PHP_EOL;
        foreach($noStatusPackages as $package) {
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
        
        $packages = $em->getRepository("ersBase\Entity\Package")
            ->findBy(array('ticket_status' => 'can_send'), array(), 100);
        echo "Can send out e-tickets for ".count($packages)." packages.".PHP_EOL;
        
        if(!$isReal) {
            echo "Use -r parameter to really send out all etickets.".PHP_EOL;
            exit();
        }
        
        echo PHP_EOL;
        for($i=10; $i > 0; $i--) {
            echo "Really sending out e-tickets in... ".$i." seconds (ctrl+c to abort)   \r";
            sleep(1);
        }
        echo PHP_EOL;
        
        foreach($packages as $package) {
            # prepare email (participant, buyer)
            $emailService = new Service\EmailService();
            $emailService->setFrom('prereg@eja.net');

            $order = $package->getOrder();
            $participant = $package->getParticipant();

            $buyer = $order->getBuyer();
            if($participant->getEmail() == '') {
                $emailService->addTo($buyer);
            } elseif($participant->getEmail() == $buyer->getEmail()) {
                $emailService->addTo($buyer);
            } else {
                $emailService->addTo($participant);
                $emailService->addCc($buyer);
            }
            /*$user = new Entity\User();
            $user->setEmail('andi@inbaz.org');
            $emailService->addTo($user);*/
            
            $bcc = new Entity\User();
            $bcc->setEmail('prereg@eja.net');
            $emailService->addBcc($bcc);

            $subject = "[EJC 2015] E-Ticket for ".$participant->getFirstname()." ".$participant->getSurname()." (order ".$order->getCode()->getValue().")";
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
                ->get('ersBase\Service\ETicketService');

            $eticketService->setLanguage('en');
            $eticketService->setPackage($package);
            $eticketFile = $eticketService->generatePdf();

            echo ob_get_clean();
            echo "generated e-ticket ".$eticketFile.".".PHP_EOL;

            $emailService->addAttachment($eticketFile);

            #$terms1 = getcwd().'/public/Terms-and-Conditions-ERS-EN-v4.pdf';
            #$terms2 = getcwd().'/public/Terms-and-Conditions-ORGA-EN-v2.pdf';
            #$emailService->addAttachment($terms1);
            #$emailService->addAttachment($terms2);
            
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
        
        $users = $em->getRepository("ersBase\Entity\User")
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
        
        $qb = $em->getRepository("ersBase\Entity\Item")->createQueryBuilder('i');
        $qb->where('i.agegroup IS NULL');
        $items = $qb->getQuery()->getResult();
        /*$items = $em->getRepository("ersBase\Entity\Item")
                ->findAll();*/
        
        echo "checking ".count($items)." items".PHP_EOL;
        
        $count = 0;
        foreach($items as $item) {
            $package = $item->getPackage();
            
            $agegroupService = $this->getServiceLocator()
                    ->get('ersBase\Service\AgegroupService:price');
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
    
    public function correctItemStatusAction() {
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $qb = $em->getRepository("ersBase\Entity\Item")->createQueryBuilder('i');
        $qb->where("i.status = 'transferred'");
        #$qb->orWhere("p.ticket_status = 'not_send'");
        $transferred_items = $qb->getQuery()->getResult();
        echo "found ".count($transferred_items)." transferred items.".PHP_EOL;

        foreach($transferred_items as $item) {
            $tItem = $item->getTransferredItem();
            if(!$tItem) {
                continue;
            }
            if(!$tItem->hasChildItems()) {
                continue;
            }
            foreach($item->getChildItems() as $cItem) {
                $cItem->setStatus('transferred');
                $em->persist($item);
            }
            foreach($tItem->getChildItems() as $cItem) {
                if($tItem->getPackageId() != $cItem->getPackageId()) {
                    echo "Package Ids do not match: ".$tItem->getPackageId()." != ".$cItem->getPackageId().PHP_EOL;
                    $cItem->setPackage($tItem->getPackage());   
                    $em->persist($cItem);
                }
            }
        }
        $em->flush();
    }
}
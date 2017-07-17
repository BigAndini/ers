<?php

use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\Context,
    Behat\Behat\Context\SnippetAcceptingContext,
    Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

use ErsBase\CommonContexts\ZF2DoctrineContext;

use Behat\MinkExtension\Context\MinkContext;
#use Behat\SahiClient\Client;

# doctrine
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\Connection;
    

/**
 * Features context.
 */
#class FeatureContext extends MinkContext
class FeatureContext extends MinkContext implements Context, SnippetAcceptingContext
{
    protected $em;
    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
    }
    
    /**
     * @BeforeSuite
     */
    public static function prepare()
    {
        // prepare system for test suite
        // before it runs
        #echo "prepare".PHP_EOL;
        #$em = new \DoctrineORMModule\Service\EntityManagerFactory('orm_default');
        #echo 'class: '.get_class($em).PHP_EOL;
        #$users = $em->getRepository('ErsBase\Entity\User')->findAll();
        #echo 'found '.count($users).' users'.PHP_EOL;
        /*$this->getMainContext()
            ->getSubcontext('zf2_doctrine')
            ->buildSchema($event);*/
    }

    /**
     * @AfterScenario @Add a buyer info
     */
    public function cleanBuyer(ScenarioEvent $event)
    {
        // clean database after scenarios,
        // tagged with @database
        #echo "cleanBuyer".PHP_EOL;
    }
    
    /**
     * @BeforeScenario @Add a buyer info
     */
    public function prepareBuyer(ScenarioEvent $event)
    {
        // clean database after scenarios,
        // tagged with @database
        #echo "prepareBuyer".PHP_EOL;
    }
    
    /**
     * @Given /^I am logged in as "([^"]*)" with "([^"]*)"$/
     */
    public function IAmLoggedInAs($username, $password) {
        $this->visitPath('/user/login');
        
        $field = $this->fixStepArgument('identity');
        $value = $this->fixStepArgument($username);
        $this->getSession()->getPage()->fillField($field, $value);
        
        $field = $this->fixStepArgument('credential');
        $value = $this->fixStepArgument($password);
        $this->getSession()->getPage()->fillField($field, $value);
        
        $field = $this->fixStepArgument('remember_me');
        $value = $this->fixStepArgument(1);
        $this->getSession()->getPage()->fillField($field, $value);
        
        $button = $this->fixStepArgument('submit');
        $this->getSession()->getPage()->pressButton($button);
        
        #var_dump($this->getSession()->getPage()->getHtml());
    }
    
    

    /**
     * @Given /^I have a file named "([^"]*)"$/
     */
    public function iHaveAFileNamed($file) {
            touch($file);
    }

    /**
     * @Given /^I have a directory named "([^"]*)"$/
     */
    public function iHaveADirectoryNamed($dir) {
            mkdir($dir);
    }

    /**
     * @When /^I run "([^"]*)"$/
     */
    public function iRun($command) {
            exec($command, $output);
            $this->output = trim(implode("\n", $output));
    }

    /**
     * @Then /^I should see "([^"]*)" in the output$/
     */
    public function iShouldSeeInTheOutput($string) {
            PHPUnit_Framework_Assert::assertContains(
                    $string,
                    explode("\n", $this->output)
            );
    }
    
    /**
     * @Then /^show output$/
     */
    public function showOutput() {
        print_r($this->getSession()->getPage()->getHtml());
    }
    
    /**
     * @Given /^start new session$/
     * @Given /^I start a new session$/
     */
    public function startNewSession() {
        #$driver = new \Behat\Mink\Driver\GoutteDriver();
        #$session = new \Behat\Mink\Session($driver);
        $session = $this->getSession();
        #$session->setDriver($driver);
        if($session->isStarted()) {
            $session->stop();
        }
        $session->start();
    }
}

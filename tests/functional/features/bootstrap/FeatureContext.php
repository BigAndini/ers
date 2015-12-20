<?php

use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

use Behat\MinkExtension\Context\MinkContext;
use Behat\SahiClient\Client;

    

/**
 * Features context.
 */
class FeatureContext extends MinkContext
{
    protected $client;
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
     * @Given /^I initialized sahi$/
     */
    public function iInitializedSahi() {
        var_dump($this->getSession());
        #$this->client = new Client();
        #$this->client->start('firefox');
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

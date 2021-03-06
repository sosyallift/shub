<?php

class iisPassStrengthMeterTest extends IISTestUtilites
{
    private $TEST_USERNAME = 'adminForLoginTest';
    private $TEST_EMAIL = 'admin@gmail.com';
    private $TEST_STRONG_PASSWORD = 'asdf@1111';

    private $userService;
    private $user;

    private $questionService;
    private $question;

    protected function setUp()
    {
        $this->setBrowser('firefox');
        $this->setBrowserUrl(OW_URL_HOME);
    }
    public function setUpPage()
    {
        parent::setUpPage(); // TODO: Change the autogenerated stub
        $this->timeouts()->implicitWait(15000);
    }

    public function testPassStrengthMeter()
    {
        $this->prepareSession()->currentWindow()->maximize();
        $this->url(OW_URL_HOME.'join');
        try{
            $this->waitUntilElementLoaded('byXPath',"//form[@id='joinForm']//input[@name='password']");
            $this->byXPath("//form[@id='joinForm']//input[@name='password']")->value('aaaa');
            if($this->byXPath("//td[text()='".OW::getLanguage()->text('iispasswordstrengthmeter','strength_poor_label')."']"))
            {
                $this->assertTrue(true);
            }
            else
            {
                $this->assertTrue(false);
            }
        }catch (Exception $ex){
            echo "____________________________________________________________________";
            echo $ex;
            echo "____________________________________________________________________";
            $this->assertTrue(false);
        }
    }

    public function tearDown()
    {

    }
}
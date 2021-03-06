<?php
/**
 * User: Issa Moradnejad
 * Date: 2016/05/11
 */

class privacyTest extends IISTestUtilites
{
    private $TEST_USER1_NAME = "user1";
    private $TEST_USER2_NAME = "user2";
    private $TEST_USER3_NAME = "user3";
    private $TEST_USER4_NAME = "user4";
    private $TEST_PASSWORD = '12345';

    private $userService;
    private $user1,$user2,$user3,$user4;

    private $questionService;

    private function echoText($text, $bounding_box=false)
    {
        if ($bounding_box) {
            echo "-----------------------------ISSA------------------------------------\n";
            echo "$text\n";
            echo "---------------------------------------------------------------------\n";
        }else
            echo "==========ISSA:==>$text\n";
    }

    protected function setUp()
    {
        $this->setBrowser('firefox');
        $this->setBrowserUrl(OW_URL_HOME);
        $this->userService = BOL_UserService::getInstance();
        $accountType = BOL_QuestionService::getInstance()->getDefaultAccountType()->name;
        IISSecurityProvider::createUser($this->TEST_USER1_NAME,"user1@gmail.com",$this->TEST_PASSWORD,"1987/3/21","1",$accountType);
        IISSecurityProvider::createUser($this->TEST_USER2_NAME,"user2@gmail.com",$this->TEST_PASSWORD,"1987/3/21","1",$accountType);
        IISSecurityProvider::createUser($this->TEST_USER3_NAME,"user3@gmail.com",$this->TEST_PASSWORD,"1987/3/21","1",$accountType);
        IISSecurityProvider::createUser($this->TEST_USER4_NAME,"user4@gmail.com",$this->TEST_PASSWORD,"1987/3/21","1",$accountType);
        $this->user1 = BOL_UserService::getInstance()->findByUsername($this->TEST_USER1_NAME);
        $this->user2 = BOL_UserService::getInstance()->findByUsername($this->TEST_USER2_NAME);
        $this->user3 = BOL_UserService::getInstance()->findByUsername($this->TEST_USER3_NAME);
        $this->user4 = BOL_UserService::getInstance()->findByUsername($this->TEST_USER4_NAME);
        // set some info to users

        $friendsQuestionService = FRIENDS_BOL_Service::getInstance();
        $friendsQuestionService->request($this->user1->getId(),$this->user2->getId());
        $friendsQuestionService->accept($this->user2->getId(),$this->user1->getId());

        $friendsQuestionService->request($this->user1->getId(),$this->user4->getId());
        $friendsQuestionService->accept($this->user4->getId(),$this->user1->getId());

    }

    public function setUpPage()
    {
        parent::setUpPage(); // TODO: Change the autogenerated stub
        $this->timeouts()->implicitWait(15000);
    }
    private function hide_element($className){
        try {
            $this->execute(array(
                'script' => "document.getElementsByClassName('" . $className . "')[0].style.visibility = 'hidden';",
                'args' => array()
            ));
        }catch(Exception $ex){}
    }
    public function testScenario1()
    {

        if(true) {
            $test_caption = "privacyTest-testScenario1";
            //$this->echoText($test_caption);
            $CURRENT_SESSIONS = $this->prepareSession();
            $CURRENT_SESSIONS->currentWindow()->maximize();

            $this->url(OW_URL_HOME . "dashboard");

            //----------USER1
            $sessionId = $CURRENT_SESSIONS->cookie()->get(OW_Session::getInstance()->getName());
            $sessionId = str_replace('%2C', ',', $sessionId); // took 2 hours to detect  '/^[-,a-zA-Z0-9]{1,128}$/'
            $this->sign_in($this->user1->getUsername(),$this->TEST_PASSWORD,true,true,$sessionId);
            try {
                $this->hide_element('demo-nav');
                $this->byName('status')->click();
                $this->byName('status')->value($test_caption);
                $statusPrivacy = $this->byName('statusPrivacy');
                $statusPrivacy->byXPath('option[@value="friends_only"]')->click();//only_for_me, everybody, friends_only
                $this->byXPath('//input[@name="save"]')->click();
                sleep(2);
                $this->url('sign-out');
            } catch (Exception $ex) {
                $this->echoText($ex, true);
                if (getenv("SNAPSHOT_DIR"))
                    file_put_contents(getenv("SNAPSHOT_DIR") . $test_caption . '.png', $this->currentScreenshot());
                $this->assertTrue(false);
            }

            //----------USER2
            $this->sign_in($this->user2->getUsername(),$this->TEST_PASSWORD,true,true,$sessionId);
            try {
                //$this->byXPath('//a[@href="'.OW_URL_HOME.'index"]')->click();
                $this->url(OW_URL_HOME . 'user/' . $this->user1->getUsername());
                $this->waitUntilElementLoaded('byCssSelector', '.ow_ic_clock');
                $this->byCssSelector('.ow_newsfeed_content.ow_smallmargin')->value();
                //$this->echoText('User2 can see!');
                $this->url('sign-out');
            } catch (Exception $ex) {
                $this->echoText($ex, true);
                if (getenv("SNAPSHOT_DIR"))
                    file_put_contents(getenv("SNAPSHOT_DIR") . $test_caption . '.png', $this->currentScreenshot());
                $this->assertTrue(false);
            }

            //----------USER3
            $this->sign_in($this->user3->getUsername(),$this->TEST_PASSWORD,true,true,$sessionId);
            try {
                //$this->byXPath('//a[@href="'.OW_URL_HOME.'index"]')->click();
                $this->url(OW_URL_HOME . 'user/' . $this->user1->getUsername());
                $this->waitUntilElementLoaded('byCssSelector', '.ow_ic_clock');
                try {
                    $this->byCssSelector('.ow_newsfeed_content.ow_smallmargin')->value();
                    if (getenv("SNAPSHOT_DIR"))
                        file_put_contents(getenv("SNAPSHOT_DIR") . $test_caption . '.png', $this->currentScreenshot());
                    $this->echoText('User3 can see!');
                } catch (Exception $ex) {
                    $this->url('sign-out');
                    $this->assertTrue(true);
                    return;
                }
                $this->assertTrue(false);
            } catch (Exception $ex) {
                $this->echoText($ex, true);
                if (getenv("SNAPSHOT_DIR"))
                    file_put_contents(getenv("SNAPSHOT_DIR") . $test_caption . '.png', $this->currentScreenshot());
                $this->assertTrue(false);
            }
        }
    }

    public function testScenario2()
    {
        if(true) {
            $test_caption = "privacyTest-testScenario2";
            //$this->echoText($test_caption);
            $CURRENT_SESSIONS = $this->prepareSession();
            $CURRENT_SESSIONS->currentWindow()->maximize();

            $this->url(OW_URL_HOME . "dashboard");

            //----------USER1
            $sessionId = $CURRENT_SESSIONS->cookie()->get(OW_Session::getInstance()->getName());
            $sessionId = str_replace('%2C', ',', $sessionId); // took 2 hours to detect  '/^[-,a-zA-Z0-9]{1,128}$/'
            $this->sign_in($this->user1->getUsername(),$this->TEST_PASSWORD,true,true,$sessionId);
            try {
                $this->hide_element('demo-nav');
                $this->byName('status')->click();
                $this->byName('status')->value($test_caption);
                $statusPrivacy = $this->byName('statusPrivacy');
                $statusPrivacy->byXPath('option[@value="everybody"]')->click();//only_for_me, everybody, friends_only
                $this->byXPath('//input[@name="save"]')->click();
                sleep(2);
                $this->url('sign-out');
            } catch (Exception $ex) {
                $this->echoText($ex, true);
                if (getenv("SNAPSHOT_DIR"))
                    file_put_contents(getenv("SNAPSHOT_DIR") . $test_caption . '.png', $this->currentScreenshot());
                $this->assertTrue(false);
            }

            //----------USER2
            $this->sign_in($this->user2->getUsername(),$this->TEST_PASSWORD,true,true,$sessionId);
            try {
                //$this->byXPath('//a[@href="'.OW_URL_HOME.'index"]')->click();
                $this->url(OW_URL_HOME . 'user/' . $this->user1->getUsername());
                $this->waitUntilElementLoaded('byCssSelector', '.ow_ic_clock');

                $this->byCssSelector('.ow_newsfeed_content.ow_smallmargin')->value();
                //$this->echoText('User2 can see!');
                $this->url('sign-out');
            } catch (Exception $ex) {
                $this->echoText($ex, true);
                if (getenv("SNAPSHOT_DIR"))
                    file_put_contents(getenv("SNAPSHOT_DIR") . $test_caption . '.png', $this->currentScreenshot());
                $this->assertTrue(false);
            }

            //----------USER3
            $this->sign_in($this->user3->getUsername(),$this->TEST_PASSWORD,true,true,$sessionId);
            try {
                //$this->byXPath('//a[@href="'.OW_URL_HOME.'index"]')->click();
                $this->url(OW_URL_HOME . 'user/' . $this->user1->getUsername());
                $this->waitUntilElementLoaded('byCssSelector', '.ow_ic_clock');
                $this->byCssSelector('.ow_newsfeed_content.ow_smallmargin')->value();
                //$this->echoText('User3 can see!');
                $this->url('sign-out');
                $this->assertTrue(true);
                return;
            } catch (Exception $ex) {
                $this->echoText($ex, true);
                if (getenv("SNAPSHOT_DIR"))
                    file_put_contents(getenv("SNAPSHOT_DIR") . $test_caption . '.png', $this->currentScreenshot());
                $this->assertTrue(false);
            }
        }

    }

    public function testScenario3()
    {

        if(true) {
            $test_caption = "privacyTest-testScenario3";
            //$this->echoText($test_caption);
            $CURRENT_SESSIONS = $this->prepareSession();
            $CURRENT_SESSIONS->currentWindow()->maximize();

            $this->url(OW_URL_HOME . "dashboard");

            //----------USER1
            $sessionId = $CURRENT_SESSIONS->cookie()->get(OW_Session::getInstance()->getName());
            $sessionId = str_replace('%2C', ',', $sessionId); // took 2 hours to detect  '/^[-,a-zA-Z0-9]{1,128}$/'
            $this->sign_in($this->user1->getUsername(),$this->TEST_PASSWORD,true,true,$sessionId);
            try {
                $this->hide_element('demo-nav');
                $this->byName('status')->click();
                $this->byName('status')->value($test_caption);
                $statusPrivacy = $this->byName('statusPrivacy');
                $statusPrivacy->byXPath('option[@value="only_for_me"]')->click();//only_for_me, everybody, friends_only
                $this->byXPath('//input[@name="save"]')->click();
                sleep(2);
                $this->url('sign-out');
            } catch (Exception $ex) {
                $this->echoText($ex, true);
                if (getenv("SNAPSHOT_DIR"))
                    file_put_contents(getenv("SNAPSHOT_DIR") . $test_caption . '.png', $this->currentScreenshot());
                $this->assertTrue(false);
            }

            //----------USER2
            $this->sign_in($this->user2->getUsername(),$this->TEST_PASSWORD,true,true,$sessionId);
            try {
                //$this->byXPath('//a[@href="'.OW_URL_HOME.'index"]')->click();
                $this->url(OW_URL_HOME . 'user/' . $this->user1->getUsername());
                $this->waitUntilElementLoaded('byCssSelector', '.ow_ic_clock');
                try {
                    $this->byCssSelector('.ow_newsfeed_content.ow_smallmargin')->value();
                    if (getenv("SNAPSHOT_DIR"))
                        file_put_contents(getenv("SNAPSHOT_DIR") . $test_caption . '.png', $this->currentScreenshot());
                    $this->echoText('User2 can see!');
                    $this->assertTrue(false);
                    return;
                } catch (Exception $ex) {
                }
                $this->url('sign-out');
            } catch (Exception $ex) {
                $this->echoText($ex, true);
                if (getenv("SNAPSHOT_DIR"))
                    file_put_contents(getenv("SNAPSHOT_DIR") . $test_caption . '.png', $this->currentScreenshot());
                $this->assertTrue(false);
            }

            //----------USER3
            $this->sign_in($this->user3->getUsername(),$this->TEST_PASSWORD,true,true,$sessionId);
            try {
                //$this->byXPath('//a[@href="'.OW_URL_HOME.'index"]')->click();
                $this->url(OW_URL_HOME . 'user/' . $this->user1->getUsername());
                $this->waitUntilElementLoaded('byCssSelector', '.ow_ic_clock');
                try {
                    $this->byCssSelector('.ow_newsfeed_content.ow_smallmargin')->value();
                    if (getenv("SNAPSHOT_DIR"))
                        file_put_contents(getenv("SNAPSHOT_DIR") . $test_caption . '.png', $this->currentScreenshot());
                    $this->echoText('User3 can see!');
                    $this->assertTrue(false);
                } catch (Exception $ex) {
                    return;
                }
            } catch (Exception $ex) {
                $this->echoText($ex, true);
                if (getenv("SNAPSHOT_DIR"))
                    file_put_contents(getenv("SNAPSHOT_DIR") . $test_caption . '.png', $this->currentScreenshot());
                $this->assertTrue(false);
            }
        }
    }

    public function testScenario4PrivacySettings()
    {

        if(true) {
            $test_caption = "privacyTest-testScenario4PrivacySettings";
            //$this->echoText($test_caption);
            $CURRENT_SESSIONS = $this->prepareSession();
            $CURRENT_SESSIONS->currentWindow()->maximize();

            $this->url(OW_URL_HOME . "dashboard");

            //----------USER1
            $sessionId = $CURRENT_SESSIONS->cookie()->get(OW_Session::getInstance()->getName());
            $sessionId = str_replace('%2C', ',', $sessionId); // took 2 hours to detect  '/^[-,a-zA-Z0-9]{1,128}$/'
            $this->sign_in($this->user1->getUsername(),$this->TEST_PASSWORD,true,true,$sessionId);
            try {
                $this->url(OW_URL_HOME.'profile/privacy');
                $this->hide_element('demo-nav');
                //only_for_me, everybody, friends_only
                $this->byName('base_view_profile')->byXPath('option[@value="friends_only"]')->click();
                $this->byName('base_view_my_presence_on_site')->byXPath('option[@value="only_for_me"]')->click();

                $this->byXPath('//input[@name="privacySubmit"]')->click();
                sleep(2);
                $this->url('sign-out');
            } catch (Exception $ex) {
                $this->echoText($ex, true);
                if (getenv("SNAPSHOT_DIR"))
                    file_put_contents(getenv("SNAPSHOT_DIR") . $test_caption . '.png', $this->currentScreenshot());
                $this->assertTrue(false);
            }

            //----------USER2
            $this->sign_in($this->user2->getUsername(),$this->TEST_PASSWORD,true,true,$sessionId);
            try {
                $this->url(OW_URL_HOME . 'user');
                $this->url(OW_URL_HOME . 'user/' . $this->user1->getUsername());

                //1--check profile
                $this->waitUntilElementLoaded('byCssSelector', '.user_profile_data');
            } catch (Exception $ex) {
                $this->echoText($ex, true);
                if (getenv("SNAPSHOT_DIR"))
                    file_put_contents(getenv("SNAPSHOT_DIR") . $test_caption . '.png', $this->currentScreenshot());
                $this->assertTrue(false);
            }
            try { //2--check live
                $this->waitUntilElementLoaded('byCssSelector', '.ow_miniic_live');
                if (getenv("SNAPSHOT_DIR"))
                    file_put_contents(getenv("SNAPSHOT_DIR") . $test_caption . '.png', $this->currentScreenshot());
                $this->assertTrue(false);
            } catch (Exception $ex) {
                $this->url('sign-out');
            }
        }
    }
    public function testScenario5()
    {
        // User1 posts for friends
        // User2 likes the post
        // User3 can't see the post
        // User4 can see the post and it's like
        if(true) {
            $test_caption = "privacyTest-testScenario5";
            //$this->echoText($test_caption);
            $CURRENT_SESSIONS = $this->prepareSession();
            $CURRENT_SESSIONS->currentWindow()->maximize();

            $this->url(OW_URL_HOME . "dashboard");

            //----------USER1
            $sessionId = $CURRENT_SESSIONS->cookie()->get(OW_Session::getInstance()->getName());
            $sessionId = str_replace('%2C', ',', $sessionId); // took 2 hours to detect  '/^[-,a-zA-Z0-9]{1,128}$/'
            $this->sign_in($this->user1->getUsername(),$this->TEST_PASSWORD,true,true,$sessionId);
            try {
                $this->url(OW_URL_HOME . "dashboard");
                $this->hide_element('demo-nav');
                $this->byName('status')->click();
                $this->byName('status')->value($test_caption);
                $statusPrivacy = $this->byName('statusPrivacy');
                $statusPrivacy->byXPath('option[@value="friends_only"]')->click();//only_for_me, everybody, friends_only
                $this->byXPath('//input[@name="save"]')->click();
                sleep(1);
                $this->url('sign-out');
            } catch (Exception $ex) {
                $this->echoText($ex, true);
                if (getenv("SNAPSHOT_DIR"))
                    file_put_contents(getenv("SNAPSHOT_DIR") . $test_caption . '.png', $this->currentScreenshot());
                $this->assertTrue(false);
            }

            //----------USER2 - FRIEND of 1
            $comment = 'good one!';
            $this->sign_in($this->user2->getUsername(),$this->TEST_PASSWORD,true,true,$sessionId);
            try {
                $this->url(OW_URL_HOME . 'user/' . $this->user1->getUsername());
                $this->hide_element('demo-nav');
                $tmp = $this->byXPath("//*[contains(text(),'$test_caption') and contains(@class, 'ow_newsfeed_content ow_smallmargin')]");
                $tag_parent = $tmp->byXPath("../../..");
                $tag_parent->byCssSelector('.newsfeed_like_btn_cont')->click();
                //$tag_parent->byCssSelector('.newsfeed_comment_btn_cont')->click();
                //$tag_parent->byCssSelector('.comments_fake_autoclick')->value($comment);
                sleep(1);
                $this->url('sign-out');
            } catch (Exception $ex) {
                $this->echoText($ex, true);
                if (getenv("SNAPSHOT_DIR"))
                    file_put_contents(getenv("SNAPSHOT_DIR") . $test_caption . '.png', $this->currentScreenshot());
                $this->assertTrue(false);
            }
            //----------USER3
            $this->sign_in($this->user3->getUsername(),$this->TEST_PASSWORD,true,true,$sessionId);
            try {
                $this->url(OW_URL_HOME . 'user/' . $this->user1->getUsername());

                $resp = $this->checkIfXPathExists("//*[contains(text(),'$test_caption') and contains(@class, 'ow_newsfeed_content ow_smallmargin')]");
                $this->assertTrue(!$resp);
                $this->url('sign-out');
            } catch (Exception $ex) {
                $this->echoText($ex, true);
                if (getenv("SNAPSHOT_DIR"))
                    file_put_contents(getenv("SNAPSHOT_DIR") . $test_caption . '.png', $this->currentScreenshot());
                $this->assertTrue(false);
            }

            //----------USER4 - FRIEND of 1
            $this->sign_in($this->user4->getUsername(),$this->TEST_PASSWORD,true,true,$sessionId);
            try {
                $this->url(OW_URL_HOME . 'user/' . $this->user1->getUsername());

                $tmp = $this->byXPath("//*[contains(text(),'$test_caption') and contains(@class, 'ow_newsfeed_content ow_smallmargin')]");
                $tag_parent = $tmp->byXPath("../../..");
                $tag_parent->byXPath("//*[contains(text(),'1') and contains(@class, 'newsfeed_counter_likes')]");
                sleep(1);
                $this->url('sign-out');
            } catch (Exception $ex) {
                $this->echoText($ex, true);
                if (getenv("SNAPSHOT_DIR"))
                    file_put_contents(getenv("SNAPSHOT_DIR") . $test_caption . '.png', $this->currentScreenshot());
                $this->assertTrue(false);
            }
        }
    }
    public function testScenario6()
    {
        // User1 sets wallwritingprivacy to friends
        // User2 posts in User1
        // User2 likes the post
        // User3 can't see the post
        // User4 can see the post and it's like
        if(true) {
            $test_caption = "privacyTest-testScenario6";
            //$this->echoText($test_caption);
            $CURRENT_SESSIONS = $this->prepareSession();
            $CURRENT_SESSIONS->currentWindow()->maximize();

            $this->url(OW_URL_HOME . "dashboard");

            //----------USER1
            $sessionId = $CURRENT_SESSIONS->cookie()->get(OW_Session::getInstance()->getName());
            $sessionId = str_replace('%2C', ',', $sessionId); // took 2 hours to detect  '/^[-,a-zA-Z0-9]{1,128}$/'
            $this->sign_in($this->user1->getUsername(),$this->TEST_PASSWORD,true,true,$sessionId);
            try {
                $this->url(OW_URL_HOME.'profile/privacy');
                //only_for_me, everybody, friends_only
                $this->hide_element('demo-nav');
                $privacyItem = $this->byName('who_post_on_newsfeed');
                $privacyItem->byXPath('option[@value="friends_only"]')->click();
                $this->byXPath('//input[@name="privacySubmit"]')->click();
                sleep(1);
                $this->url('sign-out');
            } catch (Exception $ex) {
                $this->echoText($ex, true);
                if (getenv("SNAPSHOT_DIR"))
                    file_put_contents(getenv("SNAPSHOT_DIR") . $test_caption . '.png', $this->currentScreenshot());
                $this->assertTrue(false);
            }

            //----------USER2 - FRIEND of 1
            $this->sign_in($this->user2->getUsername(),$this->TEST_PASSWORD,true,true,$sessionId);
            try {
                $this->url(OW_URL_HOME . 'user/' . $this->user1->getUsername());
                $this->hide_element('demo-nav');
                $form = $this->byId('feed1');
                $form->byName('status')->value($test_caption);
                $form->byName('save')->click();
                sleep(1);
                $tmp = $this->byXPath("//*[contains(text(),'$test_caption') and contains(@class, 'ow_newsfeed_content ow_smallmargin')]");
                $tag_parent = $tmp->byXPath("../../..");
                $tag_parent->byCssSelector('.newsfeed_like_btn_cont')->click();
                $this->url('sign-out');
            } catch (Exception $ex) {
                $this->echoText($ex, true);
                if (getenv("SNAPSHOT_DIR"))
                    file_put_contents(getenv("SNAPSHOT_DIR") . $test_caption . '.png', $this->currentScreenshot());
                $this->assertTrue(false);
            }
            //----------USER3
            $this->sign_in($this->user3->getUsername(),$this->TEST_PASSWORD,true,true,$sessionId);
            try {
                $this->url(OW_URL_HOME . 'user/' . $this->user1->getUsername());

                $resp = $this->checkIfXPathExists("//*[contains(text(),'$test_caption') and contains(@class, 'ow_newsfeed_content ow_smallmargin')]");
                $this->assertTrue(!$resp);
                $this->url('sign-out');
            } catch (Exception $ex) {
                $this->echoText($ex, true);
                if (getenv("SNAPSHOT_DIR"))
                    file_put_contents(getenv("SNAPSHOT_DIR") . $test_caption . '.png', $this->currentScreenshot());
                $this->assertTrue(false);
            }

            //----------USER4 - FRIEND of 1
            $this->sign_in($this->user4->getUsername(),$this->TEST_PASSWORD,true,true,$sessionId);
            try {
                $this->url(OW_URL_HOME . 'user/' . $this->user1->getUsername());

                $tmp = $this->byXPath("//*[contains(text(),'$test_caption') and contains(@class, 'ow_newsfeed_content ow_smallmargin')]");
                $tag_parent = $tmp->byXPath("../../..");
                $tag_parent->byXPath("//*[contains(text(),'1') and contains(@class, 'newsfeed_counter_likes')]");
                sleep(1);
                $this->url('sign-out');
            } catch (Exception $ex) {
                $this->echoText($ex, true);
                if (getenv("SNAPSHOT_DIR"))
                    file_put_contents(getenv("SNAPSHOT_DIR") . $test_caption . '.png', $this->currentScreenshot());
                $this->assertTrue(false);
            }
        }
    }
    public function testScenario7()
    {
        // User2 posts in User1
        // User1 changes last_post_of_others_newsfeed to only_for_me
        // User4 can't see the post
        if(true) {
            $test_caption = "privacyTest-testScenario7";
            //$this->echoText($test_caption);
            $CURRENT_SESSIONS = $this->prepareSession();
            $CURRENT_SESSIONS->currentWindow()->maximize();

            $this->url(OW_URL_HOME . "dashboard");

            $sessionId = $CURRENT_SESSIONS->cookie()->get(OW_Session::getInstance()->getName());
            $sessionId = str_replace('%2C', ',', $sessionId); // took 2 hours to detect  '/^[-,a-zA-Z0-9]{1,128}$/'
            //----------USER2 - FRIEND of 1
            $this->sign_in($this->user2->getUsername(),$this->TEST_PASSWORD,true,true,$sessionId);
            try {
                $this->url(OW_URL_HOME . 'user/' . $this->user1->getUsername());
                $this->hide_element('demo-nav');
                $form = $this->byId('feed1');
                $form->byName('status')->value($test_caption);
                $form->byName('save')->click();
                sleep(1);
                $form->byName('status')->value($test_caption);
                $form->byName('save')->click();
                sleep(1);
                $this->url('sign-out');
            } catch (Exception $ex) {
                $this->echoText($ex, true);
                if (getenv("SNAPSHOT_DIR"))
                    file_put_contents(getenv("SNAPSHOT_DIR") . $test_caption . '.png', $this->currentScreenshot());
                $this->assertTrue(false);
            }
            //----------USER1
            $this->sign_in($this->user1->getUsername(),$this->TEST_PASSWORD,true,true,$sessionId);
            try {
                $this->url(OW_URL_HOME.'profile/privacy');
                $this->hide_element('demo-nav');
                //only_for_me, everybody, friends_only
                //others post
                $privacyItem = $this->byName('other_post_on_feed_newsfeed');
                $privacyItem->byXPath('option[@value="only_for_me"]')->click();

                //last posts of others
                $privacyItem = $this->byName('last_post_of_others_newsfeed');
                $privacyItem->byXPath('option[@value="only_for_me"]')->click();
                $this->byXPath('//input[@name="privacySubmit"]')->click();
                sleep(2);
                $this->url('sign-out');
            }catch (Exception $ex) {
                $this->echoText($ex, true);
                if (getenv("SNAPSHOT_DIR"))
                    file_put_contents(getenv("SNAPSHOT_DIR") . $test_caption . '.png', $this->currentScreenshot());
                $this->assertTrue(false);
            }
            //--------------CRON JOB
            OW::getConfig()->addConfig("issa","weAreTesting",true);
            $cron_dir = "\"".OW_DIR_ROOT."ow_cron".DS."run.php\"";
            echo exec("php ".$cron_dir) ;
            OW::getConfig()->deleteConfig("issa","weAreTesting");
            sleep(1);

            //----------USER4
            $this->sign_in($this->user4->getUsername(),$this->TEST_PASSWORD,true,true,$sessionId);
            try {
                $this->url(OW_URL_HOME . 'user/' . $this->user1->getUsername());
                sleep(1);
                $resp = $this->checkIfXPathExists("//*[contains(text(),'$test_caption') and contains(@class, 'ow_newsfeed_content ow_smallmargin')]");
                if($resp) {
                    $this->echoText('cron failed');
                    PRIVACY_BOL_ActionService::getInstance()->cronUpdatePrivacy(); //direct run
                    $this->url(OW_URL_HOME . 'user/' . $this->user1->getUsername());
                    $resp = $this->checkIfXPathExists("//*[contains(text(),'$test_caption') and contains(@class, 'ow_newsfeed_content ow_smallmargin')]");
                    $this->assertTrue(!$resp);
                    $this->echoText('direct cron succeed');
                }
                $this->url('sign-out');
            } catch (Exception $ex) {
                $this->echoText($ex, true);
                if (getenv("SNAPSHOT_DIR"))
                    file_put_contents(getenv("SNAPSHOT_DIR") . $test_caption . '.png', $this->currentScreenshot());
                $this->assertTrue(false);
            }
        }
    }

    public function tearDown()
    {
        $questionDao = BOL_QuestionService::getInstance();
        $userDao = BOL_UserDao::getInstance();
        $friendsQuestionService = FRIENDS_BOL_Service::getInstance();

        $friendsQuestionService->deleteUserFriendships($this->user1->getId());
        $questionDao->deleteQuestionDataByUserId($this->user1->getId());
        $userDao->deleteById($this->user1->getId());

        $questionDao->deleteQuestionDataByUserId($this->user2->getId());
        $userDao->deleteById($this->user2->getId());

        $questionDao->deleteQuestionDataByUserId($this->user3->getId());
        $userDao->deleteById($this->user3->getId());

        $questionDao->deleteQuestionDataByUserId($this->user4->getId());
        $userDao->deleteById($this->user4->getId());
    }
}
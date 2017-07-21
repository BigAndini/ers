<?php

namespace Tests\Acceptance;

use \AcceptanceTester;

class SigninCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    public function _after(AcceptanceTester $I)
    {
    }

    public function testSignup(AcceptanceTester $I)
    {
        $I->wantTo('sign in');

        $I->amOnPage('/user/login');
        $I->see('Login');
        $I->fillField('identity', "andi@inbaz.org");
        $I->fillField('credential', 'phoo8Bie');
        $I->fillField('remember_me', '1');
        #$I->fillField('passwordVerify', 'onsite.reg531');
        $I->click('Sign In');

        $I->seeCurrentUrlEquals('/profile');
        $I->see('My Profile');
        $I->seeLink('Logout', '/user/logout');
    }

}

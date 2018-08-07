<?php

namespace Tests\Acceptance;

use \AcceptanceTester;

class PreregInfoControllerCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    public function _after(AcceptanceTester $I)
    {
    }

    public function testIndex(AcceptanceTester $I)
    {
        $I->wantTo('visit home page');

        $I->amOnPage('/');
        $I->see('Welcome to the Event Registration System');
    }
    public function testForms(AcceptanceTester $I) {
        $I->wantTo('visit parental forms page');

        $I->amOnPage('/info/forms');
        $I->see('Forms for participants of age under 16 and under 18');
    }
    public function testTerms(AcceptanceTester $I) {
        $I->wantTo('visit terms page');

        $I->amOnPage('/info/terms');
        $I->see('Stichting European Juggling Association');
    }
    public function testImpressum(AcceptanceTester $I) {
        $I->wantTo('visit impressum page');

        $I->amOnPage('/info/impressum');
        $I->see('Stichting European Juggling Association');
    }
    public function testCookie(AcceptanceTester $I) {
        $I->wantTo('visit cookie page');

        $I->amOnPage('/info/cookie');
        $I->see('Stichting European Juggling Association');
    }
    public function testHelp(AcceptanceTester $I) {
        $I->wantTo('visit help page');

        $I->amOnPage('/info/help');
        $I->see('Stichting European Juggling Association');
    }
    public function testEticket(AcceptanceTester $I) {
        $I->wantTo('visit e-ticket page');

        $I->amOnPage('/info/e-ticket');
        $I->see('Stichting European Juggling Association');
    }


}

<?php

namespace Tests\Acceptance;

use \AcceptanceTester;

class PreregBuyerControllerCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    public function _after(AcceptanceTester $I)
    {
    }

    public function testIndex(AcceptanceTester $I)
    {
        $I->wantTo('visit buyer page');

        $I->amOnPage('/buyer');
        $I->see('Stichting European Juggling Association');
    }
    public function addForms(AcceptanceTester $I) {
        $I->wantTo('visit add buyer page');

        $I->amOnPage('/buyer/add');
        $I->see('Stichting European Juggling Association');
    }
    public function testTerms(AcceptanceTester $I) {
        $I->wantTo('visit edit buyer page');

        $I->amOnPage('/buyer/edit');
        $I->see('Stichting European Juggling Association');
    }
    public function testImpressum(AcceptanceTester $I) {
        $I->wantTo('visit delete buyer page');

        $I->amOnPage('/buyer/delete');
        $I->see('Stichting European Juggling Association');
    }
}

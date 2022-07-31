<?php

class FirstCest
{
    public function department(AcceptanceTester $I)
    {
        $I->amOnPage('/');
        $I->Click('Войти');
        $I->fillField(['id' => 'login_main_login'], 'user@user.com');
        $I->fillField(['id' => 'psw_main_login'], 'myuser');
        $I->Click('form[name=main_login_form] button[type="submit"]');
        $I->Click('Мой профиль');
        $I->see('Отделы');
        $I->Click('Отделы');
        $I->see('007 Агент');
        $I->Click('Департамент');
        $I->see('4');
        $I->Click('Мой профиль');
        $I->Click('Выйти');
        $I->makeHtmlSnapShot();


    }
}

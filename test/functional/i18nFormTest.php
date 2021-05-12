<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$app = 'i18n';
if (!include(__DIR__.'/../bootstrap/functional.php'))
{
  return;
}

$b = new sfTestBrowser();

// default culture (en)
$b->get('/en/i18n/i18nForm')
  ->with('request', function (sfTesterRequest $request) {
    $request->isParameter('module', 'i18n');
    $request->isParameter('action', 'i18nForm');
  })
  ->with('user', function (sfTesterUser $user) {
    $user->isCulture('en');
  })
  ->with('response', function (sfTesterResponse $response) {
    $response->isStatusCode(200);
    $response->checkElement('label', 'First name', ['position' => 0]);
    $response->checkElement('label', 'Last name', ['position' => 1]);
    $response->checkElement('label', 'Email address', ['position' => 2]);
    $response->checkElement('td', '/Put your first name here/i', ['position' => 0]);
  })
  ->setField('i18n[email]', 'foo/bar')
  ->click('Submit')
  ->with('response', function (sfTesterResponse $response) {
    $response->checkElement('ul li', 'Required.', ['position' => 0]);
    $response->checkElement('ul li', 'foo/bar is an invalid email address', ['position' => 2]);
  })
;

// changed culture (fr)
$b->get('/fr/i18n/i18nForm')
  ->with('request', function (sfTesterRequest $request) {
    $request->isParameter('module', 'i18n');
    $request->isParameter('action', 'i18nForm');
  })
  ->with('user', function (sfTesterUser $user) {
    $user->isCulture('fr');
  })
  ->with('response', function (sfTesterResponse $response) {
    $response->isStatusCode(200);
    $response->checkElement('label', 'Prénom', ['position' => 0]);
    $response->checkElement('label', 'Nom', ['position' => 1]);
    $response->checkElement('label', 'Adresse email', ['position' => 2]);
    $response->checkElement('td', '/Mettez votre prénom ici/i', ['position' => 0]);
  })
  ->setField('i18n[email]', 'foo/bar')
  ->click('Submit')
  ->with('response', function (sfTesterResponse $response) {
    $response->checkElement('ul li', 'Champ requis.', ['position' => 0]);
    $response->checkElement('ul li', 'foo/bar est une adresse email invalide', ['position' => 2]);
  })
;

// forms label custom catalogue test
$b->get('/fr/i18n/i18nCustomCatalogueForm')
  ->with('request', function (sfTesterRequest $request) {
    $request->isParameter('module', 'i18n');
    $request->isParameter('action', 'i18nCustomCatalogueForm');
  })
  ->with('user', function (sfTesterUser $user) {
    $user->isCulture('fr');
  })
  ->with('response', function (sfTesterResponse $response) {
    $response->isStatusCode(200);
    $response->checkElement('label', 'Prénom!!!', ['position' => 0]);
    $response->checkElement('label', 'Nom!!!', ['position' => 1]);
    $response->checkElement('label', 'Adresse email!!!', ['position' => 2]);
  })
;

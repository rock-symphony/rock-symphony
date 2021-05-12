<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$app = 'frontend';
$debug = false;
if (!include(__DIR__.'/../bootstrap/functional.php'))
{
  return;
}

$b = new sfTestBrowser();

// default main page (without cache)
$b->get('/')
  ->with('request', function (sfTesterRequest $request) {
    $request->isParameter('module', 'default');
    $request->isParameter('action', 'index');
  })
  ->with('response', function (sfTesterResponse $response) {
    $response->isStatusCode(200);
    $response->checkElement('body', '/congratulations/i');
  })
;

// 404
$b->get('/nonexistant')
  ->with('request', function (sfTesterRequest $request) {
    $request->isForwardedTo('default', 'error404');
  })
  ->with('response', function (sfTesterResponse $response) {
    $response->isStatusCode(404);
    $response->checkElement('body', '!/congratulations/i');
    $response->checkElement('link[href="/sf/sf_default/css/screen.css"]');
  })
;

$b->get('/nonexistant/')
  ->with('request', function (sfTesterRequest $request) {
    $request->isForwardedTo('default', 'error404');
  })
  ->with('response', function (sfTesterResponse $response) {
    $response->isStatusCode(404);
    $response->checkElement('body', '!/congratulations/i');
    $response->checkElement('link[href="/sf/sf_default/css/screen.css"]');
  })
;

// unexistant action
$b->get('/default/nonexistantaction')
  ->with('request', function (sfTesterRequest $request) {
    $request->isForwardedTo('default', 'error404');
  })
  ->with('response', function (sfTesterResponse $response) {
    $response->isStatusCode(404);
    $response->checkElement('body', '!/congratulations/i');
    $response->checkElement('link[href="/sf/sf_default/css/screen.css"]');
  })
;

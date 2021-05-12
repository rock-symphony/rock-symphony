<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$app = 'frontend';
if (!include(__DIR__.'/../bootstrap/functional.php'))
{
  return;
}

$b = new sfTestBrowser();

// default main page
$b->getAndCheck('default', 'index', '/')
  ->with('response', function (sfTesterResponse $response) {
    $response->checkElement('body', '/congratulations/i');
    $response->checkElement('link[href="/sf/sf_default/css/screen.css"]');
    $response->checkElement('link[href="/css/main.css"]');
    $response->checkElement('link[href="/css/multiple_media.css"][media="print,handheld"]');
    $response->matches('#' . preg_quote('<!--[if lte IE 6]><link rel="stylesheet" type="text/css" media="screen" href="/css/ie6.css" /><![endif]-->') . '#');
  })
;

// default 404
$b->get('/nonexistant')
  ->with('response', function (sfTesterResponse $response) {
    $response->isStatusCode(404);
  })
;
/*
$b->
  get('/nonexistant/')->
  isStatusCode(404)
;
*/

// unexistant action
$b->get('/default/nonexistantaction')
  ->with('response', function (sfTesterResponse $response) {
    $response->isStatusCode(404);
  })
;

// module.yml: enabled
$b->get('/configModuleDisabled')
  ->with('request', function (sfTesterRequest $request) {
    $request->isForwardedTo('default', 'disabled');
  })
  ->with('response', function (sfTesterResponse $response) {
    $response->isStatusCode(200);
    $response->checkElement('body', '/module is unavailable/i');
    $response->checkElement('body', '!/congratulations/i');
    $response->checkElement('link[href="/sf/sf_default/css/screen.css"]');
  })
;

// view.yml: has_layout
$b->get('/configViewHasLayout/withoutLayout')
  ->with('response', function (sfTesterResponse $response) {
    $response->isStatusCode(200);
    $response->checkElement('body', '/no layout/i');
    $response->checkElement('head title', false);
  })
;

// security.yml: is_secure
$b->get('/configSecurityIsSecure')
  ->with('request', function (sfTesterRequest $request) {
    $request->isForwardedTo('default', 'login');
  })
  ->with('response', function (sfTesterResponse $response) {
    $response->isStatusCode(200);
    $response->checkElement('body', '/Login Required/i');

    // check that there is no double output caused by the forwarding in a filter
    $response->checkElement('body', 1);
    $response->checkElement('link[href="/sf/sf_default/css/screen.css"]');
  })
;

// security.yml: case sensitivity
$b->get('/configSecurityIsSecureAction/index')
  ->with('request', function (sfTesterRequest $request) {
    $request->isForwardedTo('default', 'login');
  })
  ->with('response', function (sfTesterResponse $response) {
    $response->isStatusCode(200);
    $response->checkElement('body', '/Login Required/i');
  })
;

$b->get('/configSecurityIsSecureAction/Index')
  ->with('request', function (sfTesterRequest $request) {
    $request->isForwardedTo('default', 'login');
  })
  ->with('response', function (sfTesterResponse $response) {
    $response->isStatusCode(200);
    $response->checkElement('body', '/Login Required/i');
  })
;

// Max forwards
$b->get('/configSettingsMaxForwards/selfForward')
  ->with('response', function (sfTesterResponse $response) {
    $response->isStatusCode(500);
    $response->throwsException(null, '/Too many forwards have been detected for this request/i');
  });

// filters.yml: add a filter
$b->get('/configFiltersSimpleFilter')
  ->with('response', function (sfTesterResponse $response) {
    $response->isStatusCode(200);
    $response->checkElement('body', '/in a filter/i');
    $response->checkElement('body', '!/congratulation/i');
  })
;

// css and js inclusions
$b->get('/assetInclusion/index')
  ->with('response', function (sfTesterResponse $response) {
    $response->isStatusCode(200);
    $response->checkElement('head link[rel="stylesheet"]', false);
    $response->checkElement('head script[type="text/javascript"]', false);
  })
;

// renderText
$b->get('/renderText')
  ->with('response', function (sfTesterResponse $response) {
    $response->isStatusCode(200);
    $response->matches('/foo/');
  })
;

// view.yml when changing template
$b->get('/view')
  ->with('response', function (sfTesterResponse $response) {
    $response->isStatusCode(200);
    $response->isHeader('Content-Type', 'text/html; charset=utf-8');
    $response->checkElement('head title', 'foo title');
  })
;

// view.yml with other than default content-type
$b->get('/view/plain')
  ->with('response', function (sfTesterResponse $response) {
    $response->isHeader('Content-Type', 'text/plain; charset=utf-8');
    $response->isStatusCode(200);
    $response->matches('/<head>/');
    $response->matches('/plaintext/');
  })
;

// view.yml with other than default content-type and no layout
$b->get('/view/image')
  ->with('response', function (sfTesterResponse $response) {
    $response->isStatusCode(200);
    $response->isHeader('Content-Type', 'image/jpg');
    $response->matches('/image/');
  })
;

// getPresentationFor()
$b->get('/presentation')
  ->with('response', function (sfTesterResponse $response) {
    $response->isStatusCode(200);
    $response->checkElement('#foo', 'foo');
    $response->checkElement('#foo_bis', 'foo');
  })
;

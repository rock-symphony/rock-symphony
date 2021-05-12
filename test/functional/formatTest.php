<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
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

$b->get('/format_test.js')
  ->with('request', function (sfTesterRequest $request) {
    $request->isParameter('module', 'format');
    $request->isParameter('action', 'index');
    $request->isFormat('js');
  })
  ->with('response', function (sfTesterResponse $response) {
    $response->isStatusCode(200);
    $response->isHeader('content-type', 'application/javascript');
    $response->matches('!/<body>/');
    $response->matches('/Some js headers/');
    $response->matches('/This is a js file/');
  })
;

$b->get('/format_test.css')
  ->with('request', function (sfTesterRequest $request) {
    $request->isParameter('module', 'format');
    $request->isParameter('action', 'index');
    $request->isFormat('css');
  })
  ->with('response', function (sfTesterResponse $response) {
    $response->isStatusCode(200);
    $response->isHeader('content-type', 'text/css; charset=utf-8');
    $response->matches('/This is a css file/');
  })
;

$b->get('/format_test')
  ->with('request', function (sfTesterRequest $request) {
    $request->isParameter('module', 'format');
    $request->isParameter('action', 'index');
    $request->isFormat('html');
  })
  ->with('response', function (sfTesterResponse $response) {
    $response->isStatusCode(200);
    $response->isHeader('content-type', 'text/html; charset=utf-8');
    $response->checkElement('body #content', 'This is an HTML file');
  })
;

$b->get('/format_test.xml')
  ->with('request', function (sfTesterRequest $request) {
    $request->isParameter('module', 'format');
    $request->isParameter('action', 'index');
    $request->isFormat('xml');
  })
  ->with('response', function (sfTesterResponse $response) {
    $response->isStatusCode(200);
    $response->isHeader('content-type', 'text/xml; charset=utf-8');
    $response->checkElement('sentences sentence:first', 'This is a XML file');
  })
;

$b->get('/format_test.foo')
  ->with('request', function (sfTesterRequest $request) {
    $request->isParameter('module', 'format');
    $request->isParameter('action', 'index');
    $request->isFormat('foo');
  })
  ->with('response', function (sfTesterResponse $response) {
    $response->isStatusCode(200);
    $response->isHeader('content-type', 'text/html; charset=utf-8');
    $response->isHeader('x-foo', 'true');
    $response->checkElement('body #content', 'This is an HTML file');
  })
;

$b->get('/format/js')
  ->with('request', function (sfTesterRequest $request) {
    $request->isParameter('module', 'format');
    $request->isParameter('action', 'js');
    $request->isFormat('js');
  })
  ->with('response', function (sfTesterResponse $response) {
    $response->isStatusCode(200);
    $response->isHeader('content-type', 'application/javascript');
    $response->matches('/A js file/');
  })
;

$b->setHttpHeader('User-Agent', 'Mozilla/5.0 (iPhone; U; CPU like Mac OS X; en) AppleWebKit/420+ (KHTML, like Gecko) Version/3.0 Mobile/1A543a Safari/419.3')
  ->get('/format/forTheIPhone')
  ->with('request', function (sfTesterRequest $request) {
    $request->isParameter('module', 'format');
    $request->isParameter('action', 'forTheIPhone');
    $request->isFormat('iphone');
  })
  ->with('response', function (sfTesterResponse $response) {
    $response->isStatusCode(200);
    $response->isHeader('content-type', 'text/html; charset=utf-8');
    $response->checkElement('#content', 'This is an HTML file for the iPhone');
    $response->checkElement('link[href*="iphone.css"]');
  })
;

$b->
  getAndCheck('format', 'throwsException', null, 500)->
  throwsException('Exception', '/message/')
;

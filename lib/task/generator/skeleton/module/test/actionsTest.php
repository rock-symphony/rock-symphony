<?php

include(__DIR__.'/../../bootstrap/functional.php');

$browser = new sfTestFunctional(new sfBrowser());

$browser->get('/##MODULE_NAME##/index')
  ->with('request', function (sfTesterRequest $request) {
    $request->isParameter('module', '##MODULE_NAME##');
    $request->isParameter('action', 'index');
  })
  ->with('response', function (sfTesterResponse $response) {
    $response->isStatusCode(200);
    $response->checkElement('body', '!/This is a temporary page/');
  })
;

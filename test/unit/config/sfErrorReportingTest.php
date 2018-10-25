<?php

require_once(__DIR__ . '/../../bootstrap/unit.php');

$t = new lime_test(21);

$error_reporting = new sfErrorReporting();

$t->diag('->parse() returns null for null');
$t->ok(null === $error_reporting->parse(null));

$t->diag('->parse() keeps integers with no changes');
$t->is(0, $error_reporting->parse(0), '0');
$t->is(E_ALL, $error_reporting->parse(E_ALL), 'E_ALL');
$t->is(E_WARNING, $error_reporting->parse(E_WARNING), 'E_WARNING');

$t->diag('->parse() unwraps strings to constant values');
$t->is(E_ALL, $error_reporting->parse('E_ALL'), 'E_ALL');
$t->is(E_WARNING, $error_reporting->parse('E_WARNING'), 'E_WARNING');

$t->diag('->parse() compiles strings to their values');
$t->is(E_ERROR | E_WARNING, $error_reporting->parse('E_ERROR | E_WARNING'), 'E_ERROR | E_WARNING');
$t->is(E_STRICT | E_DEPRECATED, $error_reporting->parse('E_STRICT | E_DEPRECATED'), 'E_STRICT | E_DEPRECATED');
$t->is(E_ERROR & E_WARNING, $error_reporting->parse('E_ERROR & E_WARNING'), 'E_ERROR & E_WARNING');
$t->is(E_STRICT & E_DEPRECATED, $error_reporting->parse('E_STRICT & E_DEPRECATED'), 'E_STRICT & E_DEPRECATED');
$t->is(
  (E_ALL | E_STRICT) ^ E_DEPRECATED,
  $error_reporting->parse('(E_ALL | E_STRICT) ^ E_DEPRECATED'),
  'E_ALL | E_STRICT ^ E_DEPRECATED'
);
$t->is(
  (E_ALL & E_STRICT) ^ E_NOTICE,
  $error_reporting->parse('(E_ALL & E_STRICT) ^ E_NOTICE'),
  'E_ALL & E_STRICT ^ E_NOTICE'
);

$t->diag('->parse() throws InvalidArgumentException for unsupported input');
foreach ([
           'boolean false'                  => false,
           'boolean true'                   => true,
           'objects'                        => new stdClass(),
           'float'                          => 1.6,
           'array'                          => [],
           'array with boolean'             => [E_ALL, false],
           'array with object'              => [E_ALL, new stdClass()],
           'array with float'               => [E_ALL, 5.6],
           'unknown error level'            => 'E_CRAZY',
         ] as $description => $unsupported_input
) {
  try {
    $error_reporting->parse($unsupported_input);
    $t->fail($description);
  } catch (InvalidArgumentException $e) {
    $t->pass($description);
  }
}

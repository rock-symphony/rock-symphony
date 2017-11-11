<?php

require_once __DIR__.'/../includes/classes.php';

$builder = new sfServiceContainerBuilder();
$builder
  ->registerServiceClass('baz', 'BazClass')
  ->setConstructor('getInstance');

$builder
  ->registerServiceClass('baz_dependent', 'BazDependentClass')
  ->setArguments([
    'baz' => new sfServiceReference('baz'),
  ]);

return $builder;

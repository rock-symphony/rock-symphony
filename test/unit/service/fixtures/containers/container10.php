<?php

require_once __DIR__.'/../includes/classes.php';

$builder = new sfServiceContainerBuilder();
$builder
  ->registerServiceClass('BazClass', 'BazClass')
  ->setConstructor('getInstance');

$builder
  ->registerServiceClass('BazDependentClass', 'BazDependentClass');

return $builder;

<?php

$builder = new sfServiceContainerBuilder();
$builder->setParameter('FOO', 'bar');
$builder->setParameter('bar', 'foo is %foo bar');
$builder->setParameter('values', array(true, false, null, 0, 1000.3, 'true', 'false', 'null'));

return $builder;

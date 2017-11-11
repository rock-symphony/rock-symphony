<?php

function sc_configure($instance)
{
  $instance->configure();
}

class BarClass
{
}

class BazClass
{
  public function configure(FooClass $instance)
  {
    $instance->configure();
  }

  static public function getInstance()
  {
    return new self();
  }

  static public function configureStatic(FooClass $instance)
  {
    $instance->configure();
  }

  static public function configureStatic1()
  {
  }
}

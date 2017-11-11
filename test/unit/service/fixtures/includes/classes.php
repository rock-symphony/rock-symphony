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
  private function __construct()
  {
    // forbid to construct this object from outside
    // == forced singleton usage only
  }

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

class BazDependentClass
{
  /** @var \BazClass */
  public $baz;

  /**
   * BazDependentClass constructor.
   * @param \BazClass $baz
   */
  public function __construct(\BazClass $baz)
  {
    $this->baz = $baz;
  }
}

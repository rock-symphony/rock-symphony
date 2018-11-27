<?php

require_once __DIR__ . '/../vendor/autoload.php';

class ProjectConfiguration extends sfProjectConfiguration
{
  public function setup()
  {
    $this->enablePlugins('sfI18NPlugin', 'sfConfigPlugin');
  }
}

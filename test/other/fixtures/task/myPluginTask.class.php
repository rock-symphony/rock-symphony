<?php

class myPluginTask extends sfBaseTask
{
  public function configure(): void
  {
    $this->namespace = 'p';
    $this->name      = 'run';
  }

  public function execute(array $arguments = [], array $options = []): int
  {
    return 0;
  }
}

<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(__DIR__.'/../../bootstrap/unit.php');

$t = new lime_test(0);

class myStorage extends sfStorage
{
  public function read(string $key): mixed {
    return null;
  }
  public function remove(string $key): mixed {
    return null;
  }
  public function shutdown(): void {}
  public function write(string $key, mixed $data): void {}
  public function regenerate(bool $destroy = false): void {}
}

class fakeStorage
{
}

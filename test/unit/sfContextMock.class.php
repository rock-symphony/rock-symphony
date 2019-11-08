<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class sfContextMock extends sfContext
{
  /** @var string */
  private $sessionPath = '';

  static public function mockInstance(array $factories = [], bool $force = false): sfContextMock
  {
    $instance = new sfContextMock();
    $instance->sessionPath = sys_get_temp_dir().'/sessions_'.rand(11111, 99999);
    $instance->factories['storage'] = new sfSessionTestStorage(['session_path' => $instance->sessionPath]);
    $instance->dispatcher = new sfEventDispatcher();
    foreach ($factories as $type => $class) {
      $instance->inject($type, $class);
    }
    self::$instances['default'] = $instance;
    return $instance;
  }

  public function loadFactories(): void
  {
    // do nothing
  }

  public function __destruct()
  {
    sfToolkit::clearDirectory($this->sessionPath);
  }

  static public function hasInstance(string $name = null): bool
  {
    return true;
  }

  public function getModuleName(): string
  {
    return 'module';
  }

  public function getActionName(): string
  {
    return 'action';
  }

  public function inject(string $type, string $class, array $parameters = []): void
  {
    switch ($type) {
      case 'routing':
        $object = new $class($this->dispatcher, null, $parameters);
        break;
      case 'response':
        $object = new $class($this->dispatcher, $parameters);
        break;
      case 'request':
        $object = new $class($this->dispatcher, $this->getRouting(), $parameters);
        break;
      default:
        $object = new $class($this, $parameters);
    }

    $this->factories[$type] = $object;
  }
}

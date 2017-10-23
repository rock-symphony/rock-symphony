<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfCoreAutoload class.
 *
 * This class is a singleton as PHP seems to be unable to register 2 autoloaders that are instances
 * of the same class (why?).
 *
 * @package    symfony
 * @subpackage autoload
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfCoreAutoload.class.php 32415 2011-03-30 16:09:00Z Kris.Wallsmith
 *
 * @deprecated Please don't use this class.
 */
class sfCoreAutoload
{
  /** @var sfCoreAutoload|null */
  static protected $instance = null;

  /** @var string */
  protected $baseDir;

  protected function __construct()
  {
    $this->baseDir = realpath(__DIR__.'/..');
  }

  /**
   * Retrieves the singleton instance of this class.
   *
   * @return sfCoreAutoload A sfCoreAutoload implementation instance.
   */
  static public function getInstance()
  {
    if (!isset(self::$instance))
    {
      self::$instance = new sfCoreAutoload();
    }

    return self::$instance;
  }

  /**
   * Returns the base directory this autoloader is working on.
   *
   * @return string The path to the symfony core lib directory
   */
  public function getBaseDir()
  {
    return $this->baseDir;
  }
}

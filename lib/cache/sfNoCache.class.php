<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Cache class that does nothing.
 *
 * @package    symfony
 * @subpackage cache
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfNoCache extends sfCache
{
  /**
   * @see sfCache
   * @inheritdoc
   */
  public function get(string $key, $default = null): ?string
  {
    return $default;
  }

  /**
   * @see sfCache
   * @inheritdoc
   */
  public function has(string $key): bool
  {
    return false;
  }

  /**
   * @see sfCache
   * @inheritdoc
   */
  public function set(string $key, string $data, int $lifetime = null): bool
  {
    return true;
  }

  /**
   * @see sfCache
   * @inheritdoc
   */
  public function remove(string $key): bool
  {
    return true;
  }

  /**
   * @see sfCache
   * @inheritdoc
   */
  public function removePattern(string $pattern): bool
  {
    return true;
  }

  /**
   * @see sfCache
   * @inheritdoc
   */
  public function clean(int $mode = self::ALL): bool
  {
    return true;
  }

  /**
   * @see sfCache
   * @inheritdoc
   */
  public function getLastModified(string $key): int
  {
    return 0;
  }

  /**
   * @see sfCache
   * @inheritdoc
   */
  public function getTimeout(string $key): int
  {
    return 0;
  }
}

<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Cache class that stores cached content in APCu.
 *
 * @package    symfony
 * @subpackage cache
 */
class sfAPCuCache extends sfCache
{
  /** @var bool */
  protected $enabled;

  /**
   * Available options:
   *
   * * see sfCache for options available for all drivers
   *
   * @see sfCache
   * @inheritdoc
   */
  public function __construct(array $options = [])
  {
    parent::__construct($options);

    $this->enabled = function_exists('apcu_store') && ini_get('apc.enabled');
  }

  /**
   * @see sfCache
   * @inheritdoc
   */
  public function get(string $key, $default = null): ?string
  {
    if (!$this->enabled)
    {
      return $default;
    }

    $value = $this->fetch($this->getOption('prefix').$key, $has);

    return $has ? $value : $default;
  }

  /**
   * @see sfCache
   * @inheritdoc
   */
  public function has(string $key): bool
  {
    if (!$this->enabled)
    {
      return false;
    }

    $this->fetch($this->getOption('prefix').$key, $has);

    return $has;
  }

  private function fetch(string $key, &$success)
  {
    $has = null;
    $value = apcu_fetch($key, $has);
    // the second argument was added in APC 3.0.17. If it is still null we fall back to the value returned
    if (null !== $has)
    {
      $success = $has;
    }
    else
    {
      $success = $value !== false;
    }

    return $value;
  }


  /**
   * @see sfCache
   * @inheritdoc
   */
  public function set(string $key, string $data, int $lifetime = null): bool
  {
    if (!$this->enabled)
    {
      return true;
    }

    return apcu_store($this->getOption('prefix').$key, $data, $this->getLifetime($lifetime));
  }

  /**
   * @see sfCache
   * @inheritdoc
   */
  public function remove(string $key): bool
  {
    if (!$this->enabled)
    {
      return true;
    }

    return apcu_delete($this->getOption('prefix').$key);
  }

  /**
   * @see sfCache
   * @inheritdoc
   */
  public function clean(int $mode = sfCache::ALL): bool
  {
    if (!$this->enabled)
    {
      return true;
    }

    if (sfCache::ALL === $mode)
    {
      return apcu_clear_cache();
    }
    return false;
  }

  /**
   * @see sfCache
   * @inheritdoc
   */
  public function getLastModified(string $key): int
  {
    if ($info = $this->getCacheInfo($key))
    {
      return $info['creation_time'] + $info['ttl'] > time() ? $info['mtime'] : 0;
    }

    return 0;
  }

  /**
   * @see sfCache
   * @inheritdoc
   */
  public function getTimeout(string $key): int
  {
    if ($info = $this->getCacheInfo($key))
    {
      return $info['creation_time'] + $info['ttl'] > time() ? $info['creation_time'] + $info['ttl'] : 0;
    }

    return 0;
  }

  /**
   * @see sfCache
   * @inheritdoc
   */
  public function removePattern(string $pattern): bool
  {
    if (!$this->enabled)
    {
      return true;
    }

    $infos = apcu_cache_info();
    if (!is_array($infos['cache_list']))
    {
      return false;
    }

    $regexp = self::patternToRegexp($this->getOption('prefix').$pattern);

    foreach ($infos['cache_list'] as $info)
    {
      if (preg_match($regexp, $info['info']))
      {
        apcu_delete($info['info']);
      }
    }
    return true;
  }

  /**
   * Gets the cache info
   *
   * @param  string $key The cache key
   *
   * @return array|null
   */
  protected function getCacheInfo(string $key): ?array
  {
    if (!$this->enabled)
    {
      return null;
    }

    $infos = apcu_cache_info();

    if (is_array($infos['cache_list']))
    {
      foreach ($infos['cache_list'] as $info)
      {
        if ($this->getOption('prefix').$key == $info['info'])
        {
          return $info;
        }
      }
    }

    return null;
  }
}

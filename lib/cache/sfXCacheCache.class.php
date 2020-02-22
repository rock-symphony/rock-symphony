<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Cache class that stores cached content in XCache.
 *
 * @package    symfony
 * @subpackage cache
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfXCacheCache extends sfCache
{
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
    if (!function_exists('xcache_set'))
    {
      throw new sfInitializationException('You must have XCache installed and enabled to use sfXCacheCache class.');
    }

    if (!ini_get('xcache.var_size'))
    {
      throw new sfInitializationException('You must set the "xcache.var_size" variable to a value greater than 0 to use sfXCacheCache class.');
    }
    parent::__construct($options);
  }

 /**
  * @see sfCache
  * @inheritdoc
  */
  public function get(string $key, $default = null): ?string
  {

    $set = $this->getBaseValue($key);

    if (!is_array($set) || !array_key_exists('data', $set))
    {

      return $default;
    }

    return $set['data'];
  }

  /**
   * @see sfCache
   * @inheritdoc
   */
  public function has(string $key): bool
  {
    return xcache_isset($this->getOption('prefix').$key);
  }

  /**
   * @see sfCache
   * @inheritdoc
   */
  public function set(string $key, string $data, int $lifetime = null): bool
  {
    $lifetime = $this->getLifetime($lifetime);

    $set = array(
      'timeout' => time() + $lifetime,
      'data'    => $data,
      'ctime'   => time()
    );

    return xcache_set($this->getOption('prefix').$key, $set, $lifetime);
  }

  /**
   * @see sfCache
   * @inheritdoc
   */
  public function remove(string $key): bool
  {
    return xcache_unset($this->getOption('prefix').$key);
  }

  /**
   * @see sfCache
   * @inheritdoc
   */
  public function clean(int $mode = sfCache::ALL): bool
  {
    if ($mode !== sfCache::ALL)
    {
      return true;
    }

    $this->checkAuth();

    for ($i = 0, $max = xcache_count(XC_TYPE_VAR); $i < $max; $i++)
    {
      if (false === xcache_clear_cache(XC_TYPE_VAR, $i))
      {
        return false;
      }
    }

    return true;
  }

  /**
   * @see sfCache
   * @inheritdoc
   */
  public function getLastModified(string $key): int
  {
    $set = $this->getBaseValue($key);

    if (!is_array($set) || !array_key_exists('ctime', $set))
    {
      return 0;
    }

    return $set['ctime'];
  }

  /**
   * @see sfCache
   * @inheritdoc
   */
  public function getTimeout(string $key): int
  {

    $set = $this->getBaseValue($key);

    if (!is_array($set) || !array_key_exists('timeout', $set))
    {

      return 0;
    }

    return $set['timeout'];
  }

  /**
   * @param string $key
   * @return mixed|null
   */
  public function getBaseValue(string $key)
  {
    return xcache_isset($this->getOption('prefix').$key) ? xcache_get($this->getOption('prefix').$key) : null;
  }

  /**
   * @see sfCache
   * @inheritdoc
   */
  public function removePattern(string $pattern): bool
  {
    $this->checkAuth();

    $regexp = self::patternToRegexp($this->getOption('prefix').$pattern);

    for ($i = 0, $max = xcache_count(XC_TYPE_VAR); $i < $max; $i++)
    {
      $infos = xcache_list(XC_TYPE_VAR, $i);
      if (!is_array($infos['cache_list']))
      {
        return true;
      }

      foreach ($infos['cache_list'] as $info)
      {
        if (preg_match($regexp, $info['name']))
        {
          xcache_unset($info['name']);
        }
      }
    }
    return true;
  }

  /**
   * @param string $key
   * @return array|null
   */
  public function getCacheInfo(string $key): ?array
  {
    $this->checkAuth();

    for ($i = 0, $max = xcache_count(XC_TYPE_VAR); $i < $max; $i++)
    {
      $infos = xcache_list(XC_TYPE_VAR, $i);

      if (is_array($infos['cache_list']))
      {
        foreach ($infos['cache_list'] as $info)
        {
          if ($this->getOption('prefix').$key == $info['name'])
          {
            return $info;
          }
        }
      }
    }

    return null;
  }

  protected function checkAuth(): void
  {
    if (ini_get('xcache.admin.enable_auth'))
    {
      throw new sfConfigurationException('To use all features of the "sfXCacheCache" class, you must set "xcache.admin.enable_auth" to "Off" in your php.ini.');
    }
  }
}

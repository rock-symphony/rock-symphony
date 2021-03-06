<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Cache class that stores cached content in memcache.
 *
 * @package    symfony
 * @subpackage cache
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfMemcacheCache extends sfCache
{
  /** @var Memcache */
  protected $memcache = null;

  /**
   * Initializes this sfCache instance.
   *
   * Available options:
   *
   * * memcache: A memcache object (optional)
   *
   * * host:       The default host (default to localhost)
   * * port:       The port for the default server (default to 11211)
   * * persistent: true if the connection must be persistent, false otherwise (true by default)
   *
   * * servers:    An array of additional servers (keys: host, port, persistent)
   *
   * * see sfCache for options available for all drivers
   *
   * @see sfCache
   * @inheritdoc
   */
  public function __construct(array $options = [])
  {
    if (!class_exists('Memcache'))
    {
      throw new sfInitializationException('You must have memcache installed and enabled to use sfMemcacheCache class.');
    }

    parent::__construct($options);

    if ($this->getOption('memcache'))
    {
      $this->memcache = $this->getOption('memcache');
    }
    else
    {
      $this->memcache = new Memcache();

      if ($this->getOption('servers'))
      {
        foreach ($this->getOption('servers') as $server)
        {
          $port = isset($server['port']) ? $server['port'] : 11211;
          if (!$this->memcache->addServer($server['host'], $port, isset($server['persistent']) ? $server['persistent'] : true))
          {
            throw new sfInitializationException(sprintf('Unable to connect to the memcache server (%s:%s).', $server['host'], $port));
          }
        }
      }
      else
      {
        $method = $this->getOption('persistent', true) ? 'pconnect' : 'connect';
        if (!$this->memcache->$method($this->getOption('host', 'localhost'), $this->getOption('port', 11211), $this->getOption('timeout', 1)))
        {
          throw new sfInitializationException(sprintf('Unable to connect to the memcache server (%s:%s).', $this->getOption('host', 'localhost'), $this->getOption('port', 11211)));
        }
      }
    }
  }

  /**
   * @see sfCache
   * @return Memcache
   */
  public function getBackend(): Memcache
  {
    return $this->memcache;
  }

  /**
   * @see sfCache
   * @inheritdoc
   */
  public function get(string $key, $default = null): ?string
  {
    $value = $this->memcache->get($this->getOption('prefix').$key);

    return (false === $value && false === $this->getMetadata($key)) ? $default : $value;
  }

  /**
   * @see sfCache
   * @inheritdoc
   */
  public function has(string $key): bool
  {
    if (false === $this->memcache->get($this->getOption('prefix') . $key))
    {
      // if there is metadata, $key exists with a false value
      return !(false === $this->getMetadata($key));
    }

    return true;
  }

  /**
   * @see sfCache
   * @inheritdoc
   */
  public function set(string $key, string $data, int $lifetime = null): bool
  {
    $lifetime = null === $lifetime ? $this->getOption('lifetime') : $lifetime;

    // save metadata
    $this->setMetadata($key, $lifetime);

    // save key for removePattern()
    if ($this->getOption('storeCacheInfo', false))
    {
      $this->setCacheInfo($key);
    }

    if (false !== $this->memcache->replace($this->getOption('prefix').$key, $data, false, time() + $lifetime))
    {
      return true;
    }

    return $this->memcache->set($this->getOption('prefix').$key, $data, false, time() + $lifetime);
  }

  /**
   * @see sfCache
   * @inheritdoc
   */
  public function remove(string $key): bool
  {
    // delete metadata
    $this->memcache->delete($this->getOption('prefix').'_metadata'.self::SEPARATOR.$key, 0);
    if ($this->getOption('storeCacheInfo', false))
    {
      $this->setCacheInfo($key, true);
    }
    return $this->memcache->delete($this->getOption('prefix').$key, 0);
  }

  /**
   * @see sfCache
   * @inheritdoc
   */
  public function clean(int $mode = sfCache::ALL): bool
  {
    if (sfCache::ALL === $mode)
    {
      return $this->memcache->flush();
    }
    return false;
  }

  /**
   * @see sfCache
   * @inheritdoc
   */
  public function getLastModified(string $key): int
  {
    if (false === ($retval = $this->getMetadata($key)))
    {
      return 0;
    }

    return $retval['lastModified'];
  }

  /**
   * @see sfCache
   * @inheritdoc
   */
  public function getTimeout(string $key): int
  {
    if (false === ($retval = $this->getMetadata($key)))
    {
      return 0;
    }

    return $retval['timeout'];
  }

  /**
   * @see sfCache
   * @inheritdoc
   *
   * @throws sfCacheException
   */
  public function removePattern(string $pattern): bool
  {
    if (!$this->getOption('storeCacheInfo', false))
    {
      throw new sfCacheException('To use the "removePattern" method, you must set the "storeCacheInfo" option to "true".');
    }

    $regexp = self::patternToRegexp($this->getOption('prefix').$pattern);
    foreach ($this->getCacheInfo() as $key)
    {
      if (preg_match($regexp, $key))
      {
        $this->remove(substr($key, strlen($this->getOption('prefix'))));
      }
    }

    return true;
  }

  /**
   * @see sfCache
   * @inheritdoc
   */
  public function getMany(array $keys): array
  {
    $values = [];
    $prefix = $this->getOption('prefix');
    $prefixed_keys = array_map(function($k) use ($prefix) { return $prefix . $k; }, $keys);

    foreach ($this->memcache->get($prefixed_keys) as $key => $value)
    {
      $values[str_replace($prefix, '', $key)] = $value;
    }

    return $values;
  }

  /**
   * Gets metadata about a key in the cache.
   *
   * @param string $key A cache key
   *
   * @return array An array of metadata information
   */
  protected function getMetadata(string $key): array
  {
    return $this->memcache->get($this->getOption('prefix').'_metadata'.self::SEPARATOR.$key);
  }

  /**
   * Stores metadata about a key in the cache.
   *
   * @param string $key      A cache key
   * @param int $lifetime The lifetime
   */
  protected function setMetadata(string $key, int $lifetime): void
  {
    $this->memcache->set($this->getOption('prefix').'_metadata'.self::SEPARATOR.$key, array('lastModified' => time(), 'timeout' => time() + $lifetime), false, time() + $lifetime);
  }

  /**
   * Updates the cache information for the given cache key.
   *
   * @param string $key The cache key
   * @param boolean $delete Delete key or not
   */
  protected function setCacheInfo(string $key, bool $delete = false): void
  {
    $keys = $this->memcache->get($this->getOption('prefix').'_metadata');
    if (!is_array($keys))
    {
      $keys = array();
    }

    if ($delete)
    {
       if (($k = array_search($this->getOption('prefix').$key, $keys)) !== false)
       {
         unset($keys[$k]);
       }
    }
    else
    {
      if (!in_array($this->getOption('prefix').$key, $keys))
      {
        $keys[] = $this->getOption('prefix').$key;
      }
    }

    $this->memcache->set($this->getOption('prefix').'_metadata', $keys, 0);
  }

  /**
   * Gets cache information.
   *
   * @return array
   */
  protected function getCacheInfo(): array
  {
    $keys = $this->memcache->get($this->getOption('prefix') . '_metadata');
    if (!is_array($keys)) {
      return [];
    }

    return $keys;
  }
}

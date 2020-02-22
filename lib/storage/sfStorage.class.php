<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) 2004-2006 Sean Kerr <sean@code-box.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfStorage allows you to customize the way symfony stores its persistent data.
 *
 * @package    symfony
 * @subpackage storage
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <sean@code-box.org>
 * @version    SVN: $Id$
 */
abstract class sfStorage
{
  /** @var array */
  protected $options = [];

  /**
   * Class constructor.
   *
   * Available options:
   *
   *  * auto_shutdown: Whether to automatically save the changes to the session (true by default)
   *
   * @param  array $options  An associative array of options
   *
   * @throws sfInitializationException If an error occurs while initializing this sfStorage
   */
  public function __construct($options = [])
  {
    $this->options = array_merge(
      ['auto_shutdown' => true],
      $options
    );

    if ($this->options['auto_shutdown'])
    {
      register_shutdown_function(array($this, 'shutdown'));
    }
  }

  /**
   * Returns the option array.
   *
   * @return array The array of options
   */
  public function getOptions(): array
  {
    return $this->options;
  }

  /**
   * Reads data from this storage.
   *
   * The preferred format for a key is directory style so naming conflicts can be avoided.
   *
   * @param  string $key  A unique key identifying your data
   *
   * @return mixed Data associated with the key
   *
   * @throws <b>sfStorageException</b> If an error occurs while reading data from this storage
   */
  abstract public function read(string $key);

  /**
   * Regenerates id that represents this storage.
   *
   * @param  boolean $destroy Destroy session when regenerating?
   *
   * @throws <b>sfStorageException</b> If an error occurs while regenerating this storage
   */
  abstract public function regenerate(bool $destroy = false): void;

  /**
   * Removes data from this storage.
   *
   * The preferred format for a key is directory style so naming conflicts can be avoided.
   *
   * @param  string $key  A unique key identifying your data
   *
   * @return mixed Data associated with the key
   *
   * @throws <b>sfStorageException</b> If an error occurs while removing data from this storage
   */
  abstract public function remove(string $key);

  /**
   * Executes the shutdown procedure.
   *
   * @throws <b>sfStorageException</b> If an error occurs while shutting down this storage
   */
  abstract public function shutdown(): void;

  /**
   * Writes data to this storage.
   *
   * The preferred format for a key is directory style so naming conflicts can be avoided.
   *
   * @param  string $key   A unique key identifying your data
   * @param  mixed  $data  Data associated with your key
   *
   * @throws <b>sfStorageException</b> If an error occurs while writing to this storage
   */
  abstract public function write(string $key, $data): void;
}

<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfSessionTestStorage is a fake sfSessionStorage implementation to allow easy testing.
 *
 * @package    symfony
 * @subpackage storage
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfSessionTestStorage extends sfStorage
{
  /** @var string */
  protected $sessionId;
  /** @var array */
  protected $sessionData = [];

  /**
   * Available options:
   *
   *  * session_path: The path to store the session files
   *  * session_id:   The session identifier
   *
   * @param array $options  An associative array of options
   *
   * @see sfStorage
   */
  public function __construct(array $options = [])
  {
    if (!isset($options['session_path']))
    {
      throw new InvalidArgumentException('The "session_path" option is mandatory for the sfSessionTestStorage class.');
    }

    $options = array_merge(array(
      'session_id'   => null,
    ), $options);

    // initialize parent
    parent::__construct($options);

    $this->sessionId = null !== $this->options['session_id'] ? $this->options['session_id'] : (array_key_exists('session_id', $_SERVER) ? $_SERVER['session_id'] : null);

    if ($this->sessionId)
    {
      // we read session data from temp file
      $file = $this->options['session_path'].DIRECTORY_SEPARATOR.$this->sessionId.'.session';
      $this->sessionData = is_file($file) ? unserialize(file_get_contents($file)) : array();
    }
    else
    {
      $this->sessionId   = md5(uniqid(mt_rand(), true));
      $this->sessionData = array();
    }
  }

  /**
   * Gets session id for the current session storage instance.
   *
   * @return string Session id
   */
  public function getSessionId(): string
  {
    return $this->sessionId;
  }

  /**
   * Reads data from this storage.
   *
   * The preferred format for a key is directory style so naming conflicts can be avoided.
   *
   * @param  string $key  A unique key identifying your data
   *
   * @return mixed Data associated with the key
   */
  public function read(string $key)
  {
    $retval = null;

    if (isset($this->sessionData[$key]))
    {
      $retval = $this->sessionData[$key];
    }

    return $retval;
  }

  /**
   * Removes data from this storage.
   *
   * The preferred format for a key is directory style so naming conflicts can be avoided.
   *
   * @param  string $key  A unique key identifying your data
   *
   * @return mixed Data associated with the key
   */
  public function remove(string $key)
  {
    $retval = null;

    if (isset($this->sessionData[$key]))
    {
      $retval = $this->sessionData[$key];
      unset($this->sessionData[$key]);
    }

    return $retval;
  }

  /**
   * Writes data to this storage.
   *
   * The preferred format for a key is directory style so naming conflicts can be avoided
   *
   * @param string $key   A unique key identifying your data
   * @param mixed  $data  Data associated with your key
   *
   */
  public function write(string $key, $data): void
  {
    $this->sessionData[$key] = $data;
  }

  /**
   * Clears all test sessions.
   */
  public function clear(): void
  {
    sfToolkit::clearDirectory($this->options['session_path']);
  }

  /**
   * Regenerates id that represents this storage.
   *
   * @param  boolean $destroy Destroy session when regenerating?
   */
  public function regenerate(bool $destroy = false): void
  {
  }

  /**
   * Executes the shutdown procedure.
   *
   */
  public function shutdown(): void
  {
    if ($this->sessionId)
    {
      $current_umask = umask(0000);
      $sessionsDir   = $this->options['session_path'];
      if (!is_dir($sessionsDir) && !@mkdir($sessionsDir, 0777, true) && !is_dir($sessionsDir))
      {
        throw new \RuntimeException(sprintf('Logger was not able to create a directory "%s"', $sessionsDir));
      }
      umask($current_umask);
      file_put_contents($sessionsDir.DIRECTORY_SEPARATOR.$this->sessionId.'.session', serialize($this->sessionData));
      $this->sessionId   = '';
      $this->sessionData = array();
    }
  }
}

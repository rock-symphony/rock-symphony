<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Output escaping decorator class for arrays.
 *
 * @see        sfOutputEscaper
 * @package    symfony
 * @subpackage view
 * @author     Mike Squire <mike@somosis.co.uk>
 * @version    SVN: $Id$
 */
class sfOutputEscaperArrayDecorator extends sfOutputEscaperGetterDecorator implements IteratorAggregate, ArrayAccess, Countable
{
  /**
   * Constructor.
   *
   * @see sfOutputEscaper
   * @inheritdoc
   */
  public function __construct(callable $escapingMethod, array $value)
  {
    parent::__construct($escapingMethod, $value);
  }

  public function getIterator(): Traversable
  {
    foreach ($this->value as $key => $value) {
      yield $key => sfOutputEscaper::escape($this->escapingMethod, $value);
    }
  }

  /**
   * Returns true if the supplied offset isset in the array (as required by the ArrayAccess interface).
   *
   * @param  string $offset  The offset of the value to check existance of
   *
   * @return bool true if the offset isset; false otherwise
   */
  public function offsetExists($offset): bool
  {
    return isset($this->value[$offset]);
  }

  /**
   * Returns the element associated with the offset supplied (as required by the ArrayAccess interface).
   *
   * @param  string $offset  The offset of the value to get
   *
   * @return mixed The escaped value
   */
  public function offsetGet(mixed $offset = ''): mixed
  {
    return sfOutputEscaper::escape($this->escapingMethod, $this->value[$offset]);
  }

  /**
   * Throws an exception saying that values cannot be set (this method is
   * required for the ArrayAccess interface).
   *
   * This (and the other sfOutputEscaper classes) are designed to be read only
   * so this is an illegal operation.
   *
   * @param  string $offset  (ignored)
   * @param  string $value   (ignored)
   *
   * @throws sfException
   */
  public function offsetSet(mixed $offset = '', mixed $value = ''): void
  {
    throw new sfException('Cannot set values.');
  }

  /**
   * Throws an exception saying that values cannot be unset (this method is
   * required for the ArrayAccess interface).
   *
   * This (and the other sfOutputEscaper classes) are designed to be read only
   * so this is an illegal operation.
   *
   * @param  string $offset  (ignored)
   *
   * @throws sfException
   */
  public function offsetUnset(mixed $offset = ''): void
  {
    throw new sfException('Cannot unset values.');
  }

  /**
   * Returns the size of the array (are required by the Countable interface).
   *
   * @return int The size of the array
   */
  public function count(): int
  {
    return count($this->value);
  }

  /**
   * Returns the (unescaped) value from the array associated with the key supplied.
   *
   * @param  string $key  The key into the array to use
   *
   * @return mixed The value
   */
  public function getRaw($key)
  {
    return $this->value[$key];
  }
}

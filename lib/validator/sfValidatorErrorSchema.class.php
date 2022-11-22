<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfValidatorErrorSchema represents a validation schema error.
 *
 * @package    symfony
 * @subpackage validator
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfValidatorErrorSchema extends sfValidatorError implements ArrayAccess, IteratorAggregate, Countable
{
  /**
   * @var list<sfValidatorError>
   */
  protected array $errors = [];
  /**
   * @var list<sfValidatorError>
   */
  protected array $globalErrors = [];
  /**
   * @var array<string,sfValidatorError>
   */
  protected array $namedErrors = [];

  /**
   * Constructor.
   *
   * @param sfValidatorBase $validator  An sfValidatorBase instance
   * @param array           $errors     An array of errors, depreciated
   */
  public function __construct(sfValidatorBase $validator, array $errors = [])
  {
    $this->validator = $validator;
    $this->arguments = [];

    // override default exception message and code
    $this->code    = '';
    $this->message = '';

    foreach ($errors as $name => $error)
    {
      $this->addError($error, is_numeric($name) ? null : $name);
    }
  }

  /**
   * Adds an error.
   *
   * This method merges sfValidatorErrorSchema errors with the current instance.
   *
   * @param sfValidatorError  $error An sfValidatorError instance
   * @param string|null       $name  The error name
   *
   * @return $this The current error schema instance
   */
  public function addError(sfValidatorError $error, string $name = null)
  {
    if (null === $name)
    {
      if ($error instanceof sfValidatorErrorSchema)
      {
        $this->addErrors($error);
      }
      else
      {
        $this->globalErrors[] = $error;
        $this->errors[] = $error;
      }
    }
    else if (isset($this->namedErrors[$name]))
    {
      if (!$this->namedErrors[$name] instanceof sfValidatorErrorSchema)
      {
        $current = $this->namedErrors[$name];
        $this->namedErrors[$name] = new sfValidatorErrorSchema($current->getValidator());
        $this->namedErrors[$name]->addError($current);
      }

      $method = $error instanceof sfValidatorErrorSchema ? 'addErrors' : 'addError';
      $this->namedErrors[$name]->$method($error);

      $this->errors[$name] = $this->namedErrors[$name];
    }
    else
    {
      $this->namedErrors[$name] = $error;
      $this->errors[$name] = $error;
    }

    $this->updateCode();
    $this->updateMessage();

    return $this;
  }

  /**
   * Adds a collection of errors.
   *
   * @param sfValidatorErrorSchema $errors An sfValidatorErrorSchema instance
   *
   * @return $this The current error schema instance
   */
  public function addErrors(sfValidatorErrorSchema $errors)
  {
    foreach ($errors->getGlobalErrors() as $error)
    {
      $this->addError($error);
    }

    foreach ($errors->getNamedErrors() as $name => $error)
    {
      $this->addError($error, $name);
    }

    return $this;
  }

  /**
   * Gets an array of all errors
   *
   * @return sfValidatorError[] An array of sfValidatorError instances
   */
  public function getErrors(): array
  {
    return $this->errors;
  }

  /**
   * Gets an array of all named errors
   *
   * @return sfValidatorError[] An array of sfValidatorError instances
   */
  public function getNamedErrors(): array
  {
    return $this->namedErrors;
  }

  /**
   * Gets an array of all global errors
   *
   * @return sfValidatorError[] An array of sfValidatorError instances
   */
  public function getGlobalErrors(): array
  {
    return $this->globalErrors;
  }

  /**
   * @see sfValidatorError
   */
  public function getValue()
  {
    return null;
  }

  /**
   * @param bool $raw
   *
   * @see sfValidatorError
   */
  public function getArguments(bool $raw = false): array
  {
    return [];
  }

  /**
   * @see sfValidatorError
   */
  public function getMessageFormat(): string
  {
    return '';
  }

  /**
   * Returns the number of errors (implements the Countable interface).
   *
   * @return int The number of array
   */
  public function count(): int
  {
    return count($this->errors);
  }

  /**
   * @return Traversable<sfValidatorError>
   */
  public function getIterator(): Traversable
  {
    yield from $this->errors;
  }

  /**
   * Returns true if the error exists (implements the ArrayAccess interface).
   *
   * @param  string $name  The name of the error
   *
   * @return bool true if the error exists, false otherwise
   */
  public function offsetExists($name)
  {
    return isset($this->errors[$name]);
  }

  /**
   * Returns the error associated with the name (implements the ArrayAccess interface).
   *
   * @param  string $name  The offset of the value to get
   *
   * @return sfValidatorError A sfValidatorError instance
   */
  public function offsetGet($name)
  {
    return $this->errors[$name] ?? null;
  }

  /**
   * Throws an exception saying that values cannot be set (implements the ArrayAccess interface).
   *
   * @param string $offset  (ignored)
   * @param string $value   (ignored)
   *
   * @throws LogicException
   */
  public function offsetSet($offset, $value)
  {
    throw new LogicException('Unable update an error.');
  }

  /**
   * Impossible to call because this is an exception!
   *
   * @param string $offset  (ignored)
   */
  public function offsetUnset($offset)
  {
  }

  /**
   * Updates the exception error code according to the current errors.
   */
  protected function updateCode(): void
  {
    $this->code = implode(' ', array_merge(
      array_map(fn (sfValidatorError $e) => $e->getCode(), $this->globalErrors),
      array_map(fn ($name, sfValidatorError $e) => "{$name} [{$e->getCode()}]", array_keys($this->namedErrors), array_values($this->namedErrors)),
    ));
  }

  /**
   * Updates the exception error message according to the current errors.
   */
  protected function updateMessage(): void
  {
    $this->message = implode(' ', array_merge(
      array_map(fn (sfValidatorError $e) => $e->getMessage(), $this->globalErrors),
      array_map(fn (string $name, sfValidatorError $e) => "{$name} [{$e->getMessage()}]", array_keys($this->namedErrors), array_values($this->namedErrors)),
    ));
  }

  /**
   * Serializes the current instance.
   *
   * @return string The instance as a serialized string
   */
  public function __serialize(): array
  {
    return array_merge(parent::__serialize(), [
      'errors'       => $this->errors,
      'globalErrors' => $this->globalErrors,
      'namedErrors'  => $this->namedErrors,
    ]);
  }

  /**
   * Unserializes a sfValidatorError instance.
   *
   * @param array $serialized  Serialized sfValidatorError instance data
   *
   */
  public function __unserialize(array $serialized)
  {
    parent::__unserialize($serialized);

    $this->errors = $serialized['errors'];
    $this->globalErrors = $serialized['globalErrors'];
    $this->namedErrors = $serialized['namedErrors'];
  }
}

<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfValidatorError represents a validation error.
 *
 * @package    symfony
 * @subpackage validator
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfValidatorError extends Exception
{
  protected sfValidatorBase $validator;
  protected array $arguments = [];

  /**
   * Constructor.
   *
   * @param sfValidatorBase $validator  An sfValidatorBase instance
   * @param string          $code       The error code
   * @param array           $arguments  An array of named arguments needed to render the error message
   */
  public function __construct(sfValidatorBase $validator, string $code, array $arguments = [])
  {
    $this->validator = $validator;
    $this->arguments = $arguments;

    parent::__construct();

    // override default exception message and code
    $this->code = $code;

    if (!$messageFormat = $this->getMessageFormat())
    {
      $messageFormat = $code;
    }
    $this->message = strtr($messageFormat, $this->getArguments());
  }

  /**
   * Returns the string representation of the error.
   *
   * @return string The error message
   */
  public function __toString()
  {
    return $this->getMessage();
  }

  /**
   * Returns the input value that triggered this error.
   *
   * @return mixed The input value
   */
  public function getValue()
  {
    return $this->arguments['value'] ?? null;
  }

  /**
   * Returns the validator that triggered this error.
   *
   * @return sfValidatorBase A sfValidatorBase instance
   */
  public function getValidator(): sfValidatorBase
  {
    return $this->validator;
  }

  /**
   * Returns the arguments needed to format the message.
   *
   * @param bool $raw  false to use it as arguments for the message format, true otherwise (default to false)
   *
   * @see getMessageFormat()
   */
  public function getArguments(bool $raw = false): array
  {
    if ($raw)
    {
      return $this->arguments;
    }

    $arguments = array();
    foreach ($this->arguments as $key => $value)
    {
      if (is_array($value))
      {
        continue;
      }

      $arguments["%$key%"] = htmlspecialchars($value ?? '', ENT_QUOTES, sfValidatorBase::getCharset());
    }

    return $arguments;
  }

  /**
   * Returns the message format for this error.
   *
   * This is the string you need to use if you need to internationalize
   * error messages:
   *
   * $i18n->__($error->getMessageFormat(), $error->getArguments());
   *
   * If no message format has been set in the validator, the exception standard
   * message is returned.
   *
   * @return string The message format
   */
  public function getMessageFormat(): string
  {
    $messageFormat = $this->validator->getMessage($this->code);
    if (!$messageFormat)
    {
      $messageFormat = $this->getMessage();
    }

    return $messageFormat;
  }

  /**
   * Serializes the current instance.
   *
   * We must implement the Serializable interface to overcome a problem with PDO
   * used as a session handler.
   *
   * The default serialization process serializes the exception trace, and because
   * the trace can contain a PDO instance which is not serializable, serializing won't
   * work when using PDO.
   *
   * @return array The instance data prepared for serialization
   */
  public function __serialize()
  {
    return [
      'validator' => $this->validator,
      'arguments' => $this->arguments,
      'code'      => $this->code,
      'message'   => $this->message,
    ];
  }

  /**
   * Unserializes a sfValidatorError instance.
   *
   * @param array $serialized  A serialized instance data
   *
   */
  public function __unserialize(array $serialized)
  {
    $this->validator = $serialized['validator'];
    $this->arguments = $serialized['arguments'];
    $this->code = $serialized['code'];
    $this->message = $serialized['message'];
  }
}

<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfTesterForm implements tests for forms submitted by the user.
 *
 * @package    symfony
 * @subpackage test
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfTesterForm extends sfTester
{
  /** @var sfForm|null */
  protected $form = null;

  /**
   * @param  sfTestFunctional  $browser  A browser
   * @param  lime_test         $tester   A tester object
   */
  public function __construct(sfTestFunctional $browser, lime_test $tester)
  {
    parent::__construct($browser, $tester);

    $this->browser->addListener('template.filter_parameters', function (sfEvent $event, array $parameters): array {
      return $this->filterTemplateParameters($parameters);
    });
  }

  /**
   * Initiliazes the tester.
   */
  public function initialize()
  {
    if (null === $this->form)
    {
      $action = $this->browser->getContext()->getActionStack()->getLastEntry()->getActionInstance();

      foreach ($action->getVarHolder()->getAll() as $name => $value)
      {
        if ($value instanceof sfForm && $value->isBound())
        {
          $this->form = $value;
          break;
        }
      }
    }
  }

  /**
   * Returns the current form.
   *
   * @return sfForm The current sfForm form instance
   */
  public function getForm(): ?sfForm
  {
    return $this->form;
  }

  /**
   * Tests if the submitted form has some error.
   *
   * @param  bool|int $value Whether to check if the form has error or not, or the number of errors
   *
   * @return $this
   */
  public function hasErrors($value = true): self
  {
    if (null === $this->form)
    {
      throw new LogicException('no form has been submitted.');
    }

    if (is_int($value))
    {
      $this->tester->is(count($this->form->getErrorSchema()), $value, sprintf('the submitted form has "%s" errors.', $value));
    }
    else
    {
      $this->tester->is($this->form->hasErrors(), $value, sprintf('the submitted form %s.', ($value) ? 'has some errors' : 'is valid'));
    }

    if ((false === $value || is_int($value) && $value != count($this->form->getErrorSchema()))
      && $this->form->hasErrors())
    {
      $this->tester->diag(sprintf('%s Errors:', get_class($this->form)));
      foreach ($this->form->getErrorSchema()->getErrors() as $key => $error)
      {
        $this->tester->diag(sprintf('  - %s: %s', $key, $error));
      }
    }

    return $this;
  }

  /**
   * Tests if the submitted form has a specific error.
   *
   * @param mixed $value The error message or the number of errors for the field (optional)
   *
   * @return $this
   */
  public function hasGlobalError($value = true): self
  {
    return $this->isError(null, $value);
  }

  /**
   * Tests if the submitted form has a specific error.
   *
   * @param string $field The field name to check for an error (null for global errors)
   * @param mixed  $value The error message or the number of errors for the field (optional)
   *
   * @return $this
   */
  public function isError(string $field, $value = true): self
  {
    if (null === $this->form)
    {
      throw new LogicException('no form has been submitted.');
    }

    if (null === $field)
    {
      $error = new sfValidatorErrorSchema(new sfValidatorPass());
      foreach ($this->form->getGlobalErrors() as $globalError)
      {
        $error->addError($globalError);
      }
    }
    else
    {
      $error = $this->getFormField($field)->getError();
    }

    if (false === $value)
    {
      $this->tester->ok(!$error || 0 == count($error), sprintf('the submitted form has no "%s" error.', $field));
    }
    else if (true === $value)
    {
      $this->tester->ok($error && count($error) > 0, sprintf('the submitted form has a "%s" error.', $field));
    }
    else if (is_int($value))
    {
      $this->tester->ok($error && count($error) == $value, sprintf('the submitted form has %s "%s" error(s).', $value, $field));
    }
    else if (preg_match('/^(!)?([^a-zA-Z0-9\\\\]).+?\\2[ims]?$/', $value, $match))
    {
      if (!$error)
      {
        $this->tester->fail(sprintf('the submitted form has a "%s" error.', $field));
      }
      else
      {
        if ($match[1] == '!')
        {
          $this->tester->unlike($error->getCode(), substr($value, 1), sprintf('the submitted form has a "%s" error that does not match "%s".', $field, $value));
        }
        else
        {
          $this->tester->like($error->getCode(), $value, sprintf('the submitted form has a "%s" error that matches "%s".', $field, $value));
        }
      }
    }
    else
    {
      if (!$error)
      {
        $this->tester->fail(sprintf('the submitted form has a "%s" error (%s).', $field, $value));
      }
      else
      {
        $this->tester->is($error->getCode(), $value, sprintf('the submitted form has a "%s" error (%s).', $field, $value));
      }
    }

    return $this;
  }

  /**
   * Outputs some debug information about the current submitted form.
   */
  public function debug()
  {
    if (null === $this->form)
    {
      throw new LogicException('no form has been submitted.');
    }

    $this->tester->error('Form debug');

    print sprintf("Submitted values: %s\n", str_replace("\n", '', var_export($this->form->getTaintedValues(), true)));
    print sprintf("Errors: %s\n", $this->form->getErrorSchema());

    exit(1);
  }

  /**
   * Listens to the template.filter_parameters event to get the submitted form object.
   *
   * @param  array  $parameters  An array of parameters passed to the template
   *
   * @return array The array of parameters passed to the template
   */
  private function filterTemplateParameters(array $parameters): array
  {
    if (!isset($parameters['sf_type']))
    {
      return $parameters;
    }

    if ('action' == $parameters['sf_type'])
    {
      foreach ($parameters as $key => $value)
      {
        if ($value instanceof sfForm && $value->isBound())
        {
          $this->form = $value;
          break;
        }
      }
    }

    return $parameters;
  }

  /**
   * @param string $path
   * @return sfFormField
   */

  public function getFormField(string $path): sfFormField
  {
    if (false !== $pos = strpos($path, '['))
    {
      $field = $this->form[substr($path, 0, $pos)];
    }
    else
    {
      return $this->form[$path];
    }

    if (preg_match_all('/\[(?P<part>[^]]+)\]/', $path, $matches))
    {
      foreach($matches['part'] as $part)
      {
        $field = $field[$part];
      }
    }

    return $field;
  }
}

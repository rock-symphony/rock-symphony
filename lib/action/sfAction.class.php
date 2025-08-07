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
 * sfAction executes all the logic for the current request.
 *
 * @package    symfony
 * @subpackage action
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <sean@code-box.org>
 * @version    SVN: $Id$
 *
 * @method sfWebController getController()
 * @method sfWebResponse getResponse()
 */
abstract class sfAction extends sfComponent
{
  /** @var array */
  protected array $security = [];

  /**
   * @param sfContext $context     The current application context.
   * @param string    $moduleName  The module name.
   * @param string    $actionName  The action name.
   */
  public function __construct(sfContext $context, string $moduleName, string $actionName)
  {
    parent::__construct($context, $moduleName, $actionName);

    // include security configuration
    if ($file = $context->getConfigCache()->checkConfig("modules/{$this->getModuleName()}/config/security.yml", true)) {
      require($file);
    }
  }

  /**
   * Executes an application defined process prior to execution of this sfAction object.
   *
   * By default, this method is empty.
   */
  public function preExecute(): void
  {
    // Point of extension for subclasses
  }

  /**
   * Execute an application defined process immediately after execution of this sfAction object.
   *
   * By default, this method is empty.
   */
  public function postExecute(): void
  {
    // Point of extension for subclasses
  }

  /**
   * Forwards current action to the default 404 error action.
   *
   * @param string|null $message  Message of the generated exception
   * @return never-returns
   *
   * @throws sfError404Exception
   *
   */
  public function forward404(string $message = null): void
  {
    throw new sfError404Exception($this->get404Message($message));
  }

  /**
   * Forwards current action to the default 404 error action unless the specified condition is true.
   *
   * @param bool        $condition  A condition that evaluates to true or false
   * @param string|null $message    Message of the generated exception
   *
   * @return void|never-returns
   *
   * @throws sfError404Exception
   */
  public function forward404Unless(bool $condition, string $message = null): void
  {
    if ( ! $condition) {
      throw new sfError404Exception($this->get404Message($message));
    }
  }

  /**
   * Forwards current action to the default 404 error action if the specified condition is true.
   *
   * @param bool        $condition  A condition that evaluates to true or false
   * @param string|null $message    Message of the generated exception
   *
   * @return void|never-returns
   *
   * @throws sfError404Exception
   */
  public function forward404If(bool $condition, string $message = null): void
  {
    if ($condition) {
      throw new sfError404Exception($this->get404Message($message));
    }
  }

  /**
   * Redirects current action to the default 404 error action (with browser redirection).
   *
   * This method stops the current code flow.
   *
   * @return never-returns
   */
  public function redirect404(): void
  {
    $this->redirect('/' . sfConfig::get('sf_error_404_module') . '/' . sfConfig::get('sf_error_404_action'));
  }

  /**
   * Forwards current action to a new one (without browser redirection).
   *
   * This method stops the action. So, no code is executed after a call to this method.
   *
   * @param string $module  A module name
   * @param string $action  An action name
   *
   * @return never-returns
   *
   * @throws sfStopException
   */
  public function forward(string $module, string $action): void
  {
    if (sfConfig::get('sf_logging_enabled')) {
      $this->dispatcher->notify(
        new sfEvent($this, 'application.log', [sprintf('Forward to action "%s/%s"', $module, $action)]),
      );
    }

    $this->getController()->forward($module, $action);

    throw new sfStopException();
  }

  /**
   * If the condition is true, forwards current action to a new one (without browser redirection).
   *
   * This method stops the action. So, no code is executed after a call to this method.
   *
   * @param bool   $condition  A condition that evaluates to true or false
   * @param string $module     A module name
   * @param string $action     An action name
   *
   * @return void|never-returns
   *
   * @throws sfStopException
   */
  public function forwardIf(bool $condition, string $module, string $action): void
  {
    if ($condition) {
      $this->forward($module, $action);
    }
  }

  /**
   * Unless the condition is true, forwards current action to a new one (without browser redirection).
   *
   * This method stops the action. So, no code is executed after a call to this method.
   *
   * @param bool   $condition  A condition that evaluates to true or false
   * @param string $module     A module name
   * @param string $action     An action name
   *
   * @return void|never-returns
   *
   * @throws sfStopException
   */
  public function forwardUnless(bool $condition, string $module, string $action): void
  {
    if ( ! $condition) {
      $this->forward($module, $action);
    }
  }

  /**
   * Redirects current request to a new URL.
   *
   * 2 URL formats are accepted :
   *  - a full URL: http://www.google.com/
   *  - an internal URL (url_for() format): module/action
   *
   * This method stops the action. So, no code is executed after a call to this method.
   *
   * @param string $url         Url
   * @param int    $statusCode  Status code (default to 302)
   *
   * @throws sfStopException
   *
   * @return never-returns
   */
  public function redirect(string $url, int $statusCode = 302): void
  {
    $this->getController()->redirect($url, 0, $statusCode);

    throw new sfStopException();
  }

  /**
   * Redirects current request to a new URL, only if specified condition is true.
   *
   * This method stops the action. So, no code is executed after a call to this method.
   *
   * @param bool   $condition   A condition that evaluates to true or false
   * @param string $url         Url
   * @param int    $statusCode  Status code (default to 302)
   *
   * @throws sfStopException
   *
   * @see redirect
   *
   * @return void|never-returns
   */
  public function redirectIf(bool $condition, string $url, int $statusCode = 302): void
  {
    if ($condition) {
      $this->redirect($url, $statusCode);
    }
  }

  /**
   * Redirects current request to a new URL, unless specified condition is true.
   *
   * This method stops the action. So, no code is executed after a call to this method.
   *
   * @param bool   $condition   A condition that evaluates to true or false
   * @param string $url         Url
   * @param int    $statusCode  Status code (default to 302)
   *
   * @throws sfStopException
   *
   * @see redirect
   *
   * @return void|never-returns
   */
  public function redirectUnless(bool $condition, string $url, int $statusCode = 302): void
  {
    if ( ! $condition) {
      $this->redirect($url, $statusCode);
    }
  }

  /**
   * Appends the given text to the response content and bypasses the built-in view system.
   *
   * This method must be called as with a return:
   *
   * <code>return $this->renderText('some text')</code>
   *
   * @param string $text  Text to append to the response
   *
   * @return string sfView::NONE
   */
  public function renderText(string $text): string
  {
    $this->getResponse()->setContent($this->getResponse()->getContent() . $text);

    return sfView::NONE;
  }

  /**
   * Convert the given data into a JSON response.
   *
   * <code>return $this->renderJson(array('username' => 'john'))</code>
   *
   * @param mixed $data  Data to encode as JSON
   *
   * @return string sfView::NONE
   */
  public function renderJson(mixed $data): string
  {
    $this->getResponse()->setContentType('application/json');
    $this->getResponse()->setContent(json_encode($data));

    return sfView::NONE;
  }

  /**
   * Returns the partial rendered content.
   *
   * If the vars parameter is omitted, the action's internal variables
   * will be passed, just as it would to a normal template.
   *
   * If the vars parameter is set then only those values are
   * available in the partial.
   *
   * @param string                   $templateName  Partial name
   * @param array<string,mixed>|null $vars          Variables
   *
   * @return string The partial content
   */
  public function getPartial(string $templateName, array | null $vars = null): string
  {
    $this->getContext()->getConfiguration()->loadHelpers(['Partial']);

    $vars = null !== $vars ? $vars : $this->varHolder->getAll();

    return get_partial($templateName, $vars);
  }

  /**
   * Appends the result of the given partial execution to the response content.
   *
   * This method must be called as with a return:
   *
   * <code>return $this->renderPartial('foo/bar')</code>
   *
   * @param string              $templateName  Partial name
   * @param array<string,mixed> $vars          Variables
   *
   * @return string sfView::NONE
   *
   * @see    getPartial
   */
  public function renderPartial(string $templateName, array $vars = null): string
  {
    return $this->renderText($this->getPartial($templateName, $vars));
  }

  /**
   * Returns the component rendered content.
   *
   * If the vars parameter is omitted, the action's internal variables
   * will be passed, just as it would to a normal template.
   *
   * If the vars parameter is set then only those values are
   * available in the component.
   *
   * @param string     $moduleName     module name
   * @param string     $componentName  component name
   * @param array|null $vars           vars
   *
   * @return string  The component rendered content
   */
  public function getComponent(string $moduleName, string $componentName, array | null $vars = null): string
  {
    $this->getContext()->getConfiguration()->loadHelpers(['Partial']);

    $vars = null !== $vars ? $vars : $this->varHolder->getAll();

    return get_component($moduleName, $componentName, $vars);
  }

  /**
   * Appends the result of the given component execution to the response content.
   *
   * This method must be called as with a return:
   *
   * <code>return $this->renderComponent('foo', 'bar')</code>
   *
   * @param string     $moduleName     module name
   * @param string     $componentName  component name
   * @param array|null $vars           vars
   *
   * @return string  sfView::NONE
   *
   * @see    getComponent
   */
  public function renderComponent(string $moduleName, string $componentName, array | null $vars = null): string
  {
    return $this->renderText($this->getComponent($moduleName, $componentName, $vars));
  }

  /**
   * Returns the security configuration for this module.
   *
   * @return array Current security configuration as an array
   */
  public function getSecurityConfiguration(): array
  {
    return $this->security;
  }

  /**
   * Overrides the current security configuration for this module.
   *
   * @param array $security  The new security configuration
   */
  public function setSecurityConfiguration(array $security): void
  {
    $this->security = $security;
  }

  /**
   * Returns a value from security.yml.
   *
   * @param string $name     The name of the value to pull from security.yml
   * @param mixed  $default  The default value to return if none is found in security.yml
   *
   * @return mixed
   */
  public function getSecurityValue(string $name, mixed $default = null): mixed
  {
    $actionName = strtolower($this->getActionName());

    if (isset($this->security[$actionName][$name])) {
      return $this->security[$actionName][$name];
    }

    if (isset($this->security['all'][$name])) {
      return $this->security['all'][$name];
    }

    return $default;
  }

  /**
   * Indicates that this action requires security.
   *
   * @return bool true, if this action requires security, otherwise false.
   */
  public function isSecure(): bool
  {
    return $this->getSecurityValue('is_secure', false);
  }

  /**
   * Gets credentials the user must have to access this action.
   *
   * @return mixed An array or a string describing the credentials the user must have to access this action
   */
  public function getCredential()
  {
    return $this->getSecurityValue('credentials');
  }

  /**
   * Sets an alternate template for this sfAction.
   *
   * See 'Naming Conventions' in the 'Symfony View' documentation.
   *
   * @param string      $name    Template name
   * @param string|null $module  The module (current if null)
   */
  public function setTemplate(string $name, string $module = null): void
  {
    if (sfConfig::get('sf_logging_enabled')) {
      $this->dispatcher->notify(new sfEvent($this, 'application.log', [sprintf('Change template to "%s/%s"', null === $module ? 'CURRENT' : $module, $name)]));
    }

    if (null !== $module) {
      $dir  = $this->context->getConfiguration()->getTemplateDir($module, $name . sfView::SUCCESS . '.php');
      $name = $dir . '/' . $name;
    }

    sfConfig::set('symfony.view.' . $this->getModuleName() . '_' . $this->getActionName() . '_template', $name);
  }

  /**
   * Gets the name of the alternate template for this sfAction.
   *
   * WARNING: It only returns the template you set with the setTemplate() method,
   *          and does not return the template that you configured in your view.yml.
   *
   * See 'Naming Conventions' in the 'Symfony View' documentation.
   *
   * @return string|null Template name. Returns null if no template has been set within the action
   */
  public function getTemplate(): ?string
  {
    return sfConfig::get('symfony.view.' . $this->getModuleName() . '_' . $this->getActionName() . '_template');
  }

  /**
   * Sets an alternate layout for this sfAction.
   *
   * To de-activate the layout, set the layout name to false.
   *
   * To revert the layout to the one configured in the view.yml, set the template name to null.
   *
   * @param string|false $name  Layout name or false to de-activate the layout
   */
  public function setLayout(string | false $name): void
  {
    if (sfConfig::get('sf_logging_enabled')) {
      $this->dispatcher->notify(new sfEvent($this, 'application.log', [sprintf('Change layout to "%s"', $name)]));
    }

    sfConfig::set('symfony.view.' . $this->getModuleName() . '_' . $this->getActionName() . '_layout', $name);
  }

  /**
   * Gets the name of the alternate layout for this sfAction.
   *
   * WARNING: It only returns the layout you set with the setLayout() method,
   *          and does not return the layout that you configured in your view.yml.
   *
   * @return string|false|null Layout name. Returns null if no layout has been set within the action
   */
  public function getLayout(): string | false | null
  {
    return sfConfig::get('symfony.view.' . $this->getModuleName() . '_' . $this->getActionName() . '_layout');
  }

  /**
   * Changes the default view class used for rendering the template associated with the current action.
   *
   * @param class-string $class  View class name
   */
  public function setViewClass(string $class): void
  {
    sfConfig::set('mod_' . strtolower($this->getModuleName()) . '_view_class', $class);
  }

  /**
   * Returns the current route for this request
   *
   * @return sfRoute The route for the request
   */
  public function getRoute(): sfRoute
  {
    return $this->getRequest()->getAttribute('sf_route');
  }

  /**
   * Returns a formatted message for a 404 error.
   *
   * @param string|null $message  An error message (null by default)
   *
   * @return string The error message or a default one if null
   */
  protected function get404Message(string $message = null): string
  {
    return null === $message ? sprintf('This request has been forwarded to a 404 error page by the action "%s/%s".', $this->getModuleName(), $this->getActionName()) : $message;
  }
}

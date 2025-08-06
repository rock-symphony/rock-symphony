<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfComponent.
 *
 * @package    symfony
 * @subpackage action
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
abstract class sfComponent
{
  /** @var string */
  protected string $moduleName;

  /** @var string */
  protected string $actionName;

  /** @var sfContext */
  protected sfContext $context;

  /** @var sfEventDispatcher */
  protected sfEventDispatcher $dispatcher;

  /** @var sfRequest */
  protected sfRequest $request;

  /** @var sfResponse */
  protected sfResponse $response;

  /** @var sfParameterHolder */
  protected sfParameterHolder $varHolder;

  /** @var sfParameterHolder */
  protected sfParameterHolder $requestParameterHolder;

  /**
   * @param sfContext $context
   * @param string    $moduleName
   * @param string    $actionName
   */
  public function __construct(sfContext $context, string $moduleName, string $actionName)
  {
    $this->moduleName             = $moduleName;
    $this->actionName             = $actionName;
    $this->context                = $context;
    $this->dispatcher             = $context->getEventDispatcher();
    $this->varHolder              = new sfParameterHolder();
    $this->request                = $context->getRequest();
    $this->response               = $context->getResponse();
    $this->requestParameterHolder = $this->request->getParameterHolder();
  }

  /**
   * Execute any application/business logic for this component.
   *
   * In a typical database-driven application, execute() handles application
   * logic itself and then proceeds to create a model instance. Once the model
   * instance is initialized it handles all business logic for the action.
   *
   * A model should represent an entity in your application. This could be a
   * user account, a shopping cart, or even a something as simple as a
   * single product.
   *
   * @param sfRequest $request  The current sfRequest object
   *
   * @return mixed     A string containing the view name associated with this action
   */
  abstract function execute(sfRequest $request): mixed;

  /**
   * Gets the module name associated with this component.
   *
   * @return string A module name
   */
  public function getModuleName(): string
  {
    return $this->moduleName;
  }

  /**
   * Gets the action name associated with this component.
   *
   * @return string An action name
   */
  public function getActionName(): string
  {
    return $this->actionName;
  }

  /**
   * Retrieves the current application context.
   *
   * @return sfContext The current sfContext instance
   */
  public final function getContext(): sfContext
  {
    return $this->context;
  }

  /**
   * Retrieves the current service container instance.
   *
   * @return sfServiceContainerInterface The current sfServiceContainer instance
   */
  public final function getServiceContainer(): sfServiceContainerInterface
  {
    return $this->context->getServiceContainer();
  }

  /**
   * Retrieves a service from the service container.
   *
   * @param string $id  The service identifier
   *
   * @return object The service instance
   */
  public function getService(string $id): mixed
  {
    return $this->getServiceContainer()->getService($id);
  }

  /**
   * Retrieves the current logger instance.
   *
   * @return sfLogger The current sfLogger instance
   */
  public final function getLogger(): sfLogger
  {
    return $this->context->getLogger();
  }

  /**
   * Logs a message using the sfLogger object.
   *
   * @param mixed  $message   String or object containing the message to log
   * @param string $priority  The priority of the message
   *                          (available priorities: emerg, alert, crit, err,
   *                          warning, notice, info, debug)
   *
   * @see sfLogger
   */
  public function logMessage(string $message, string $priority = 'info'): void
  {
    if (sfConfig::get('sf_logging_enabled')) {
      $this->dispatcher->notify(new sfEvent($this, 'application.log', [$message, 'priority' => constant('sfLogger::' . strtoupper($priority))]));
    }
  }

  /**
   * Gets the translation for the given string
   *
   * @param string $string     The string to translate
   * @param array  $args       An array of arguments for the translation
   * @param string $catalogue  The catalogue name
   *
   * @return string The translated string
   */
  public function __(string $string, array $args = [], string $catalogue = 'messages'): string
  {
    return $this->context->getI18N()->__($string, $args, $catalogue);
  }

  /**
   * Returns the value of a request parameter.
   *
   * This is a proxy method equivalent to:
   *
   * <code>$this->getRequest()->getParameterHolder()->get($name)</code>
   *
   * @param string $name     The parameter name
   * @param mixed  $default  The default value if parameter does not exist
   *
   * @return mixed The request parameter value
   */
  public function getRequestParameter(string $name, mixed $default = null): mixed
  {
    return $this->requestParameterHolder->get($name, $default);
  }

  /**
   * Returns true if a request parameter exists.
   *
   * This is a proxy method equivalent to:
   *
   * <code>$this->getRequest()->getParameterHolder()->has($name)</code>
   *
   * @param string $name  The parameter name
   * @return boolean true if the request parameter exists, false otherwise
   */
  public function hasRequestParameter(string $name): bool
  {
    return $this->requestParameterHolder->has($name);
  }

  /**
   * Retrieves the current sfRequest object.
   *
   * This is a proxy method equivalent to:
   *
   * <code>$this->getContext()->getRequest()</code>
   *
   * @return sfRequest The current sfRequest implementation instance
   */
  public function getRequest(): sfRequest
  {
    return $this->request;
  }

  /**
   * Retrieves the current sfResponse object.
   *
   * This is a proxy method equivalent to:
   *
   * <code>$this->getContext()->getResponse()</code>
   *
   * @return sfResponse The current sfResponse implementation instance
   */
  public function getResponse(): sfResponse
  {
    return $this->response;
  }

  /**
   * Retrieves the current sfController object.
   *
   * This is a proxy method equivalent to:
   *
   * <code>$this->getContext()->getController()</code>
   *
   * @return sfController The current sfController implementation instance
   */
  public function getController(): sfController
  {
    return $this->context->getController();
  }

  /**
   * Generates a URL for the given route and arguments.
   *
   * This is a proxy method equivalent to:
   *
   * <code>$this->getContext()->getRouting()->generate(...)</code>
   *
   * @param string  $route     The route name
   * @param array   $params    An array of parameters for the route
   * @param Boolean $absolute  Whether to generate an absolute URL or not
   *
   * @return string  The URL
   */
  public function generateUrl(string $route, array $params = [], bool $absolute = false): string
  {
    return $this->context->getRouting()->generate($route, $params, $absolute);
  }

  /**
   * Retrieves the current sfUser object.
   *
   * This is a proxy method equivalent to:
   *
   * <code>$this->getContext()->getUser()</code>
   *
   * @return sfUser The current sfUser implementation instance
   */
  public function getUser(): sfUser
  {
    return $this->context->getUser();
  }

  /**
   * Gets the current mailer instance.
   *
   * @return sfMailer A sfMailer instance
   */
  public function getMailer(): sfMailer
  {
    return $this->getContext()->getMailer();
  }

  /**
   * Sets a variable for the template.
   *
   * If you add a safe value, the variable won't be output escaped
   * by symfony, so this is your responsability to ensure that the
   * value is escaped properly.
   *
   * @param string  $name   The variable name
   * @param mixed   $value  The variable value
   * @param Boolean $safe   true if the value is safe for output (false by default)
   */
  public function setVar(string $name, mixed $value, bool $safe = false): void
  {
    $this->varHolder->set($name, $safe ? new sfOutputEscaperSafe($value) : $value);
  }

  /**
   * Gets a variable set for the template.
   *
   * @param string $name  The variable name
   *
   * @return mixed  The variable value
   */
  public function getVar(string $name): mixed
  {
    return $this->varHolder->get($name);
  }

  /**
   * Gets the sfParameterHolder object that stores the template variables.
   *
   * @return sfParameterHolder The variable holder.
   */
  public function getVarHolder(): sfParameterHolder
  {
    return $this->varHolder;
  }

  /**
   * Sets a variable for the template.
   *
   * This is a shortcut for:
   *
   * <code>$this->setVar('name', 'value')</code>
   *
   * @param string $key    The variable name
   * @param string $value  The variable value
   *
   * @return boolean always true
   *
   * @see setVar()
   */
  public function __set($key, $value)
  {
    return $this->varHolder->setByRef($key, $value);
  }

  /**
   * Gets a variable for the template.
   *
   * This is a shortcut for:
   *
   * <code>$this->getVar('name')</code>
   *
   * @param string $key  The variable name
   *
   * @return mixed The variable value
   *
   * @see getVar()
   */
  public function & __get($key)
  {
    return $this->varHolder->get($key);
  }

  /**
   * Returns true if a variable for the template is set.
   *
   * This is a shortcut for:
   *
   * <code>$this->getVarHolder()->has('name')</code>
   *
   * @param string $name  The variable name
   *
   * @return boolean true if the variable is set
   */
  public function __isset($name)
  {
    return $this->varHolder->has($name);
  }

  /**
   * Removes a variable for the template.
   *
   * This is just really a shortcut for:
   *
   * <code>$this->getVarHolder()->remove('name')</code>
   *
   * @param string $name  The variable Name
   */
  public function __unset($name)
  {
    $this->varHolder->remove($name);
  }
}

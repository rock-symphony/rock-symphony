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
 * sfDatabaseManager allows you to setup your database connectivity before the
 * request is handled. This eliminates the need for a filter to manage database
 * connections.
 *
 * @package    symfony
 * @subpackage database
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <sean@code-box.org>
 * @version    SVN: $Id$
 */
class sfDatabaseManager
{
  /** @var sfDatabase[] */
  protected array $databases = [];
  /** @var PDO[] */
  protected array $pdoConnections = [];
  
  protected ?sfProjectConfiguration $configuration = null;
  protected ?string $defaultDatabaseName = null;

  /**
   * Class constructor.
   *
   * @param sfProjectConfiguration $configuration
   * @param array $options
   */
  public function __construct(sfProjectConfiguration $configuration, array $options = [])
  {
    $this->configuration = $configuration;

    $this->loadConfiguration();

    $this->setDefaultDatabaseName(sfConfig::get('sf_default_database'));

    if (!isset($options['auto_shutdown']) || $options['auto_shutdown']) {
      register_shutdown_function(array($this, 'shutdown'));
    }
  }

  public function setDefaultDatabaseName($name): void
  {
    if (!in_array($name, $this->getNames())) {
      throw new RuntimeException('Default database does not exist.');
    }

    $this->defaultDatabaseName = $name;
  }

  public function getDefaultDatabaseName(): string
  {
    return $this->defaultDatabaseName;
  }

  /**
   * Loads database configuration.
   */
  public function loadConfiguration(): void
  {
    if ($this->configuration instanceof sfApplicationConfiguration) {
      $databases = include($this->configuration->getConfigCache()->checkConfig('config/databases.yml'));
    } else {
      $configHandler = new sfDatabaseConfigHandler();
      $databases = $configHandler->evaluate(array($this->configuration->getRootDir() . '/config/databases.yml'));
    }

    foreach ($databases as $name => $database) {
      $this->setDatabase($name, $database);
    }
  }

  /**
   * Sets a database connection.
   *
   * @param string $name The database name
   * @param sfDatabase $database A sfDatabase instance
   */
  public function setDatabase(string $name, sfDatabase $database): void
  {
    $this->databases[$name] = $database;
  }

  /**
   * Retrieves the database connection associated with this sfDatabase implementation.
   *
   * @param string $name A database name
   *
   * @return sfDatabase A Database instance
   *
   * @throws <b>sfDatabaseException</b> If the requested database name does not exist
   */
  public function getDatabase(string $name = 'default'): sfDatabase
  {
    if (isset($this->databases[$name])) {
      return $this->databases[$name];
    }

    // nonexistent database name
    throw new sfDatabaseException(sprintf('Database "%s" does not exist.', $name));
  }

  public function getDatabases(): array
  {
    return $this->databases;
  }

  public function getDefaultDatabase(): sfDatabase
  {
    return $this->getDatabase($this->getDefaultDatabaseName());
  }

  public function databaseExists(string $name): bool
  {
    return in_array($name, $this->getNames());
  }

  /**
   * Returns the names of all database connections.
   *
   * @return string[] An array containing all database connection names
   */
  public function getNames(): array
  {
    return array_keys($this->databases);
  }

  public function getPdoConnection(string $datasource): PDO
  {
    if (!isset($this->pdoConnections[$datasource])) {
      $this->pdoConnections[$datasource] = Propel::initConnection(
        $this->getPdoConnectionParams($datasource),
        $datasource
      );
    }

    return $this->pdoConnections[$datasource];
  }

  public function getPdoConnectionParams(string $datasource): array
  {
    $database = $this->getDatabase($datasource);

    return [
      'adapter' => $database->getParameter('phptype'),
      'dsn' => str_replace("@DB@", $datasource, $database->getParameter('dsn')),
      'user' => $database->getParameter('username'),
      'password' => $database->getParameter('password'),
      'settings' => array(
        'queries' => $database->getParameter('queries'),
        'charset' => array(
          'value' => $database->getParameter('encoding'),
        ),
      ),
    ];
  }

  public function getAllPdoConnectionsParams(): array
  {
    $connectionParams = [];

    foreach ($this->getNames() as $datasource) {
      $connectionParams[$datasource] = $this->getPdoConnectionParams($datasource);
    }

    return $connectionParams;
  }

  public function getPdoConnectionParam(string $datasource, string $param)
  {
    return $this->getPdoConnectionParams()[$param];
  }

  public function getPhingPropertiesForConnection(string $connection): array
  {
    $database = $this->getDatabase($connection);

    return [
      'propel.database'          => $database->getParameter('phptype'),
      'propel.database.driver'   => $database->getParameter('phptype'),
      'propel.database.url'      => $database->getParameter('dsn'),
      'propel.database.user'     => $database->getParameter('username'),
      'propel.database.password' => $database->getParameter('password'),
      'propel.database.encoding' => $database->getParameter('encoding'),
    ];
  }

  public function getPlatform(string $datasource)
  {
    $database = $this->getDatabase($datasource);
    $adapter = $database->getParameter('phptype');
    $adapterClass = ucfirst($adapter) . 'Platform';

    return new $adapterClass();
  }

  public function getSchemaParser(string $datasource, GeneratorConfig $generatorConfig)
  {
    $database = $this->getDatabase($datasource);
    $adapter = $database->getParameter('phptype');

    $parserClass = ucfirst($adapter) . 'SchemaParser';
    /** @var SchemaParser $parser */
    $parser = new $parserClass();
    $parser->setConnection($this->getPdoConnection($datasource));
    $parser->setGeneratorConfig($generatorConfig);
    return $parser;
  }

  /**
   * Executes the shutdown procedure
   *
   * @return void
   *
   * @throws <b>sfDatabaseException</b> If an error occurs while shutting down this DatabaseManager
   */
  public function shutdown(): void
  {
    // loop through databases and shutdown connections
    foreach ($this->databases as $database) {
      $database->shutdown();
    }
  }
}

<?php

class sfServiceContainerConfigParserTest extends \PHPUnit\Framework\TestCase
{
  /** @var sfServiceContainerConfigParser */
  private $parser;

  protected function setUp()
  {
    $this->parser = new sfServiceContainerConfigParser();
  }

  // __construct()


  /**
   * @test
   */
  public function it_should_construct_new_instances()
  {
    $parser = new sfServiceContainerConfigParser();
    $this->assertNotEmpty($parser, '__construct() creates a new parser instances without arguments');

    $parser = new sfServiceContainerConfigParser( new sfServiceContainerBuilder());
    $this->assertNotEmpty($parser, '__construct() takes a container builder instance as its first argument');
  }

  // ->parse()


  /**
   * @test
   */
  public function it_should_throw_if_definition_structure_is_not_valid()
  {
    $this->setExpectedException('InvalidArgumentException');
    $this->parser->parse(self::load('fixtures/yaml/nonvalid1.yml'));
  }

  /**
   * @test
   */
  public function it_should_fail_if_definition_structure_is_not_an_array()
  {
    $this->setExpectedException('TypeError');
    $this->parser->parse(self::load('fixtures/yaml/nonvalid2.yml'));
  }

  // ->parse # parameters

  /**
   * @test
   */
  public function it_should_define_empty_parameters_array_for_an_empty_array_definition()
  {
    $builder = $this->parser->parse(array());
    $this->assertEquals(array(), $builder->getParameters());
  }

  /**
   * @test
   */
  public function it_should_parse_parameters_and_convert_keys_to_lowercase()
  {
    $builder = $this->parser->parse(self::load('fixtures/yaml/services2.yml'));
    $this->assertEquals(
      array(
        'foo' => 'bar',
        'values' => array(true, false, 0, 1000.3),
        'bar' => 'foo',
        'foo_bar' => new sfServiceReference('foo_bar')
      ),
      $builder->getParameters()
    );
  }

  // ->parse # services

  /**
   * @test
   */
  public function it_should_return_empty_services_array_for_an_empty_array_definition()
  {
    $builder = $this->parser->parse(self::load('fixtures/yaml/services2.yml'));
    $this->assertEquals(array(), $builder->getServiceDefinitions());
  }

  /**
   * @test
   */
  public function it_should_parse_service_definitions()
  {
    $builder = $this->parser->parse(self::load('fixtures/yaml/services3.yml'));

    $this->assertTrue($builder->hasServiceDefinition('foo'), '->parse parses service elements');
    $this->assertInstanceOf('sfServiceDefinition', $builder->getServiceDefinition('foo'), '->parse converts service element to sfServiceDefinition instances');
    $this->assertEquals('FooClass', $builder->getServiceDefinition('foo')->getClass(), '->parse parses the class attribute');
    $this->assertTrue($builder->getServiceDefinition('shared')->isShared(), '->parse parses the shared attribute');
    $this->assertFalse($builder->getServiceDefinition('non_shared')->isShared(), '->parse parses the shared attribute');
    $this->assertEquals('getInstance', $builder->getServiceDefinition('constructor')->getConstructor(), '->parse parses the constructor attribute');
    $this->assertEquals('%path%/foo.php', $builder->getServiceDefinition('file')->getFile(), '->parse parses the file tag');
    $this->assertEquals(array('foo', new sfServiceReference('foo'), array(true, false)), $builder->getServiceDefinition('arguments')->getArguments(), '->parse parses the argument tags');
    $this->assertEquals('sc_configure', $builder->getServiceDefinition('configurator1')->getConfigurator(), '->parse parses the configurator tag');
    $this->assertEquals(array(new sfServiceReference('baz'), 'configure'), $builder->getServiceDefinition('configurator2')->getConfigurator(), '->parse parses the configurator tag');
    $this->assertEquals(array('BazClass', 'configureStatic'), $builder->getServiceDefinition('configurator3')->getConfigurator(), '->parse parses the configurator tag');
    $this->assertEquals(array(array('setBar', array())), $builder->getServiceDefinition('method_call1')->getMethodCalls(), '->parse parses the method_call tag');
    $this->assertEquals(array(array('setBar', array('foo', new sfServiceReference('foo'), array(true, false)))), $builder->getServiceDefinition('method_call2')->getMethodCalls(), '->parse parses the method_call tag');
    $this->assertTrue($builder->hasAlias('alias_for_foo'), '->parse parses aliases');
    $this->assertEquals($builder->getAlias('alias_for_foo'), 'foo', '->parse parses aliases');
  }

  /**
   * @test
   */
  public function it_should_parse_config_to_service_builder()
  {
    $parser = new sfServiceContainerConfigParser($builder = new sfServiceContainerBuilder(array('bar' => 'foo')));
    $parser->parse(array('parameters' => array('foo' => 'bar')));
    $this->assertEquals(array('bar' => 'foo', 'foo' => 'bar'), $builder->getParameters(), '->parse() merges current parameters with the loaded ones');

    $parser = new sfServiceContainerConfigParser($builder = new sfServiceContainerBuilder(array('bar' => 'foo', 'foo' => 'baz')));
    $parser->parse(array('parameters' => array('foo' => 'bar')));
    $this->assertEquals(array('bar' => 'foo', 'foo' => 'baz'), $builder->getParameters(), '->parse() does not change the already defined parameters');

    $parser = new sfServiceContainerConfigParser($builder = new sfServiceContainerBuilder(array('bar' => 'foo')));
    $parser->parse(array('parameters' => array('foo' => '%bar%')));
    $this->assertEquals(array('bar' => 'foo', 'foo' => new sfServiceParameter('bar')), $builder->getParameters(), '->parse() evaluates the values of the parameters towards already defined ones');

    $parser = new sfServiceContainerConfigParser($builder = new sfServiceContainerBuilder(array('bar' => 'foo')));
    $parser->parse(array('parameters' => array('foo' => '%bar%', 'baz' => '%foo%')));
    $this->assertEquals(array('bar' => 'foo', 'foo' => new sfServiceParameter('bar'), 'baz' => new sfServiceParameter('foo')), $builder->getParameters(), '->parse() evaluates the values of the parameters towards already defined ones');

    $parser = new sfServiceContainerConfigParser($builder = new sfServiceContainerBuilder());
    $builder->setServiceDefinition('foo', new sfServiceDefinition('FooClass'));
    $builder->setServiceDefinition('bar', new sfServiceDefinition('BarClass'));
    $parser->parse(array('services' => array('baz' => array('class' => 'BazClass'), 'alias_for_foo' => '@foo')));
    $this->assertEquals(array('foo', 'bar', 'baz'), array_keys($builder->getServiceDefinitions()), '->parse() merges definitions already defined ones');
    $this->assertEquals(array('alias_for_foo' => 'foo'), $builder->getAliases(), '->parse() registers defined aliases');

    $parser = new sfServiceContainerConfigParser($builder = new sfServiceContainerBuilder());
    $builder->setAlias('foo', 'FooClass');
    $parser->parse(array('services' => array('foo' => array('class' => 'BazClass'))));
    $this->assertEquals('BazClass', $builder->getServiceDefinition('foo')->getClass(), '->parse() overrides already defined services');
  }

  /**
   * @test
   */
  public function it_should_parse_strings_with_parameter_references()
  {
    $parser = new sfServiceContainerConfigParser($builder = new sfServiceContainerBuilder());
    $parser->parse(array(
      'parameters' => array(
        'base_host' => 'example.com',
        'subdomain' => 'app',
        'host' => '%subdomain%.%base_host%'
      ),
      'services' => array(
        'foo' => array(
          'class' => 'BazClass',
          'arguments' => array('url' => 'http://%host%/'),
        ),
      ),
    ));

    $this->assertEquals(
      new sfServiceParameterStringExpression(array(
        new sfServiceParameter('subdomain'),
        '.',
        new sfServiceParameter('base_host'),
      )),
      $builder->getParameter('host')
    );

    $this->assertEquals(
      new sfServiceDefinition('BazClass', array(
        'url' => new sfServiceParameterStringExpression(array('http://', new sfServiceParameter('host'), '/'))
      )),
      $builder->getServiceDefinition('foo')
    );
  }

  private static function load($path)
  {
    return sfYaml::load(__DIR__ . '/../../unit/service/' . $path);
  }
}

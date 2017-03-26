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
    $this->assertEquals($builder->getParameters(), array());
  }

  /**
   * @test
   */
  public function it_should_parse_parameters_and_convert_keys_to_lowercase()
  {
    $builder = $this->parser->parse(self::load('fixtures/yaml/services2.yml'));
    $this->assertEquals($builder->getParameters(), array(
      'foo' => 'bar',
      'values' => array(true, false, 0, 1000.3),
      'bar' => 'foo',
      'foo_bar' => new sfServiceReference('foo_bar')
    ));
  }

  // ->parse # services

  /**
   * @test
   */
  public function it_should_return_empty_services_array_for_an_empty_array_definition()
  {
    $builder = $this->parser->parse(self::load('fixtures/yaml/services2.yml'));
    $this->assertEquals($builder->getServiceDefinitions(), array());
  }

  /**
   * @test
   */
  public function it_should_parse_service_definitions()
  {
    $builder = $this->parser->parse(self::load('fixtures/yaml/services3.yml'));

    $this->assertTrue($builder->hasServiceDefinition('foo'), '->parse parses service elements');
    $this->assertEquals(get_class($builder->getServiceDefinition('foo')), 'sfServiceDefinition', '->parse converts service element to sfServiceDefinition instances');
    $this->assertEquals($builder->getServiceDefinition('foo')->getClass(), 'FooClass', '->parse parses the class attribute');
    $this->assertTrue($builder->getServiceDefinition('shared')->isShared(), '->parse parses the shared attribute');
    $this->assertFalse($builder->getServiceDefinition('non_shared')->isShared(), '->parse parses the shared attribute');
    $this->assertEquals($builder->getServiceDefinition('constructor')->getConstructor(), 'getInstance', '->parse parses the constructor attribute');
    $this->assertEquals($builder->getServiceDefinition('file')->getFile(), '%path%/foo.php', '->parse parses the file tag');
    $this->assertEquals($builder->getServiceDefinition('arguments')->getArguments(), array('foo', new sfServiceReference('foo'), array(true, false)), '->parse parses the argument tags');
    $this->assertEquals($builder->getServiceDefinition('configurator1')->getConfigurator(), 'sc_configure', '->parse parses the configurator tag');
    $this->assertEquals($builder->getServiceDefinition('configurator2')->getConfigurator(), array(new sfServiceReference('baz'), 'configure'), '->parse parses the configurator tag');
    $this->assertEquals($builder->getServiceDefinition('configurator3')->getConfigurator(), array('BazClass', 'configureStatic'), '->parse parses the configurator tag');
    $this->assertEquals($builder->getServiceDefinition('method_call1')->getMethodCalls(), array(array('setBar', array())), '->parse parses the method_call tag');
    $this->assertEquals($builder->getServiceDefinition('method_call2')->getMethodCalls(), array(array('setBar', array('foo', new sfServiceReference('foo'), array(true, false)))), '->parse parses the method_call tag');
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
    $this->assertEquals($builder->getParameters(), array('bar' => 'foo', 'foo' => 'bar'), '->parse() merges current parameters with the loaded ones');

    $parser = new sfServiceContainerConfigParser($builder = new sfServiceContainerBuilder(array('bar' => 'foo', 'foo' => 'baz')));
    $parser->parse(array('parameters' => array('foo' => 'bar')));
    $this->assertEquals($builder->getParameters(), array('bar' => 'foo', 'foo' => 'baz'), '->parse() does not change the already defined parameters');

    $parser = new sfServiceContainerConfigParser($builder = new sfServiceContainerBuilder(array('bar' => 'foo')));
    $parser->parse(array('parameters' => array('foo' => '%bar%')));
    $this->assertEquals($builder->getParameters(), array('bar' => 'foo', 'foo' => 'foo'), '->parse() evaluates the values of the parameters towards already defined ones');

    $parser = new sfServiceContainerConfigParser($builder = new sfServiceContainerBuilder(array('bar' => 'foo')));
    $parser->parse(array('parameters' => array('foo' => '%bar%', 'baz' => '%foo%')));
    $this->assertEquals($builder->getParameters(), array('bar' => 'foo', 'foo' => 'foo', 'baz' => 'foo'), '->parse() evaluates the values of the parameters towards already defined ones');

    $parser = new sfServiceContainerConfigParser($builder = new sfServiceContainerBuilder());
    $builder->setAlias('foo', 'FooClass');
    $builder->setAlias('bar', 'BarClass');
    $parser->parse(array('services' => array('baz' => new sfServiceDefinition('BazClass'), 'alias_for_foo' => 'foo')));
    $this->assertEquals(array_keys($builder->getServiceDefinitions()), array('foo', 'bar', 'baz'), '->parse() merges definitions already defined ones');
    $this->assertEquals($builder->getAliases(), array('alias_for_foo' => 'foo'), '->parse() registers defined aliases');

    $parser = new sfServiceContainerConfigParser($builder = new sfServiceContainerBuilder());
    $builder->setAlias('foo', 'FooClass');
    $parser->parse(array('services' => array('foo' => new sfServiceDefinition('BazClass'))));
    $this->assertEquals($builder->getServiceDefinition('foo')->getClass(), 'BazClass', '->parse() overrides already defined services');
  }

  private static function load($path)
  {
    return sfYaml::load(__DIR__ . '/../../unit/service/' . $path);
  }
}

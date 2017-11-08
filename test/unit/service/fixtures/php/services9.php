/**
 * @return \sfServiceContainer
 */
return function() {
  $container = new \sfServiceContainer();

  // foo
  $container->bindResolver('foo', function(\sfServiceContainer $container) {
    require_once '/home/io/workspace/fos1/symfony1/test/unit/service/fixtures/includes/foo.php';

    $instance = $container->call(array('FooClass', 'getInstance'), array(0 => 'foo', 1 => $this->get('foo.baz'), 2 => array('%foo%' => 'foo is %foo%'), 3 => true, 4 => $this));
    $container->call(array($instance, 'setBar'), array(0 => 'bar'));
    $container->call(array($instance, 'initialize'), array());
    sc_configure($instance);
    return $instance;
  });

  // bar
  $container->bindSingletonResolver('bar', function(\sfServiceContainer $container) {
    $instance = $container->construct('FooClass', array(0 => 'foo', 1 => $this->get('foo.baz'), 2 => sfConfig::get('foo_bar')));
    $this->get('@foo.baz')->configure($instance);
    return $instance;
  });

  // foo.baz
  $container->bindSingletonResolver('foo.baz', function(\sfServiceContainer $container) {
    $instance = $container->call(array('%baz_class%', 'getInstance'), array());
    $container->call(array(0 => '%baz_class%', 1 => 'configureStatic1'), array($instance));
    return $instance;
  });

  // foo_bar
  $container->bindSingletonResolver('foo_bar', function(\sfServiceContainer $container) {
    $instance = $container->construct('FooClass', array());
    return $instance;
  });

  // alias_for_foo => foo
  $container->alias('foo', 'alias_for_foo');
};

class ProjectServiceContainer extends sfServiceContainer
{

  /**
   * @inheritdoc
   */
  public function hasService($id)
  {
     if (parent::hasService($id)) {
       return true;
     }

     return in_array($id, array(0 => 'foo', 1 => 'bar', 2 => 'foo.baz', 3 => 'foo_bar', 4 => 'alias_for_foo'));
  }

  /**
   * @inheritdoc
   */
  public function getService($id)
  {
    if (parent::hasService($id)) {
      return parent::getService($id);
    }

    if (in_array($id, array(0 => 'foo', 1 => 'bar', 2 => 'foo.baz', 3 => 'foo_bar', 4 => 'alias_for_foo'))) {
      $method = 'get' . sfServiceContainer::camelize($id) . 'Service';
      $instance = $this->$method();
      return $instance;
    }

    // make parent throw "missing service" exception
    return parent::getService($id);
  }

  protected function getFooService()
  {
    require_once '%path%/foo.php';

    $instance = call_user_func(array('FooClass', 'getInstance'), 'foo', $this->getService('foo.baz'), array('%foo%' => 'foo is %foo%'), true, $this);
    $instance->setBar('bar');
    $instance->initialize();
    sc_configure($instance);

    return $instance;
  }

  protected function getBarService()
  {
    $instance = new FooClass('foo', $this->getService('foo.baz'), sfConfig::get('foo_bar'));
    $this->getService('@foo.baz')->configure($instance);

    parent::setService('bar', $instance);
    return $instance;
  }

  protected function getFoo_BazService()
  {
    $instance = call_user_func(array('%baz_class%', 'getInstance'));
    call_user_func(array('%baz_class%', 'configureStatic1'), $instance);

    parent::setService('foo.baz', $instance);
    return $instance;
  }

  protected function getFooBarService()
  {
    $instance = new FooClass();

    parent::setService('foo_bar', $instance);
    return $instance;
  }

  protected function getAliasForFooService()
  {
    return $this->getService('foo');
  }

}

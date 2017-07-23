class ProjectServiceContainer extends sfServiceContainer
{

  public function __construct()
  {
    parent::__construct();

    $this->addParameters($this->getDefaultParameters());
  }

  protected function getDefaultParameters()
  {
    return array('FOO' => 'bar', 'bar' => 'foo is %foo bar', 'values' => array(0 => true, 1 => false, 2 => NULL, 3 => 0, 4 => 1000.3, 5 => 'true', 6 => 'false', 7 => 'null'));
  }

  /**
   * @inheritdoc
   */
  public function hasParameter($name)
  {
    if (parent::hasParameter($name)) {
      return true;
    }
    return in_array($name, array(0 => 'FOO', 1 => 'bar', 2 => 'values'));
  }

  /**
   * @inheritdoc
   */
  public function getParameter($name)
  {
    if (parent::hasParameter($name)) {
      return parent::getParameter($name);
    }

    switch ($name) {


      default:
        // make parent::getParameter() throw "missing parameter" exception
        return parent::getParameter($name);
    }
    parent::setParameter($name, $value);
    return $value;
  }
}

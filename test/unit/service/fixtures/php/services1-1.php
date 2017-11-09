/**
 * @return \sfServiceContainerInterface
 */
return function() {
  $container = new CustomContainer();

  return $container;
};

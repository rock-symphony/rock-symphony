Upgrade guide 7.0 to 8.0
========================

1. Upgrade rock-symphony dependency to `8.0`:

   ```bash
   composer require rock-symphony/rock-symphony:^8.0
   ```

2. Make sure that your project does not rely on dropped `->initialize()` methods.
   - Make sure you don't call `->initialize()` from your code
   - Make sure that if you used to extend `initialize()` method of any framework classes
     that do not have it now, you convert these methods to overriden constructors.
     
   See respective pull requests for details:
  
   - [#41](https://github.com/rock-symphony/rock-symphony/pull/41) 
   - [#42](https://github.com/rock-symphony/rock-symphony/pull/42) 
   - [#43](https://github.com/rock-symphony/rock-symphony/pull/43) 
   - [#45](https://github.com/rock-symphony/rock-symphony/pull/45)
     
3. Make sure that if you have any custom `sfLogger` implementations in your project,
   they are compatible with the new `sfLoggerInterface` spec. 
   See [#40](https://github.com/rock-symphony/rock-symphony/pull/40).

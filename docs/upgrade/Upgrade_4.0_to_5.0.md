Upgrade guide 3.0 to 4.0
========================

1. Make sure you're not relying on symfony's built-in plugin management.
   Use [composer](https://getcomposer.org/) instead.
   
   Make sure you don't use any of the deleted classes or methods or properties.
   See the complete list in the Pull Request description: 
   [#17](https://github.com/rock-symphony/rock-symphony/pull/17).
   
2. Make sure your project plugins don't rely on symfony's testing commands.
   See the complete list of dropped stuff in 
   [#17](https://github.com/rock-symphony/rock-symphony/pull/18).

3. Make sure you don't rely on symfony's response compression (`sf_compressed` setting).
   Move compression to your webserver instead.

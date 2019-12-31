Upgrade guide 6.0 to 7.0
========================

1. Make sure your project is compatible with PHP 7.1.

2. Do not require `Escaper` helper explicitly. 
   The escaping functions are now globally available.
   
3. Upgrade rock-symphony dependency to `7.0`:

   ```bash
   composer require rock-symphony/rock-symphony:^7.0
   ```
   
4. Make sure your project classes extending framework 
   classes have compatible methods specifications.

5. Test.   

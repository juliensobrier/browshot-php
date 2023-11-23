# Browshot (PHP)

Browshot (https://www.browshot.com/) is a web service to easily make screenshots of web pages in any screen size, as any device: iPhone©, iPad©, Android©, Nook©, PC, etc. Browshot has full Flash, JavaScript, CSS, & HTML5 support.

The latest API version is detailed at https://browshot.com/api/documentation. browshot follows the API documentation very closely: the function names are similar to the URLs used (screenshot/create becomes screenshot_create(), instance/list becomes instance_list(), etc.), the request arguments are exactly the same, etc.

The library version matches closely the API version it handles: Browshot 1.0.0 is the first release for the API 1.0, browshot 1.1.1 is the second release for the API 1.1, etc.

Browshot can handle most the API updates within the same major version, e.g. browshot 1.0.0 should be compatible with the API 1.1 or 1.2.



## Requirements

    PHP 5.1.6 or abaove
    PhpUnit 3.3.5 or above (to run unit tests)


## Use Browshot

    git clone https://github.com/juliensobrier/browshot-php
    include "src/Browshot.php"

To run the unit tests (and understand how to use Browshot)

    phpunit
    
    ### Install with Composer
    composer.json can be found at https://raw.githubusercontent.com/juliensobrier/browshot-php/master/composer.json
    
    run composer update: `php composer.phar update` or `php composer.phar install`
    
    include composer packages and libraries:
        
    require "vendor/autoload.php";


## Contributing to Browshot
 
* Check out the latest master to make sure the feature hasn't been implemented or the bug hasn't been fixed yet
* Check out the issue tracker to make sure someone hasn't requested it and/or contributed to it
* Fork the project
* Start a feature/bugfix branch
* Commit and push until you are happy with your contribution
* Make sure to add tests for it. This is important so I don't break it in a future version unintentionally.

## Copyright

Copyright (c) 2012-2023 Julien Sobrier

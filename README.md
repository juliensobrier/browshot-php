# Browshot (PHP)

Browshot (https://www.browshot.com/) is a web service to easily make screenshots of web pages in any screen size, as any device: iPhone©, iPad©, Android©, Nook©, PC, etc. Browshot has full Flash, JavaScript, CSS, & HTML5 support.

The latest API version is detailed at https://browshot.com/api/documentation. browshot follows the API documentation very closely: the function names are similar to the URLs used (screenshot/create becomes screenshot_create(), instance/list becomes instance_list(), etc.), the request arguments are exactly the same, etc.

The library version matches closely the API version it handles: Browshot 1.0.0 is the first release for the API 1.0, browshot 1.1.1 is the second release for the API 1.1, etc.

Browshot can handle most the API updates within the same major version, e.g. browshot 1.0.0 should be compatible with the API 1.1 or 1.2.



## Requirements

    PHP 5.1.6 or above
    PHP cURL and JSON extensions
    PHP 7.4 or above and PHPUnit 9.6 (to run unit tests)


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


## API 1.32

Pass capture options in the existing parameter array. Use the instance ID to
choose the browser and capture country.

```php
$client = new Browshot(getenv('BROWSHOT_API_KEY'));
$capture = $client->screenshot_create(array(
    'url' => 'https://example.com/',
    'instance_id' => 373,
    'pdf' => 1,
    'pdf_format' => 'a4',
    'pdf_margin' => '10mm',
    'device_scale' => 2,
    'hide_ads' => 1
));
```

Poll `screenshot_info($capture->id)` until the status is `finished`. Then use
`screenshot_pdf($capture->id)` for the PDF bytes or
`screenshot_pdf_file($capture->id, 'example.pdf')` to save the file directly.
PDF settings include `pdf_format` (`letter`, `a4`, `legal`), `pdf_background`,
`pdf_landscape`, `pdf_one_page`, `pdf_margin`, and the per-side
`pdf_margin_top`, `pdf_margin_bottom`, `pdf_margin_left`, and `pdf_margin_right`.

For a video or animated GIF, request a recording when creating the capture.

```php
$recording = $client->screenshot_create(array(
    'url' => 'https://example.com/',
    'instance_id' => 373,
    'video' => 1,
    'video_format' => 'gif',
    'video_duration' => 5,
    'video_scale' => 0.5
));
```

After the capture finishes, use `screenshot_video($recording->id)` for the bytes
or `screenshot_video_file($recording->id, 'example.gif')` to save the file.
Use `video_format` to select `webm` (default), `mp4`, or `gif`. Options include
`video_duration` (1 to 30 seconds), `video_quality` (0 to 63),
`video_fps` (1 to 30), and `video_scale` (0.1 to 1). GIF uses 20 fps.

Download methods return a binary string. File helpers return the supplied path
on success and require an existing parent directory. Both return an empty
string on failure. An MP4 request can produce WebM when MP4 encoding is
unavailable. File helpers preserve the returned bytes and the supplied path.

| Country | Chrome | Firefox | iPhone 12 | Samsung S20 |
| --- | --- | --- | --- | --- |
| France | 373 | 374 | 375 | 376 |
| India | 377 | 378 | 379 | 380 |
| Finland | 381 | 382 | 383 | 384 |
| United Kingdom | 385 | 386 | 387 | 388 |

Each new instance costs one credit before options. PDF adds one credit per
capture. Recording adds one credit per started five seconds of requested
duration. Both require a paid Chrome-based instance. Recording also requires
`device_scale=1`. Retina (`device_scale=2`) and ad blocking (`hide_ads=1`) add no
credits. Downloads are free.

## Tests

Run `phpunit` from the repository root. Without credentials, the suite runs the
offline tests against a local HTTP server. Set `BROWSHOT_API_KEY` to enable live
tests. Use `BROWSHOT_SCREENSHOT_ID` for an owned finished screenshot and
`BROWSHOT_PDF_ID`, `BROWSHOT_WEBM_ID`, `BROWSHOT_MP4_ID`, and `BROWSHOT_GIF_ID` for
the corresponding completed artifacts. The optional hosting test requires a
disposable owned capture ID in `BROWSHOT_HOST_ID`.

## Contributing to Browshot
 
* Check out the latest master to make sure the feature hasn't been implemented or the bug hasn't been fixed yet
* Check out the issue tracker to make sure someone hasn't requested it and/or contributed to it
* Fork the project
* Start a feature/bugfix branch
* Commit and push until you are happy with your contribution
* Make sure to add tests for it. This is important so I don't break it in a future version unintentionally.

## Copyright

Copyright (c) 2012-2026 Julien Sobrier

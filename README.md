# Neonbug\FiscalVerification

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]

Implementation of the JSON standard for fiscal verification of invoices in Slovenia.

More info available at their site: [http://www.datoteke.fu.gov.si/dpr/index_en.html](http://www.datoteke.fu.gov.si/dpr/index_en.html)

## Install

Via Composer

``` bash
$ composer require neonbug/fiscal-verification
```

## Usage

See `tests` folder (esp. `InvoiceTest.php`) for usage examples.

## Testing

Convert your test certificate to PEM format and place it in `tests/assets` folder.

Open `tests/_config.php` and fill out all the information (for every test to be run successfully, all fields are required).

Afterwards, open a terminal, navigate to the project's folder and run

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email tadej@ncode.si instead of using the issue tracker.

## Credits

- [Tadej Kanižar][link-author]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/neonbug/fiscal-verification.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/neonbug/fiscal-verification/master.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/neonbug/fiscal-verification
[link-travis]: https://travis-ci.org/neonbug/fiscal-verification
[link-author]: https://github.com/tadejkan

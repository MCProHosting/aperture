# Aperture

Aperture is a super simple Laravel package to make sharing and storing unchanging database data easy and effective. Essentially, it provides a quick interface to dump and restore information from a database table. It is build so that, if needed, it should be able to handle unlimited rows without running out of memory.


### Usage

 1. Add this package to your composer.json.
 2. Add the service provider `'Mcprohosting\Aperture\ApertureServiceProvider'` to your list of providers in config/app.php

You then have access to the commands `snapshot:take` and `snapshot:restore`.

```
> php artisan snapshot:take --help

Usage:
 snapshot:take [--database[="..."]] [--chunk[="..."]] table

Arguments:
 table                 Table to snapshot.

Options:
 --database            Database the table lives on.
 --chunk               How many rows to process at once. (default: 500)

> php artisan snapshot:restore --help

Usage:
 snapshot:restore [--database[="..."]] [--chunk[="..."]] table

Arguments:
 table                 Table to snapshot.

Options:
 --database            Database the table lives on.
 --chunk               How many rows to process at once. (default: 500)
```


Licensed under the MIT license.

# ksuid

This is a library for parsing and generating Cuvva IDs in the KSUID format.

Maintained by George Miller

&copy; [Cuvva Ltd](https://github.com/cuvva/jobs) - 2018

## Installation

```bash
composer require cuvva/ksuid
```

## Usage

### Generating

```php
use Cuvva\KSUID\KSUID;

require_once('vendor/autoload.php');

$id = KSUID::generate('resource');

echo("{$id}\n"); // resource_000F5wCycYNiduc6XpnXD5Xd
```

### Parsing

```php
use Cuvva\KSUID\KSUID;

require_once('vendor/autoload.php');

$id = KSUID::parse('resource_000F5wCycYNiduc6XpnXD5Xd');

var_dump($id);
```

## Testing

```bash
composer install cuvva/ksuid

./vendor/bin/phpunit --testdox tests/
```

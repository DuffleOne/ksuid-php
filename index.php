<?php

use Cuvva\KSUID\KSUID;
use Cuvva\KSUID\InstanceIdentifier;

require_once('vendor/autoload.php');

$ids = [];
$ids[] = KSUID::generate('dfl');
$ids[] = KSUID::generate('dfl');
$ids[] = KSUID::generate('dfl');
$ids[] = KSUID::generate('dfl');

dump($ids);

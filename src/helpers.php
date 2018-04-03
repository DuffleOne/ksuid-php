<?php
declare(strict_types=1);

namespace Cuvva\KSUID;

use Cuvva\KSUID\Exceptions\InvalidCharactersException;

function checkIdFragment(string $name, string $str)
{
	if (preg_match(Constants::prefixRegex, $str) == false) {
		throw new InvalidCharactersException("${name} contains invalid characters");
	}

	return;
}

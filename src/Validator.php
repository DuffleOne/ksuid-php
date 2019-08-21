<?php
declare(strict_types=1);

namespace Cuvva\KSUID;

use Cuvva\KSUID\Exceptions as E;

final class Validator
{
	private function __construct()
	{
		// static class only
	}

	public static function checkPrefix(string $field, string $value): void
	{
		if (preg_match(Constants::prefixRegex, $value) != false) {
			return;
		}

		throw new E\InvalidPrefixException("{$field} contains invalid characters");
	}

	public static function checkUint(string $field, int $value, int $byteLength): void
	{
		if ($value < 0) {
			throw new E\NegativeIntegerException("{$field} must be positive");
		}

		if ($value >= 2 ** ($byteLength * 8)) {
			throw new E\InvalidByteLengthException("{$field} must be {$byteLength} bytes");
		}
	}

	public static function checkBuffer(string $field, string $value, int $byteLength): void
	{
		if (strlen($value) !== $byteLength) {
			throw new E\InvalidByteLengthException("{$field} must be {$byteLength} bytes");
		}
	}
}

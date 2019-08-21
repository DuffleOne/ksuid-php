<?php

namespace Cuvva\KSUID;

use Cuvva\KSUID\Exceptions as E;

class InstanceIdentifier
{
	private $scheme;
	private $bytes;
	private $cache;

	public function __construct(int $scheme, string $bytes)
	{
		Validator::checkUint('scheme', $scheme, 1);
		Validator::checkBuffer('bytes', $bytes, 8);

		$this->scheme = chr($scheme);
		$this->bytes = $bytes;
	}

	public function toBuffer(): string
	{
		if ($this->cache) {
			return $this->cache;
		}

		$this->cache = "{$this->scheme}{$this->bytes}";

		Validator::checkBuffer('instance_identifier', $this->cache, 9);

		return $this->cache;
	}

	public static function create(string $buffer): InstanceIdentifier
	{
		Validator::checkBuffer('buffer', $buffer, 9);

		$split = str_split($buffer);
		$scheme = ord($split[0]);
		$bytes = implode('', array_slice($split, 1));

		return new self($scheme, $bytes);
	}

	public function __get($var)
	{
		return $this->{$var};
	}

	public static function createRandom()
	{
		return new self(Constants::RANDOM, random_bytes(8));
	}
}

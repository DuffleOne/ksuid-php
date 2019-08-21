<?php
declare(strict_types=1);

namespace Cuvva\KSUID;

use SplFixedArray;
use Tuupola\Base62Proxy as Base62;
use Cuvva\KSUID\Exceptions as E;
use Cuvva\KSUID\Validator;

class ID
{
	private $environment;
	private $resource;
	private $timestamp;
	private $instance;
	private $sequenceId;
	private $string;

	public function __construct(string $environment, string $resource, int $timestamp, InstanceIdentifier $instance, int $sequenceId)
	{
		Validator::checkPrefix('environment', $environment);
		Validator::checkPrefix('resource', $resource);
		Validator::checkUint('timestamp', $timestamp, 8);
		Validator::checkUint('sequenceId', $sequenceId, 4);

		$this->environment = $environment;
		$this->resource = $resource;
		$this->timestamp = $timestamp;
		$this->instance = $instance;
		$this->sequenceId = $sequenceId;
	}

	public static function parse(string $input): ID
	{
		if (!$input) {
			throw new E\ParseException('input must not be empty');
		}

		$parsed = self::splitPrefixId($input);
		extract($parsed);

		$ba = new SplFixedArray(Constants::decodedLen);

		for ($i = 0; $i < Constants::decodedLen; $i++) {
			$ba[$i] = pack('C', 0);
		}

		$decoded = Base62::decode($encoded);
		$split = array_reverse(str_split($decoded));
		$position = Constants::decodedLen;

		foreach ($split as $item) {
			if ($position < 0) {
				throw new Exception('payload is larger than expected decoded length');
			}

			$ba[$position - 1] = $item;
			$position--;
		}

		$rawTimestamp = $ba[0] . $ba[1] . $ba[2] . $ba[3] . $ba[4] . $ba[5] . $ba[6] . $ba[7];
		$rawInstance = $ba[8] . $ba[9] . $ba[10] . $ba[11] . $ba[12] . $ba[13] . $ba[14] . $ba[15] . $ba[16];
		$rawSequence = $ba[17] . $ba[18] . $ba[19] . $ba[20];

		$timestamp = unpack('J', $rawTimestamp)[1];
		$instance = InstanceIdentifier::create($rawInstance);
		$sequenceId = unpack('N', $rawSequence)[1];

		return new ID (
			$environment,
			$resource,
			$timestamp,
			$instance,
			$sequenceId
		);
	}

	private static function splitPrefixId(string $input): array
	{
		if (preg_match(Constants::ksuidRegex, $input, $parsed) == false) {
			throw new E\ParseException("id is invalid");
		}

		if ($parsed[1] === 'prod') {
			throw new E\ParseException("production env is implied");
		}

		if ($parsed[1] === '') {
			$parsed[1] = 'prod';
		}

		return [
			'environment' => $parsed[1],
			'resource' => $parsed[2],
			'encoded' => $parsed[3],
		];
	}

	public function toString(): string
	{
		if ($this->string) {
			return $this->string;
		}

		$decoded = "";

		$env = $this->environment === 'prod' ? '' : "{$this->environment}_";
		$prefix = "{$env}{$this->resource}_";

		$decoded .= pack('J', $this->timestamp);
		$decoded .= $this->instance->toBuffer();
		$decoded .= pack('N', $this->sequenceId);

		$encoded = Base62::encode($decoded);

		if (strlen($encoded) > Constants::encodedLen) {
			throw new InvalidByteLengthException("decoded byte length is too long");
		}

		$padded = str_pad($encoded, Constants::encodedLen, '0', STR_PAD_LEFT);

		$this->string = "{$prefix}{$padded}";

		return $this->string;
	}

	public function __toString(): string
	{
		return $this->toString();
	}

	public function __get($var)
	{
		return $this->{$var};
	}
}

<?php
declare(strict_types=1);

namespace Cuvva\KSUID;

use Tuupola\Base62Proxy as Base62;
use Cuvva\KSUID\Exceptions\InvalidCharactersException;
use Cuvva\KSUID\Exceptions\InvalidGenerationException;
use Cuvva\KSUID\Exceptions\ParseException;
use function Cuvva\KSUID\checkIdFragment;

class ID
{
	public function __construct(string $environment, string $resource, int $timestamp, string $machineId, int $processId, int $sequenceId)
	{
		checkIdFragment('environment', $environment);
		checkIdFragment('resource', $resource);
		$this->checkInteger('timestamp', $timestamp, 8);
		$this->checkInteger('timestamp', $timestamp - Constants::epoch, 4);
		$this->checkStrLength('machineId', $machineId, 6);
		$this->checkInteger('processId', $processId, 4);
		$this->checkInteger('sequenceId', $sequenceId, 4);

		$this->environment = $environment;
		$this->resource = $resource;
		$this->timestamp = $timestamp;
		$this->machineId = $machineId;
		$this->processId = $processId;
		$this->sequenceId = $sequenceId;
	}

	private function checkInteger(string $name, int $value, int $byteLength): void
	{
		if ($value < 0) {
			throw new InvalidCharactersException("${name} must be positive");
		}

		$bitLength = $byteLength * 8;

		if ($value >= pow(2, $bitLength)) {
			throw new InvalidCharactersException("{$name} must be a uint{$bitLength}");
		}

	}

	private function checkStrLength(string $name, string $value, int $length): void
	{
		if (strlen($value) !== $length) {
			throw new InvalidCharactersException("${name} is not the correct length");
		}
	}

	public static function parse(string $input): ID
	{
		if (!$input) {
			throw new ParseException("input is too short");
		}

		$parsed = self::splitPrefixId($input);
		extract($parsed);

		if ($id[0] !== '0' || $id[1] !== '0') {
			throw new ParseException('invalid version');
		}

		$encoded = substr($id, 2);
		$decoded = Base62::decode($id);

		$split = str_split($decoded);

		if (count($split) !== Constants::decodedLen) {
			throw new ParseException('encoded length too long');
		}

		$timestamp = unpack('N', $split[0].$split[1].$split[2].$split[3])[1];
		$machineId = $split[4].$split[5].$split[6].$split[7].$split[8].$split[9];
		$processId = unpack('n', $split[10].$split[11])[1];
		$sequenceId = unpack('N', $split[12].$split[13].$split[14].$split[15])[1];

		return new ID (
			$environment,
			$resource,
			$timestamp + Constants::epoch,
			$machineId,
			$processId,
			$sequenceId
		);
	}

	private static function splitPrefixId(string $input): array
	{
		if (preg_match(Constants::ksuidRegex, $input, $parsed) == false) {
			throw new ParseException("cannot parse ksuid");
		}

		if ($parsed[1] === 'prod') {
			throw new ParseException("production env is implied");
		}

		if ($parsed[1] === '') {
			$parsed[1] = 'prod';
		}

		return [
			'environment' => $parsed[1],
			'resource' => $parsed[2],
			'id' => $parsed[3],
		];
	}

	public function toString(): string
	{
		$output = "";
		$payload = "";

		if ($this->environment !== '' && $this->environment !== 'prod') {
			$output .= "{$this->environment}_";
		}

		$output .= "{$this->resource}_";
		$output .= '00'; // Add future proofing padding

		$payload .= pack('N', $this->timestamp - Constants::epoch);
		$payload .= $this->machineId;
		$payload .= pack('n', $this->processId);
		$payload .= pack('N', $this->sequenceId);

		$encodedPayload = Base62::encode($payload);

		if (strlen($encodedPayload) > Constants::encodedLen) {
			throw new InvalidGenerationException("payload is too long");
		}

		$paddedPayload = str_pad($encodedPayload, Constants::encodedLen, '0', STR_PAD_LEFT);
		$output .= $paddedPayload;


		return $output;
	}

	public function __toString(): string
	{
		try {
			return $this->toString();
		} catch (Exception $error) {
			return "ERROR: {$error->getMessage()}";
		}
	}
}

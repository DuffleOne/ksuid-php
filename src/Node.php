<?php
declare(strict_types=1);

namespace Cuvva\KSUID;

use function Cuvva\KSUID\checkIdFragment;

final class Node
{
	private $environment;
	private $machineId;
	private $processId;
	private $currentSequence;
	private $lastTimestamp;

	public function __construct(?string $environment = 'prod') {
		$this->environment = $environment;
		$this->machineId = $this->getMacAddress();
		$this->processId = getmypid();

		$this->lastTimestamp = Constants::epoch;
		$this->currentSequence = 0;
	}

	public function getEnvironment(): string
	{
		return $this->environment;
	}

	public function setEnvironment(string $environment): void
	{
		checkIdFragment('environment', $environment);

		$this->environment = $environment;
	}

	public function generate(string $resource): ID {
		$now = time();

		if ($this->lastTimestamp !== $now) {
			$this->lastTimestamp = $now;
			$this->currentSequence = 0;
		}

		$this->currentSequence += 1;

		return new ID (
			$this->environment,
			$resource,
			$this->lastTimestamp,
			$this->machineId,
			$this->processId % Constants::processIdSize,
			$this->currentSequence
		);
	}

	private function getMacAddress(): string
	{
		$out = '';

		try {
			$interfaces = NetworkInterfaces::getInterfaces();

			if (count($interfaces) === 0) {
				return random_bytes(6);
			}

			$interface = $interfaces[0];
			$interface = str_replace(':', '', $interface);

			return hex2bin($interface);
		} catch (UnsupportedOSException $error) {
			return random_bytes(6);
		} catch (Exception $error) {
			throw $error;
		}
	}
}

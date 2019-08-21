<?php
declare(strict_types=1);

namespace Cuvva\KSUID;

use function Cuvva\KSUID\checkIdFragment;

final class Node
{
	private $environment;
	private $instance;
	private $lastTimestamp;
	private $currentSequence;

	public function __construct(?string $environment = 'prod', ?InstanceIdentifier $instance = null) {
		$instance = $this->defaultInstance($instance);

		$this->environment = $environment;
		$this->instance = $instance;
		$this->lastTimestamp = 0;
		$this->currentSequence = 0;
	}

	public function getEnvironment(): string
	{
		return $this->environment;
	}

	public function setEnvironment(string $environment): void
	{
		Validator::checkPrefix('environment', $environment);

		$this->environment = $environment;
	}

	public function setInstanceIdentifier(InstanceIdentifier $instance): void
	{
		$this->instance = $instance;
	}

	public function generate(string $resource): ID {
		$now = time();

		Validator::checkPrefix('resource', $resource);

		if ($this->lastTimestamp !== $now) {
			$this->lastTimestamp = $now;
			$this->currentSequence = 0;
		} else {
			$this->currentSequence += 1;
		}

		return new ID (
			$this->environment,
			$resource,
			$this->lastTimestamp,
			$this->instance,
			$this->currentSequence
		);
	}

	private function defaultInstance(?InstanceIdentifier $instance = null): InstanceIdentifier
	{
		if ($instance !== null)
			return $instance;

		$id = $this->getMacPidNodeId();

		if (isset($id) && $id !== null) {
			return $id;
		}

		return new InstanceIdentifier(Constants::RANDOM, random_bytes(8));
	}

	private function getMacPidNodeId(): ?InstanceIdentifier
	{
		try {
			$macAddresses = (new \Duffleman\MacAddr\Retriever)->all();

			if (!$macAddresses || count($macAddresses) === 0)
				return null;

			$macAddr = $macAddresses[0];

			if (!$macAddr || $macAddr === '00:00:00:00:00:00') {
				return null;
			}

			$macAddr = str_replace(':', '', $macAddr);
			$macAddr = hex2bin($macAddr);
			$processId = getmypid() % 65536;
			$processId = pack('n', "{$processId}");
			$payload = "{$macAddr}{$processId}";

			return new InstanceIdentifier(Constants::MAC_AND_PID, $payload);
		} catch (Exception $error) {
			throw $error;
		}
	}
}

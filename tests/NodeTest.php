<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Cuvva\KSUID\Exceptions\InvalidCharactersException;
use Cuvva\KSUID\Constants;
use Cuvva\KSUID\NetworkInterfaces;
use Cuvva\KSUID\Node;

final class NodeTest extends TestCase
{
	public function testCanChangeEnvironment(): void
	{
		$node = new Node();

		$this->assertEquals($node->getEnvironment(), 'prod');

		$node->setEnvironment('dev');

		$this->assertEquals($node->getEnvironment(), 'dev');
	}

	public function testThrowsWhenEnvHasUnderscore(): void
	{
		$this->expectException(InvalidCharactersException::class);

		$node = new Node();

		$node->setEnvironment('xo_xo');
	}

	public function testThrowsWhenEnvHasExclamationMark(): void
	{
		$this->expectException(InvalidCharactersException::class);

		$node = new Node();

		$node->setEnvironment('!env');
	}

	public function testThrowsWhenEnvHasUppercaseCharacter(): void
	{
		$this->expectException(InvalidCharactersException::class);

		$node = new Node();

		$node->setEnvironment('Env');
	}

	public function testCanGenerateAnId(): void
	{
		$pid = getmypid() % Constants::processIdSize;
		$mac = $this->getMacAddress();

		$node = new Node();
		$id = $node->generate('test');

		$this->assertEquals($id->environment, 'prod');
		$this->assertEquals($id->resource, 'test');
		$this->assertEquals($id->machineId, $mac);
		$this->assertEquals($id->processId, $pid);
		$this->assertEquals($id->sequenceId, 1);
	}

	public function testCanGenerateWithoutNetworkAdapters(): void
	{
		NetworkInterfaces::overrideInterfaces([]);

		$node = new Node();
		$id = $node->generate('test');

		$this->assertEquals(strlen($id->machineId), 6);
	}

	public function testCanGenerateWithCorrectSequencing(): void
	{
		$idsToGenerate = 3;
		$node = new Node();

		for ($i = 0; $i < $idsToGenerate; $i++) {
			$ids[] = $node->generate('dfl');
		}

		foreach ($ids as $key => $id) {
			$this->assertEquals($id->sequenceId, $key + 1);
		}
	}

	public function testThrowsWhenGeneratingWithNumericResource(): void
	{
		$this->expectException(TypeError::class);

		$node = new Node();
		$id = $node->generate(2);
	}

	public function testThrowsWhenGeneratingWithEmptyResource(): void
	{
		$this->expectException(InvalidCharactersException::class);

		$node = new Node();
		$id = $node->generate('');
	}

	public function testGeneratesIdsWithCorrectEnvironmentPrefix(): void
	{
		$devNode = new Node('dev');
		$prodNode = new Node();
		$devId = $devNode->generate('test')->toString();
		$prodId = $prodNode->generate('test')->toString();

		$this->assertStringStartsWith('dev_', $devId);
		$this->assertStringStartsWith('test_', $prodId);
	}

	private function getMacAddress()
	{
		$out = '';

		try {
			$interfaces = NetworkInterfaces::getInterfaces();

			if (count($interfaces) === 0) {
				$out = random_bytes(6);
			}

			$interface = $interfaces[0];
			$interface = str_replace(':', '', $interface);

			$out = $interface;
		} catch (UnsupportedOSException $error) {
			$out = random_bytes(6);
		} catch (Exception $error) {
			throw $error;
		}

		return hex2bin($out);
	}
}

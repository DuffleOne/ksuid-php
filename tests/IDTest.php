<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Cuvva\KSUID\Exceptions\ParseException;
use Cuvva\KSUID\Exceptions\InvalidCharactersException;
use Cuvva\KSUID\ID;

final class IDTest extends TestCase
{
	public function testThrowsWhenParsingIntegerInput(): void
	{
		$this->expectException(TypeError::class);

		ID::parse(6);
	}

	public function testThrowsWhenParsingEmptyStringInput(): void
	{
		$this->expectException(ParseException::class);

		ID::parse('');
	}

	public function testThrowsWhenParsingEnvironmentWithUnderscore(): void
	{
		$this->expectException(ParseException::class);

		ID::parse('_x_test_000EugN9ckqa2BG2hsLa5w2b');
	}

	public function testThrowsWhenParsingEnvironmentWithExclamationMark(): void
	{
		$this->expectException(ParseException::class);

		ID::parse('x!_test_000EugN9ckqa2BG2hsLa5w2b');
	}

	public function testThrowsWhenParsingEnvironmentWithUppercaseCharacter(): void
	{
		$this->expectException(ParseException::class);

		ID::parse('Xx_test_000EugN9ckqa2BG2hsLa5w2b');
	}

	public function testThrowsWhenParsingResourceWithUnderscore(): void
	{
		$this->expectException(ParseException::class);

		ID::parse('__test_000EugN9ckqa2BG2hsLa5w2b');
	}

	public function testThrowsWhenParsingResourceWithExclamationMark(): void
	{
		$this->expectException(ParseException::class);

		ID::parse('t!est_000EugN9ckqa2BG2hsLa5w2b');
	}

	public function testThrowsWhenParsingResourceWithAmpersand(): void
	{
		$this->expectException(ParseException::class);

		ID::parse('Tes&t_000EugN9ckqa2BG2hsLa5w2b');
	}

	public function testThrowsWhenParsingIdWithNoEnvOrResource(): void
	{
		$this->expectException(ParseException::class);

		ID::parse('000EugjcJxFwHtdLIbdFUpv7');
	}

	public function testThrowsWhenParsingWhenIdContainsInvalidCharacters(): void
	{
		$this->expectException(ParseException::class);

		ID::parse('test_000Ev5DO5DsM7ZqZLB2ntdb');
	}

	public function testThrowsWhenParsingWhenIdIsTooLong(): void
	{
		$this->expectException(ParseException::class);

		ID::parse('test_000Ev5DZiV4PC02SAi7u2CcDP');
	}

	public function testThrowsWhenParsingWhenIdIsTooShort(): void
	{
		$this->expectException(ParseException::class);

		ID::parse('test_000Ev5DZiV4PC02SAi7u2Cc');
	}

	public function testThrowsWhenParsingWithInvalidFuturePadding(): void
	{
		$this->expectException(ParseException::class);

		ID::parse('test_100Ev5ILXYIWR1lnaQZXksUb');
	}

	public function testThrowsWhenParsingWhenProdEnvironmentIsSpecified(): void
	{
		$this->expectException(ParseException::class);

		ID::parse('prod_test_000Ev5HPNAKH4uoNQpvOyUgT');
	}

	public function testThrowsWhenCreatedWithIntegerEnvironment(): void
	{
		$this->expectException(TypeError::class);

		$id = new ID(6, 'test', 1522162977, random_bytes(6), 1100, 1);
	}

	public function testThrowsWhenCreatedWithEmptyEnvironment(): void
	{
		$this->expectException(InvalidCharactersException::class);

		$id = new ID('', 'test', 1522162977, random_bytes(6), 1100, 1);
	}

	public function testThrowsWhenCreatedWithTimestampAsString(): void
	{
		$this->expectException(TypeError::class);

		$id = new ID('prod', 'test', 'a', random_bytes(6), 1100, 1);
	}

	public function testThrowsWhenCreatedWhereTimestampIsNegative(): void
	{
		$this->expectException(InvalidCharactersException::class);

		$id = new ID('prod', 'test', -1, random_bytes(6), 1100, 1);
	}

	public function testThrowsWhenCreatedWhereTimestampOverflowsUint32(): void
	{
		$this->expectException(InvalidCharactersException::class);

		$id = new ID('prod', 'test', 9000000000, random_bytes(6), 1100, 1);
	}

	public function testThrowsWhenCreatedWhenMachineIdIsEmptyString(): void
	{
		$this->expectException(InvalidCharactersException::class);

		$id = new ID('prod', 'test', 1522162977, '', 1100, 1);
	}

	public function testThrowsWhenCreatedWhenMachineIdIsTheWrongLength(): void
	{
		$this->expectException(InvalidCharactersException::class);

		$id = new ID('prod', 'test', 1522162977, 'ad0022x', 1100, 1);
	}

	public function testCanParseWithoutEnvironment(): void
	{
		$id = ID::parse('test_000EugN9ckqa2BG2hsLa5w2b');

		$this->assertEquals($id->environment, 'prod');
		$this->assertEquals($id->resource, 'test');
		$this->assertEquals($id->timestamp, 1521138086);
		$this->assertEquals(bin2hex($id->machineId), '8c85901b189c');
		$this->assertEquals($id->processId, 59217);
		$this->assertEquals($id->sequenceId, 1);
	}

	public function testCanParseWithEnvironment(): void
	{
		$id = ID::parse('dev_test_000EugjcJxFwHtdLIbdFUpv7');

		$this->assertEquals($id->environment, 'dev');
		$this->assertEquals($id->resource, 'test');
		$this->assertEquals($id->timestamp, 1521138924);
		$this->assertEquals(bin2hex($id->machineId), '8c85901b189c');
		$this->assertEquals($id->processId, 59395);
		$this->assertEquals($id->sequenceId, 1);
	}
}

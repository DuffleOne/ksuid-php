<?php
declare(strict_types=1);

namespace Cuvva\KSUID;

final class KSUID
{
	private static $node;

	private function __construct()
	{
		// static class only. Cannot construct
	}

	private function __clone()
	{
		// static class only. No cloning
	}

	public static function Instance(): Node
	{
		if (!self::$node) {
			self::$node = new Node();
		}

		return self::$node;
	}

	public static function generate(string $resource): ID
	{
		return self::Instance()->generate($resource);
	}

	public static function parse(string $ksuid): ID
	{
		return ID::parse($ksuid);
	}

	public static function setEnvironment(string $environment): void
	{
		self::Instance()->setEnvironment($environment);
	}

	public static function setInstanceIdentifier(InstanceIdentifier $instance): void
	{
		self::Instance()->setInstanceIdentifier($instance);
	}
}

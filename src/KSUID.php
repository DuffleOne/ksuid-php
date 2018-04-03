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

	public static function generate(string $resource): ID
	{
		if (!self::$node) {
			self::$node = new Node();
		}

		return self::$node->generate($resource);
	}

	public static function parse(string $ksuid): ID
	{
		return ID::parse($ksuid);
	}

	public static function setEnvironment(string $environment): void
	{
		if (!self::$node) {
			self::$node = new Node();
		}

		self::$node->setEnvironment($environment);
	}
}

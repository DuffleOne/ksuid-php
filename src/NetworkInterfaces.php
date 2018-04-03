<?php
declare(strict_types=1);

namespace Cuvva\KSUID;

use Cuvva\KSUID\Exceptions\UnsupportedOSException;

class NetworkInterfaces
{
	private static $interfaces;

	public static function overrideInterfaces(array $input): void
	{
		self::$interfaces = $input;
	}

	public static function getInterfaces(): array
	{
		if (isset(self::$interfaces)) {
			return self::$interfaces;
		}

		$osName = strtoupper(PHP_OS);

		switch ($osName) {
			default:
				throw new UnsupportedOSException("cannot run on {$osName}.");
			case 'DARWIN':
				$ipRes = shell_exec("ifconfig | awk '/ether/{print $2}'");
				$addresses = explode("\n", $ipRes);
				$addresses = array_filter(array_unique(array_map(function ($item) {
					return trim($item);
				}, $addresses)));

				self::$interfaces = $addresses;
				return $addresses;
			case 'LINUX':
				$ipRes = shell_exec('ifconfig');
				$ipPattern = '/inet addr:([\d]' . '{1,3}\.[\d]{1,3}' . '\.[\d]{1,3}\.' . '[\d]{1,3})/';

				if (preg_match_all($ipPattern, $ipRes, $matches)) {
					self::$interfaces = $matches[1];

					return self::$interfaces;
				}

				break;
		}

		return [];
	}
}

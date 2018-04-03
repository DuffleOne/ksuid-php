<?php

namespace Cuvva\KSUID;

final class Constants {
	const decodedLen = 16;
	const encodedLen = 22;
	const epoch = 1388534400; // 2014-01-01T00:00:00+00:00
	const ksuidRegex = "/^(?:([a-z\d]+)_)?([a-z\d]+)_([\da-zA-Z]{24})$/";
	const prefixRegex = "/^[a-z0-9]+$/";
	const processIdSize = 65536;
}

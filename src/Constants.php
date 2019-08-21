<?php

namespace Cuvva\KSUID;

final class Constants {
	const decodedLen = 21;
	const encodedLen = 29;
	const ksuidRegex = "/^(?:([a-z\d]+)_)?([a-z\d]+)_([a-zA-Z\d]{29})$/";
	const prefixRegex = "/^[a-z\d]+$/";

	const RANDOM = 82; // R
	const MAC_AND_PID = 72; // H
	const DOCKER_CONT = 68; // D
}

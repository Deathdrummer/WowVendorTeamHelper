<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

final class OrderStatus extends Enum {
	const new		= 0;
	const wait		= -1;
	const cancel	= -2;
	const ready		= 1;
	const doprun	= 2;
	const necro		= -3;
}

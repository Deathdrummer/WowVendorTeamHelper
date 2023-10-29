<?php declare(strict_types=1); 

namespace App\Enums;

use BenSampo\Enum\Attributes\Description;
use BenSampo\Enum\Enum;

final class LogEventsGroups extends Enum {
	#[Description('{"ru": "События","en":""}')]
	const timesheets		= 1;
	
	#[Description('{"ru": "Заказы","en":""}')]
	const orders			= 2;
	
	#[Description('{"ru": "Уведомления Slack","en":""}')]
	const slack_notifies	= 3;
	
	// LogActionTypes::getDescription(5)
}
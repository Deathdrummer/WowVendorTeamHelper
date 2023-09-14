<?php declare(strict_types=1); 

namespace App\Enums;

use BenSampo\Enum\Attributes\Description;
use BenSampo\Enum\Enum;

final class LogEventsTypes extends Enum {
	#[Description('{"ru": "Создано событие","en":""}')]
	const timesheetCreated	= 1;
	
	#[Description('{"ru": "Обновлено событие","en":""}')]
	const timesheetUpdated	= 2;
	
	#[Description('{"ru": "Удалено событие","en":""}')]
	const timesheetRemoved	= 3;
	
	
	// LogActionTypes::getDescription(5)
}
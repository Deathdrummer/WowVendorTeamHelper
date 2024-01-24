<?php declare(strict_types=1); 

namespace App\Enums;

use BenSampo\Enum\Attributes\Description;
use BenSampo\Enum\Enum;

final class LogEventsTypes extends Enum {
	#[Description('{"ru": "Создано событие","en":""}')]
	const timesheetCreated = 1;
	
	#[Description('{"ru": "Обновлено событие","en":""}')]
	const timesheetUpdated = 2;
	
	#[Description('{"ru": "Удалено событие","en":""}')]
	const timesheetRemoved = 3;
	
	
	
	
	#[Description('{"ru": "Создан заказ в событии","en":""}')]
	const orderCreatedInTs = 4;
	
	#[Description('{"ru": "Обновлен заказ","en":""}')]
	const orderUpdated = 5;
	
	#[Description('{"ru": "Заказ отправлен в лист ожидания","en":""}')]
	const orderToWaitlList = 6;
	
	#[Description('{"ru": "Заказ склонирован в лист ожидания","en":""}')]
	const orderCloneToWaitlList = 22;
	
	#[Description('{"ru": "Заказы отправлены в лист ожидания","en":""}')]
	const ordersToWaitlList = 17;
	
	#[Description('{"ru": "Заказ отправлен в отмененные","en":""}')]
	const orderToCancelList	= 7;
	
	#[Description('{"ru": "Заказы отправлены в отмененные","en":""}')]
	const ordersToCancelList	= 18;
	
	#[Description('{"ru": "Заказ отправлен в некро","en":""}')]
	const orderToNecroList	= 19;
	
	#[Description('{"ru": "Заказы отправлены в некро","en":""}')]
	const ordersToNecroList	= 20;
	
	
	
	
	#[Description('{"ru": "Заказ привязан к событию","en":""}')]
	const orderAttach = 8;
	
	#[Description('{"ru": "Заказы привязаны к событию","en":""}')]
	const ordersAttach = 21;
	
	#[Description('{"ru": "Заказ перенесен в другое событие","en":""}')]
	const orderMove	= 9;
	
	#[Description('{"ru": "Допран заказа в другое событие","en":""}')]
	const orderDoprun = 10;
	
	#[Description('{"ru": "Заказ отправлен на подтверждение","en":""}')]
	const orderToConfirm = 11;
	
	#[Description('{"ru": "Заказ подтвержден","en":""}')]
	const orderConfirm = 12;
	
	#[Description('{"ru": "Несколько заказов подтверждено","en":""}')]
	const ordersConfirm = 13;
	
	#[Description('{"ru": "Заказ удален из списка для подтверждения","en":""}')]
	const orderRemoveFromConfirmed = 14;
	
	#[Description('{"ru": "Заказ отвязан от события","en":""}')]
	const orderDetach = 15;
	
	
	
	#[Description('{"ru": "Отправлено уведомление Slack","en":""}')]
	const slackSendMessage = 16;
	
	
	
	// LogActionTypes::getDescription(5)
}
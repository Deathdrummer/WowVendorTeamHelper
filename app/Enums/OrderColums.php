<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Attributes\Description;
use BenSampo\Enum\Enum;

final class OrderColums extends Enum {
	#[Description('{"ru":"№ заказа","en":"№ заказа"}')]
	const order		= 1;
	
	#[Description('{"ru":"Дата","en":""}')]
	const date		= 2;
	
	#[Description('{"ru":"Добавл. в соб.","en":""}')]
	const date_add	= 11;
	
	#[Description('{"ru":"Тип заказа","en":""}')]
	const type		= 3;
	
	#[Description('{"ru":"Фракция","en":""}')]
	const fraction	= 9;
	
	#[Description('{"ru":"Б. таг","en":""}')]
	const battle_tag	= 10;
	
	#[Description('{"ru":"Данные","en":""}')]
	const data		= 4;
	
	#[Description('{"ru":"Инв","en":""}')]
	const invite	= 5;
	
	#[Description('{"ru":"Комментарий","en":""}')]
	const comment	= 6;
	
	#[Description('{"ru":"Стоимость","en":""}')]
	const price		= 7;
	
	#[Description('{"ru":"Уведомления","en":""}')]
	const notifies	= 8;
	
}

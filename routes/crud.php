<?php

use App\Http\Controllers\Business\AccountingController;
use App\Http\Controllers\Business\CommandsController;
use App\Http\Controllers\Business\EventsLogsController;
use App\Http\Controllers\Business\EventsTypesController;
use App\Http\Controllers\Business\OrdersController;
use App\Http\Controllers\Business\TimesheetController;
use App\Http\Controllers\Business\TimesheetPeriodsController;
use Illuminate\Support\Facades\Route;





//-------------------------------------------------------------------------------------------------- Статики
Route::post('statics/store_show', [CommandsController::class, 'store_show']);
Route::resource('statics', CommandsController::class);



//-------------------------------------------------------------------------------------------------- Типы событий
Route::post('events_types/store_show', [EventsTypesController::class, 'store_show']);
Route::resource('events_types', EventsTypesController::class);



//-------------------------------------------------------------------------------------------------- Расписание
// Периоды
Route::get('timesheet_periods/init', [TimesheetPeriodsController::class, 'init']);
Route::get('timesheet_periods/last_periods', [TimesheetPeriodsController::class, 'last_periods']);
Route::post('timesheet_periods/store_show', [TimesheetPeriodsController::class, 'store_show']);
Route::resource('timesheet_periods', TimesheetPeriodsController::class)->only(['index', 'create', 'destroy']);

// События
Route::get('timesheet/init', [TimesheetController::class, 'init']);
Route::get('timesheet/orders', [TimesheetController::class, 'orders']);
Route::post('timesheet/store_show', [TimesheetController::class, 'store_show']);
Route::get('timesheet/import_form', [TimesheetController::class, 'get_import_form']);
Route::post('timesheet/import', [TimesheetController::class, 'import_events']);
Route::get('timesheet/export', [TimesheetController::class, 'export_orders_form']);
Route::post('timesheet/export', [TimesheetController::class, 'export_orders']);
Route::get('timesheet/comment', [TimesheetController::class, 'comment_form']);
Route::post('timesheet/comment', [TimesheetController::class, 'comment_save']);
Route::get('timesheet/orders_counts_stat', [TimesheetController::class, 'orders_counts_stat']);
Route::resource('timesheet', TimesheetController::class)->except(['show']);


// Заказы
Route::get('orders/timesheet_list', [OrdersController::class, 'timesheet_list']);
Route::get('orders/form', [OrdersController::class, 'form']);
Route::post('orders/form', [OrdersController::class, 'save_form']);
Route::put('orders/form', [OrdersController::class, 'update_form']);
Route::get('orders/comments', [OrdersController::class, 'comments']);
Route::post('orders/send_comment', [OrdersController::class, 'send_comment']);
Route::get('orders/rawdatahistory', [OrdersController::class, 'rawdatahistory']);
Route::get('orders/statuses', [OrdersController::class, 'statuses']);
Route::post('orders/set_status', [OrdersController::class, 'set_status']);
Route::get('orders/relocate', [OrdersController::class, 'relocate']);
Route::get('orders/relocate/get_timesheets', [OrdersController::class, 'get_relocate_timesheets']);
Route::post('orders/relocate', [OrdersController::class, 'set_relocate']);
Route::get('orders/detach', [OrdersController::class, 'detach_form']);
Route::post('orders/detach', [OrdersController::class, 'detach']);
Route::get('orders/confirmed', [OrdersController::class, 'confirmed_orders']);
Route::put('orders/confirm', [OrdersController::class, 'confirm_order']);
Route::put('orders/confirm_all', [OrdersController::class, 'confirm_all_orders']);
Route::delete('orders/confirm', [OrdersController::class, 'remove_order_from_confirmed']);




// Логи действий пользователей
Route::get('events_logs', [EventsLogsController::class, 'index']);
Route::get('event_log', [EventsLogsController::class, 'info']);




// Бухгалтерия
Route::get('accounting', [AccountingController::class, 'index']);
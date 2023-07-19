<?php namespace App\Exceptions;


use App\Services\Locale;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }
	
	
	
	
	
	
	
	
	
	
	
	
	// ------ DDR
	
	
	
	/**
     * Логирование ошибок
     * Throwable $e
     */
	public function report(Throwable $e) {
		$code = $e->getCode();
		$message = $e->getMessage();
		$line = $e->getLine();
		$file = $e->getFile();
		
		// сюда добавить классы ошибок, которые не нужно логировать
		if ($e instanceof TokenMismatchException) return false;
		if ($e instanceof ValidationException) return false;
		
		Log::error("[{$code}] \"{$message}\" of file: {$file}:{$line} on line: {$line}");
		
		//parent::report($e);
	}
	
	
	/**
     * Отрисовка ошибок в соответствии с типом запроса
	 * $request
     * Throwable $e
     */
	public function render($request, Throwable $e) {
		$details = parent::render($request, $e);
		if ($this->isHttpException($e) && !$request->expectsJson()) {
			return $details;
		} elseif($request->expectsJson()) {
			$locale = new Locale('admin');
			$locale->set();
			$errData = $details->getData();
			$errData->status = $details->getStatusCode();
            $errData->message = __('errors.'.$errData->status) ?: $details->message;
			return response()->json($errData);
		}
		return $details;
		// !env('APP_DEBUG', false)
    }
}

<?php namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Helpers\DdrDateTime;
use App\Mail\ResetPassword;
use App\Mail\VerifyEmail;
use App\Models\Traits\Collectionable;
use App\Models\Traits\Dateable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail {
	use HasApiTokens, HasFactory, Notifiable, HasRoles, Collectionable, Dateable;
	
	/**
     * Таблица
	 *
     * @var string
     */
	protected $table = 'users';
	
	
	/**
     * Раздел аутентификации
	 *
     * @var string
     */
	protected $guard = 'site';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
		'name',
		'pseudoname',
		'email',
		'password',
		'locale',
		'verification_token',
		'email_verified_at',
		'settings',
		'_sort',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Атрибуты, которые должны быть типизированы. (Конвертация полей при добавлении и получении)
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'settings' => 'array',
    ];
	
	
	
	
	
	
	
	
	
	
	
	/**
	 * @param 
	 * @return 
	 */
	public function setPasswordAttribute($password) {
		$this->attributes['password'] = Hash::make($password);
	}
	
	
	
	
	
	/**
	 * @param 
	 * @return 
	 */
	public function getEmailAttribute($email) {
		return decodeEmail($email);
	}
	
	
	
	/**
	 * @param 
	 * @return Carbon|null
	 */
	public function getEmailVerifiedAtAttribute($timestamp):Carbon|null {
		return $timestamp ? DdrDateTime::shift($timestamp, 'TZ') : null;
	}
	
	
	/**
	 * @param 
	 * @return 
	 */
	//public function setEmailAttribute($email) {
	//	$encoder = new IdnAddressEncoder();
	//	$this->attributes['email'] = $encoder->encodeString($email);
	//}
	
	
	
	
	
	
	
	
	
	/**
	 * Send the email verification notification.
	 *
	 * @return void
	 */
	public function sendEmailVerificationNotification() {
		$this->notify(new VerifyEmail('site'));
	}
	
	
	/**
	 * Send the password reset notification.
	 *
	 * @param  string  $token
	 * @return void
	 */
	public function sendPasswordResetNotification($token) {
		$this->notify(new ResetPassword($token, 'site'));
	}
	
	
	
}

<?php namespace App\Actions;

use App\Models\AdminUser;
use App\Models\OrderComment;
use App\Models\User;

class AddOrderCommentAction {
	
	/**
	* Добавить комментарий к заказу
	* @param integer $orderId ID заказа
	* @param string $message сообщение
	* @return array
	*/
	public function __invoke($orderId, $message):array {
		if (!$orderId || !$message) return false;
		
		$guard = getGuard();
		
		$selfId = auth($guard)->user()->id;
		
		$userType = match($guard) {
			'site'	=> 1,
			'admin'	=> 2,
			default	=> 1,
		};
		
		$comment = OrderComment::create([
			'order_id'	=> $orderId,
			'from_id'	=> $selfId,
			'user_type'	=> $userType,
			'message'	=> $message,
		])->toArray();
		
		
		$userData = match($userType) {
			1		=> User::find($selfId),
			2		=> AdminUser::find($selfId),
			default	=> User::find($selfId),
		};
		
		$comment['author'] = [
			'name'			=> $userData['name'],
			'pseudoname'	=> $userData['pseudoname'],
		];
		
		return $comment;
	}
}
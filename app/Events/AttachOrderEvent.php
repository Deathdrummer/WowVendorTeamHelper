<?php namespace App\Events;

use App\Actions\GetUserSetting;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AttachOrderEvent implements ShouldBroadcastNow {
    use Dispatchable, InteractsWithSockets, SerializesModels;
	
	private $data;
	private $command;
    
	
	/**
     * Create a new event instance.
     */
    public function __construct($data, $command) {
		$this->data = $data;
		$this->command = $command;
	}
	
	
	
	/**
	* Определить, условия трансляции события.
	*
	* @return bool
	*/
	public function broadcastWhen() {
		$userSetting = app()->make(GetUserSetting::class);
		if (!in_array($this->command, $userSetting('commands'))) return false;
		return auth('site')->user()->can('notify-order-attached:site');
	}








    /**
     * Get the channels the event should broadcast on. (channel)
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): Channel {
		return new Channel('notyfy_channel');
    }
	
	/**
     * listen
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastAs(): string {
		return 'attachOrder';
    }
	
	
	/**
     * params
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastWith(): array {
		return ['data' => $this->data];
    }
	
	
	
	
	
}

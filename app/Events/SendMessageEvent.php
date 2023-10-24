<?php namespace App\Events;


use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SendMessageEvent implements ShouldBroadcastNow {
    use Dispatchable, InteractsWithSockets, SerializesModels;
	
	private $orders;
    
	
	/**
     * Create a new event instance.
     */
    public function __construct($orders) {
		$this->orders = $orders;
	}
	
	
	
	/**
	* Определить, условия трансляции события.
	*
	* @return bool
	*/
	public function broadcastWhen() {
		return true;
	}
	
	
	
	
	
	
    /**
     * Get the channels the event should broadcast on. (channel)
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): Channel {
		return new Channel('send_message_channel');
    }
	
	/**
     * listen
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastAs(): string {
		return 'incoming_orders';
    }
	
	
	/**
     * params
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastWith(): array {
		return ['orders' => $this->orders];
    }
	
}

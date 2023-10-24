<?php namespace App\Events;


use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AttachOrderEvent implements ShouldBroadcastNow {
    use Dispatchable, InteractsWithSockets, SerializesModels;
	
	private $data;
    
	
	/**
     * Create a new event instance.
     */
    public function __construct($data) {
		$this->data = $data;
	}



    /**
     * Get the channels the event should broadcast on. (channel)
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): Channel {
		return new Channel('test_channel');
    }
	
	/**
     * listen
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastAs(): string {
		return 'test';
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

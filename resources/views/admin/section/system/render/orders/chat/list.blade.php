<div>
	<div class="chat" id="chatMessageList">
		@if($comments)
			@foreach($comments as $comment)
				@include($itemView, $comment)
			@endforeach
		@endif
	</div>
	
	{{-- @cando('contract-col-chat-can-sending:site') --}}
		<div class="mt2rem">
			<div class="row align-items-end">
				<div class="col">
					<div
						id="chatMessageBlock"
						class="color-gray-600 border-all border-gray-300 border-radius-5px minh4rem maxh15rem scrollblock p1rem breakword"
						contenteditable
						>
						
					</div>
				</div>
				<div class="col-auto">
					<x-button
						id="chatSendMesageBtn"
						group="large"
						variant="blue"
						disabled
						title="Отправить сообщение"
						action="chatSendMesage:{{$orderId ?? null}}"
						><i class="fa-solid fa-paper-plane"></i></x-button>
				</div>
			</div>
		</div>
	{{-- @endcando --}}
</div>
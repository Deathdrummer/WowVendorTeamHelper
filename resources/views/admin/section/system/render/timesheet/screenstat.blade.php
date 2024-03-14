<div class="ddrtabs">
	<div class="ddrtabs__nav fb15rem">
		<ul class="ddrtabsnav fz12px" ddrtabsnav>
			<li class="ddrtabsnav__item ddrtabsnav__item_active" ddrtabsitem="tabScreenstatForm">Добавить скриншот и статистику</li>
			<li class="ddrtabsnav__item" onclick="$.getScreenstatHistory(this, this.classList.contains('ddrtabsnav__item_active'));" ddrtabsitem="tabScreenstatHistory">История</li>
		</ul>
	</div>
	
	<div class="ddrtabs__content ddrtabscontent pl10px" ddrtabscontent>
		<div class="ddrtabscontent__item ddrtabscontent__item_visible" ddrtabscontentitem="tabScreenstatForm">
			<div class="ddrlist">
				<div class="ddrlist__item mb15px">
					<p class="color-gray-600 mb3px fz12px">Статус:</p>
					
					@if($eventTypes)
						<x-chooser variant="neutral" size="small" px="20" py="5" class="mb1rem" id="screenstatEvent">
							@foreach($eventTypes as $seTypeId => $seTypeTitle)
								<x-chooser.item
									action="chooseScreenStatEvebtType:{{$seTypeId}}"
									{{-- active="{{$loop->first}}" --}}
									screenstatevent="{{$seTypeId}}"
									>{{$seTypeTitle ?? '-'}}</x-chooser.item>
							@endforeach
						</x-chooser>
					@else
						<p class="color-gray-500">Нет статусов</p>
					@endif
				</div>
				
				<div class="ddrlist__item mb15px">
					<p class="color-gray-600 mb4px fz12px">Скриншот:</p>
					<x-input
						action="setScreenshot"
						class="w100"
						size="normal"
					 	/>
				</div>
				
				<div class="ddrlist__item mb15px">
					<p class="color-gray-600 mb4px fz12px">Статистика и заказы:</p>
					<div class="row gx-6" id="ssStatOrdersForm">
						
						@foreach($sortedOrdersTypes as $otId => $otTitle)
							@if(!isset($ordersTypesCounts[$otId]))
								@continue
							@endif
							<div class="col-auto">
								
								<x-checklabel
									checked
								    id="checklabel{{$otId}}"
								    wrapper="checklabelwrapper"
								    tag="orderTypeId:{{$otId}}"
								    {{-- action="" --}}
								    class="w10rem text-center pointer d-block p2px border-radius-4px border-all border-gray"
									{{-- checkedclass="outline outline_width-2px outline_color-blue" --}}
									{{-- inistyle="background-color: #0f0;" --}}
									checkedstyle="background-color: #c6f4fb; border-color: #ace6ef;"
									title="Выбрать"
									>
									<p class="text-center mb2px"><strong>{{$otTitle ?? '-'}}</strong></p>
									<x-input
										class="w5rem ml-auto mr-auto"
										showrows
										size="small"
										type="number"
										value="{{$ordersTypesCounts[$otId] ?? 0}}"
										tag="ssorderscout"
									 	/>
									
									<div class="p2px mt6px border-radius-4px ddrlist" id="ssOrdersNumbers">
										@forelse($otOrders[$otId] ?? [] as $key => $order)
											<div class="ddrlist__item mt3px">
												<div class="d-flex">
													<x-checkbox
														id="chooseOtOrder{{$otId}}_{{$key}}"
														name="checkname"
														group="small"
														value="{{$order}}"
														checked="true"
														label="{{$order ?? '-'}}"
														tag="ordertypeorder:{{$order}}"
														action="ssChooseOrderNumber"
														/>
													
													{{-- <label for="chooseOtOrder{{$otId}}_{{$key}}">
														<p class="pointer mt1px fz12px">{{$order ?? '-'}}</p>
													</label> --}}
												</div>
													
											</div>
										@empty
											<p class="color-gray-500">-</p>
										@endforelse
									</div>
								</x-checklabel>
								
								{{-- <div class="w-10rem">
									<input
										hidden
										type="checkbox"
										checked="true"
										id="chooseOt{{$otId}}"
										>	
									<div style="position:relative;">
										<label for="chooseOt{{$otId}}" style="position: absolute; left:0; top:0; width:100%; height:100%;"></label>
										<p class="text-center mb2px">{{$otTitle ?? '-'}}</p>
										
										
									</div>
									
										
								</div> --}}
									
							</div>
						@endforeach
					</div>
				</div>
			</div>	
		</div>
		
		<div class="ddrtabscontent__item" ddrtabscontentitem="tabScreenstatHistory">
			<div id="creenStatHistory" class="w100 minh50rem"></div>
		</div>
	</div>
</div>
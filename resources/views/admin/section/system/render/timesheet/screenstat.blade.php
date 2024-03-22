<div class="ddrtabs">
	<div class="ddrtabs__nav fb15rem">
		<ul class="ddrtabsnav fz12px" ddrtabsnav>
			@if(getGuard() == 'admin' || (getGuard() == 'site' && auth('site')->user()->can('screenstat_sending:site')))
			<li class="ddrtabsnav__item ddrtabsnav__item_active" ddrtabsitem="tabScreenstatForm">Добавить скриншот и статистику</li>
			@endif
			<li
				@class([
					'ddrtabsnav__item',
					'ddrtabsnav__item_active'	=> getGuard() != 'admin' && auth('site')->user()->cannot('screenstat_sending:site')
				])
				onclick="$.getScreenstatHistory(this, this.classList.contains('ddrtabsnav__item_active'));"
				ddrtabsitem="tabScreenstatHistory"
				>История</li>
		</ul>
	</div>
	
	<div class="ddrtabs__content ddrtabscontent pl10px" ddrtabscontent>
		@if(getGuard() == 'admin' || (getGuard() == 'site' && auth('site')->user()->can('screenstat_sending:site')))
			<div @class([
					'ddrtabscontent__item',
					'ddrtabscontent__item_visible' => getGuard() == 'admin' || (getGuard() == 'site' && auth('site')->user()->can('screenstat_sending:site'))
				])
				ddrtabscontentitem="tabScreenstatForm"
				>
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
							<p class="color-gray-500 fz12px">Нет статусов</p>
						@endif
					</div>
					
					<div class="ddrlist__item mb15px">
						<div class="row gx-4">
							<div class="col-5">
								<p class="color-gray-600 mb4px fz12px">Скриншот:</p>
								<x-input
									action="setScreenshot"
									class="w100"
									size="normal"
									enablecontextmenu
								 	/>
							</div>
							<div class="col-7">
								<p class="color-gray-600 mb4px fz12px">Комментарий:</p>
								<x-textarea
									action="setComment"
									class="w100"
									size="normal"
									rows="3"
									enablecontextmenu
								 	></x-textarea>
							</div>
						</div>
					</div>
					
					<hr class="hr-light mt1rem mb1rem">
					
					<div class="ddrlist__item mb15px">
						<p class="color-gray-600 mb4px fz12px">Статистика и заказы:</p>
						<div class="row gx-6" id="ssStatOrdersForm" emptytext="Нет данных">
							@foreach($sortedOrdersTypes as $otId => $otTitle)
								@if(!isset($ordersTypesCounts[$otId])) @continue @endif
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
								</div>
							@endforeach
						</div>
					</div>
				</div>	
			</div>
		@endif
		
		<div @class([
				'ddrtabscontent__item',
				'ddrtabscontent__item_visible'	=> getGuard() != 'admin' && auth('site')->user()->cannot('screenstat_sending:site')
			])
			@if(getGuard() != 'admin' && auth('site')->user()->cannot('screenstat_sending:site'))
			onlyhistory
			@endif
			ddrtabscontentitem="tabScreenstatHistory"
			id="tabScreenstatHistory"
			>
			<div id="creenStatHistory" class="w100 minh50rem"></div>
		</div>
	</div>
</div>
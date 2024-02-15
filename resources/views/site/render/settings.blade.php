<div class="ddrtabs">
	<div class="ddrtabs__nav fb20rem">
		<ul class="ddrtabsnav fz14px" ddrtabsnav>
			<li class="ddrtabsnav__item ddrtabsnav__item_active" ddrtabsitem="userSettingsTabOrderFields">Настройки полей заказа в событии</li>
		</ul>
	</div>
				
	<div class="ddrtabs__content ddrtabscontent" ddrtabscontent="">
		<div class="ddrtabscontent__item ddrtabscontent__item_visible" ddrtabscontentitem="userSettingsTabOrderFields">
			
			<x-table class="w100">
				<x-table.head>
					<x-table.tr>
						<x-table.td class="w-auto"><strong>Поле</strong></x-table.td>
						<x-table.td class="w8rem" title="Ширина поля"><strong>Ширина</strong></x-table.td>
						<x-table.td class="w6rem" title="Сортировка поля"><strong>Сорт.</strong></x-table.td>
						<x-table.td class="w4rem h-center" title="Отобразить поле"><i class="fa-solid fa-fw fa-eye"></i></x-table.td>
					</x-table.tr>
				</x-table.head>
				<x-table.body>
					<x-table.tr class="h3rem-8px">
						<x-table.td><p>Выбор заказа</p></x-table.td>
						<x-table.td></x-table.td>
						<x-table.td></x-table.td>
						<x-table.td class="h-center">
							<x-checkbox
								size="normal"
								name="order_colums.show.-1:single"
								:checked="$userColumsSettings['show'][-1] ?? false"
								value="1"
								/>
						</x-table.td>
					</x-table.tr>
					<x-table.tr class="h3rem-8px">
						<x-table.td><p>Порядковый номер</p></x-table.td>
						<x-table.td></x-table.td>
						<x-table.td></x-table.td>
						<x-table.td class="h-center">
							<x-checkbox
								size="normal"
								name="order_colums.show.-2:single"
								:checked="$userColumsSettings['show'][-2] ?? false"
								value="1"
								/>
						</x-table.td>
					</x-table.tr>
					
					@forelse($orderColums as ['key' => $column, 'value' => $colKey, 'desc' => $colName])
						@cando($column.'-(client):site')
							<x-table.tr class="h3rem-8px">
								<x-table.td><p>{{$colName ?? '-'}}</p></x-table.td>
								<x-table.td>
									@if(!in_array($column, ['data', 'status', 'notifies']))
										<x-input
											size="normal"
											type="number"
											showrows
											name="order_colums.width.{{$colKey}}"
											:value="$userColumsSettings['width'][$colKey] ?? null"
											placeholder=""
											/>
									@endif
								</x-table.td>
								<x-table.td>
									<x-input
										size="normal"
										type="number"
										showrows
										name="order_colums.sort.{{$colKey}}"
										:value="$userColumsSettings['sort'][$colKey] ?? null"
										placeholder=""
										/>
								</x-table.td>
								<x-table.td class="h-center">
									<x-checkbox
										size="normal"
										name="order_colums.show.{{$colKey}}:single"
										:checked="$userColumsSettings['show'][$colKey] ?? false"
										value="1"
										/>
								</x-table.td>
							</x-table.tr>
						@endcando
					@empty
					@endforelse
					
					<x-table.tr class="h3rem-8px">
						<x-table.td><p>Статус</p></x-table.td>
						<x-table.td></x-table.td>
						<x-table.td></x-table.td>
						<x-table.td class="h-center">
							<x-checkbox
								size="normal"
								name="order_colums.show.-3:single"
								:checked="$userColumsSettings['show'][-3] ?? false"
								value="1"
								/>
						</x-table.td>
					</x-table.tr>
					
					<x-table.tr class="h3rem-8px">
						<x-table.td><p>Ссылка</p></x-table.td>
						<x-table.td></x-table.td>
						<x-table.td></x-table.td>
						<x-table.td class="h-center">
							<x-checkbox
								size="normal"
								name="order_colums.show.-4:single"
								:checked="$userColumsSettings['show'][-4] ?? false"
								value="1"
								/>
						</x-table.td>
					</x-table.tr>
					
					<x-table.tr class="h3rem-8px">
						<x-table.td><p>Действия</p></x-table.td>
						<x-table.td></x-table.td>
						<x-table.td></x-table.td>
						<x-table.td class="h-center">
							<x-checkbox
								size="normal"
								name="order_colums.show.-5:single"
								:checked="$userColumsSettings['show'][-5] ?? false"
								value="1"
								/>
						</x-table.td>
					</x-table.tr>
				</x-table.body>
			</x-table>
		</div>
	</div>
</div>
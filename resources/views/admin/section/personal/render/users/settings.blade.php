<div class="ddrtabs">
	<div class="ddrtabs__nav b20rem">
		<ul class="ddrtabsnav" ddrtabsnav>
			<li class="ddrtabsnav__item fz14px ddrtabsnav__item_active" ddrtabsitem="testTabCommands">Доступ к командам</li>
			{{-- <li class="ddrtabsnav__item" ddrtabsitem="testTab2">Название вкладки 2</li>
			<li class="ddrtabsnav__item" ddrtabsitem="testTab3">Название вкладки 3</li>
			<li class="ddrtabsnav__item" ddrtabsitem="testTab4">Название вкладки 4</li> --}}
		</ul>
	</div>
	
	<div class="ddrtabs__content ddrtabscontent" ddrtabscontent>
		<div class="ddrtabscontent__item ddrtabscontent__item_visible" ddrtabscontentitem="testTabCommands">
			<x-table class="w100" noborder scrolled="70vh">
				<x-table.head>
					<x-table.tr class="h3rem" noborder>
						<x-table.td class="w20rem v-start" noborder><strong>Название команды</strong></x-table.td>
						<x-table.td class="w20rem v-start" noborder><strong>Временная зона</strong></x-table.td>
						<x-table.td class="w-auto v-start" noborder></x-table.td>
						<x-table.td class="w-5rem h-center v-start" noborder><i class="fa-solid fa-check" title="Выбрать"></i></x-table.td>
					</x-table.tr>
				</x-table.head>
				<x-table.body>
					@foreach($commands as $command)
						<x-table.tr class="h3rem" noborder>
							<x-table.td><p>{{$command['title']}}</p></x-table.td>
							<x-table.td><p>{{$data['timezones'][$command['region_id']]['timezone'] ?? '-'}}</p></x-table.td>
							<x-table.td noborder></x-table.td>
							<x-table.td class="h-center">
								<x-checkbox
									size="normal"
									name="commands"
									ddr-data="terterte"
									:value="$command['id']"
									checked="{{in_array($command['id'], $userCommands ?? [])}}"
									/>
							</x-table.td>
						</x-table.tr>
					@endforeach
				</x-table.body>
			</x-table>
		</div>
		{{-- <div class="ddrtabscontent__item" ddrtabscontentitem="testTab2">
			Lorem ipsum dolor sit amet, consectetur adipisicing elit. Temporibus totam minus, explicabo, error adipisci nulla labore eaque molestiae id tempore?
		</div>
		<div class="ddrtabscontent__item" ddrtabscontentitem="testTab3">
			Lorem ipsum dolor sit amet, consectetur adipisicing elit. Aut mollitia quo consectetur fugiat eveniet officia voluptatibus. Tempora dolores aspernatur culpa cumque aliquam, ab eaque. Error assumenda nostrum possimus fugit autem officia blanditiis sint totam, quisquam.
		</div>
		<div class="ddrtabscontent__item" ddrtabscontentitem="testTab4">
			Lorem ipsum dolor sit amet consectetur adipisicing elit. Rerum excepturi eveniet enim aspernatur voluptates nam a qui alias atque ducimus quidem officiis, consequatur architecto, ea distinctio.
		</div> --}}
	</div>
</div>
<section>
	<x-settings>
		<x-card
			loading="{{__('ui.loading')}}"
			ready
			class="p0"
			>
			<div class="ddrtabs">
				<div class="ddrtabs__nav">
					<ul class="ddrtabsnav" ddrtabsnav>
						<li class="ddrtabsnav__item ddrtabsnav__item_active" ddrtabsitem="sectionsTab1">Заказы</li>
					</ul>
				</div>
				
				{{--  --}}
				<div class="ddrtabs__content ddrtabscontent" ddrtabscontent>
					<div class="ddrtabscontent__item ddrtabscontent__item_visible" ddrtabscontentitem="sectionsTab1">
						<div class="row row-cols-1 gy-20">
							<div class="col">
								<p class="mb1rem">Количество записей на одной странице списка заказов</p>
								<x-input
									type="number"
									class="w7rem"
									group="large"
									label="строк"
									min="4"
									max="50"
									showrows
									setting="orders.per_page"
								/>
							</div>
						</div>
					</div>
					
				</div>
				{{--  --}}
				
			</div>
		</x-card>
	</x-settings>
</section>
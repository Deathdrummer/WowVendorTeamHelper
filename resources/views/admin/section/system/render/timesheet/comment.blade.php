<div class="ddrtabs">
	<div class="ddrtabs__nav fb15rem">
		<ul class="ddrtabsnav fz12px" ddrtabsnav>
			<li class="ddrtabsnav__item ddrtabsnav__item_active" ddrtabsitem="tsCommentsTab1">История</li>
			<li class="ddrtabsnav__item" ddrtabsitem="tsCommentsTab2">Редактировать</li>
		</ul>
	</div>
	
	<div class="ddrtabs__content ddrtabscontent pl10px" ddrtabscontent>
		<div class="ddrtabscontent__item ddrtabscontent__item_visible" ddrtabscontentitem="tsCommentsTab1">
			<div class="format breakword color-gray-600 fz14px scrollblock"style="max-height: calc(100vh - 150px)">{!!$buildComment ?? ''!!}</div>
		</div>
		<div class="ddrtabscontent__item" ddrtabscontentitem="tsCommentsTab2">
			<p class="color-gray-600 fz12px mb3px">Комментарий:</p>
			<x-textarea
				size="normal"
				name="comment"
				value="{{$rawComment ?? ''}}"
				id="timesheetComment"
				class="w100"
				rows="20"
				placeholder="Введите текст"
				noresize
				/>
		</div>
	</div>
</div>
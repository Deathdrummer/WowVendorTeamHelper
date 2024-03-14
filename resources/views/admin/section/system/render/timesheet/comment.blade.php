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
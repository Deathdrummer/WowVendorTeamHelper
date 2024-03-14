<?php namespace App\View\Components\inputs;

use App\Traits\HasComponent;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CheckLabel extends Component {
	use HasComponent;
    
	public $tag;
	
	/**
     * Create a new component instance.
     */
    public function __construct(
		?string $tag = null
	) {
		[$t, $tValue] = $this->buildTag($tag);
		$this->tag = $tValue ? $t.'="'.$tValue.'"' : $t;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render():View|Closure|string {
        return view('components.inputs.checklabel');
    }
}
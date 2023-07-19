<?php namespace App\View\Components\Inputs;

use App\Traits\HasComponent;
use Illuminate\View\Component;

class InputGroup extends Component {
    use HasComponent;
	
    public $groupWrap;
    public $nameGroup;
    
    
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
		?string $group = null,
		?string $size = null,
		?string $name = null
	) {
        $this->groupWrap = $group ?? $size;
        $this->nameGroup = $name;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render() {
		return <<<'blade'
			{{$slot}}
		blade;
    }
}
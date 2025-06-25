<?php

namespace App\View\Components;

use App\Models\Products;
use Illuminate\View\Component;

class productBox extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public $product;
    public function __construct(Products $product)
    {
        //
        $this->product=$product;
    }
    
   

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.product-box');
    }
}

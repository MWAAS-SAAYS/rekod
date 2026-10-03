<?php

declare(strict_types=1);

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

final class GuestLayout extends Component
{
    /**
     * Get the view / contents that represents the component.
     *
     * @return View|Closure|string
     */
    public function render(): View|Closure|string
    {
        return view('layouts.guest');
    }
}
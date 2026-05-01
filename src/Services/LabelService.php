<?php

namespace Aliziodev\Biteship\Services;

use Aliziodev\Biteship\DTOs\Label\Label;
use Illuminate\Http\Response;

class LabelService
{
    /**
     * Build Label DTO dari raw response array.
     */
    public function fromRaw(array $raw): Label
    {
        return Label::fromOrderResponse($raw);
    }

    /**
     * Render label sebagai HTML string.
     */
    public function render(Label $label): string
    {
        $view = config('biteship.label.view', 'biteship::label');

        return view($view, ['label' => $label])->render();
    }

    /**
     * Return HTTP response siap print di browser.
     */
    public function response(Label $label): Response
    {
        return response($this->render($label), 200)
            ->header('Content-Type', 'text/html; charset=UTF-8');
    }
}

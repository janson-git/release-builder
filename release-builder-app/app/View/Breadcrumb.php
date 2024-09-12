<?php

namespace App\View;

class Breadcrumb
{
    public string $title;
    public ?string $iconClass;
    public ?string $url;

    public function __construct(string $title, ?string $iconClass = null, ?string $url = null)
    {
        $this->title = $title;
        $this->iconClass = $iconClass;
        $this->url = $url;
    }
}

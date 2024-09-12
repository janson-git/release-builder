<?php

namespace App\View;

class Breadcrumbs
{
    /** @var array|Breadcrumb[] */
    private array $breadcrumbs = [];

    public function push(string $title, ?string $url = null, ?string $iconClass = null): self
    {
        $this->breadcrumbs[$url] = new Breadcrumb($title, $iconClass, $url);
        return $this;
    }

    public function items(): array
    {
        return $this->breadcrumbs;
    }
}

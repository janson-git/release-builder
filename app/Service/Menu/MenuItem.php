<?php

namespace Service\Menu;

use Admin\App;

class MenuItem
{
    public string $route;
    public string $title;
    public array $matchPatterns;
    public string $iconClass;

    public function __construct(string $title, string $route, array $matchPatterns = [])
    {
        $this->title = $title;
        $this->route = $route;
        $this->matchPatterns = $matchPatterns;
    }

    public function setIconClass(string $iconClass): void
    {
        $this->iconClass = $iconClass;
    }

    public function isSelected(): bool
    {
        $isSelected = false;
        $currentPath = App::getInstance()->getRequest()->getUri()->getPath();
        if ($currentPath === $this->route) {
            return true;
        }

        foreach ($this->matchPatterns as $pattern) {
            if ($currentPath === $pattern) {
                $isSelected = true;
                break;
            }

            // check pattern as regex
            if (strlen($pattern) < 3 || substr($pattern, 0, 1) !== substr($pattern, -1)) {
                // this is not regex string. Skip it!
                continue;
            }
            if (preg_match($pattern, $currentPath)) {
                $isSelected = true;
                break;
            }
        }

        return $isSelected;
    }
}
<?php

namespace Admin;

use eftec\bladeone\BladeOne;
use Psr\Container\ContainerInterface;
use Service\Breadcrumbs\Breadcrumb;
use Service\Menu\MenuItem;
use Slim\Http\Response;

class View
{
    /** @var \Admin\App */
    protected $app;

    /** @var BladeOne */
    protected $blade;

    /** @var array|Breadcrumb[] */
    protected $breadcrumbs = [];

    /** @var string */
    protected $templatesDir;

    protected $data = [];

    public function __construct(ContainerInterface $container)
    {
        $this->data['header'] = null;
        $this->data['title'] = null;

        $this->blade = $container->get('blade');

        $this->app = App::getInstance();
    }
    
    protected function loadMenu(): void
    {
        $menu = [];

        $projectsItem = new MenuItem(__('menu.projects'), '/projects', [
            '#/projects#',
            '#/packs#',
            '#/branches/add#',
            '#/branches/remove#',
            '#/branches/fork-pack#',
            '#/branches/create-pack#',
        ]);
        $projectsItem->setIconClass('fa-solid fa-folder-tree');
        $menu[] = $projectsItem;

        if (env('ENABLE_DEPLOY')) {
            $serversItem = new MenuItem(__('menu.servers'), '/web/slot');
            $serversItem->setIconClass('fa-solid fa-server');
            $menu[] = $serversItem;
        }
        if (env('ENABLE_EDIT_CONFIGURATIONS')) {
            $configItem = new MenuItem(__('menu.configurations'), '/config', [
                '#/config#'
            ]);
            $configItem->setIconClass('fa-solid fa-gears');
            $menu[] = $configItem;
        }

        $gitItem = new MenuItem(__('menu.git'), '/git', [
            '/git',
            '/git/add-repository',
        ]);
        $gitItem->setIconClass('fa-solid fa-code-branch');
        $menu[] = $gitItem;

        if ($this->app->getAuth()->isAuthenticated()) {
            $itemProfile = new MenuItem('Profile', '/user', [
                '/',
                '#/user#',
            ]);
            $itemProfile->setIconClass('fa-solid fa-user');

            $itemLogout = new MenuItem(__('logout'), '/auth/logout');
            $itemLogout->setIconClass('fa-solid fa-right-from-bracket');

            array_unshift($menu, $itemProfile);
            array_push($menu, $itemLogout);
        } else {
            $itemLogin = new MenuItem(__('login'), '/auth/login');
            $itemLogin->setIconClass('fa-solid fa-right-to-bracket');
            array_push($menu, $itemLogin);
        }
        
        $this->data['mainMenu'] = $menu;
    }
    
    public function setHeader($text): self
    {
        $this->data['header'] = $text;

        return $this;
    }
    
    public function setTitle($text): self
    {
        $this->data['title'] = $text;

        return $this;
    }

    public function render($template, $data = null): Response
    {
        $container = $this->app->getContainer();

        $this->data['view'] = $this;
        $this->data['_identify'] = $this->app->getIdentify();
        $this->data['user'] = $this->app->getAuth()->getUser();

        $this->loadMenu();

        if ($this->app->debug) {
            $data['_logs'] = $this->app->getLogger()->getLogs();
        }

        $data = array_merge($this->data, $data);

        $output = $this->blade->run($template, $data);

        /** @var \Slim\Http\Response Response $response */
        $response = $container->get('response');
        $response->write($output);

        return $response;
    }

    public function oldRender($template, $data = null)
    {
        $content = $this->subRender($template, $data);

        $layout = clone $this;
        $data['_identify'] = $this->app->getIdentify();
        $data['content'] = $content;
        $data['user'] = [
            'id' => $this->app->getAuth()->getUserLogin(),
            'url' => '/user',
        ];

        $this->loadMenu();
        $data['mainMenu'] = $this->data['mainMenu'];
        $data['breadcrumbs'] = $this->breadcrumbs;

        if ($this->app->debug) {
            $data['_logs'] = $this->app->getLogger()->getLogs();
        }

        echo $layout->subRender('layout/main', $data);
    }

    public function subRender($template, $data)
    {
        if (strrpos($template, '.php') !== 0) {
            $template .= '.php';
        }

        $templatePathname = $this->templatesDir . DIRECTORY_SEPARATOR . ltrim($template, DIRECTORY_SEPARATOR);
        if (!is_file($templatePathname)) {
            throw new \RuntimeException("View cannot render `$template` because the template does not exist");
        }

        $data = array_merge($this->data, (array) $data);
        $data['data'] = $data;

        extract($data);

        $output = '';
        try {
            ob_start();
            require $templatePathname;
        } finally {
            $output = ob_get_clean();
        }

        return $output;
    }
    
    public static function parse($data)
    {
        if (is_string($data) || is_numeric($data)) {
            return $data;
        }
        
        if (is_array($data)) {
            if (count($data) == 1 && isset($data[0])) {
                return self::parse(reset($data));
            }
            $res = [];
            
            $data = array_filter($data);
            
            $assoc = array_keys($data) !== range(0, count($data) - 1);
            foreach ($data as $k => $item) {
                $res [] = ($assoc ? "<b>{$k}</b>: " : '') . self::parse($item);
            }
            
            return $res ? implode('<br />', $res) : '';
        }
        
        if (is_object($data)) {
            return 'Object: ' . get_class($data) . self::parse(get_object_vars($data));
        }
        
        if (is_bool($data)) {
            return $data ? "Success" : "Fail"; 
        }
        
        return 'Closure';
    }

    public function addBreadcrumb(Breadcrumb $breadcrumb): self
    {
        $this->breadcrumbs[] = $breadcrumb;

        return $this;
    }

    public function hasBreadcrumbs(): bool
    {
        return count($this->breadcrumbs) > 0;
    }

    public function getBreadcrumbs(): array
    {
        return $this->breadcrumbs;
    }

    // FOR OLD CONTROLLERS COMPATIBLE
    public function setTemplatesDirectory($dir)
    {
        $this->templatesDir = $dir;
    }
}

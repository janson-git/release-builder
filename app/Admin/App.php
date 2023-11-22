<?php

namespace Admin;

use Service\Log\Logger;
use Slim\App as SlimApp;
use Slim\Exception\SlimException;
use Slim\Http\Response;

/**
 * Class App
 * @package Admin
 */
class App extends SlimApp
{
    private static $instance;

    public $itemId;
    
    public $debug = true;
    
    private $identify;

    /** @var array */
    protected $lang = [];
       
    const DATA_PROJECTS      = 'projects';
    const DATA_PACKS         = 'packs';
    const DATA_PROJECT_NAMES = 'project_names';
    const DATA_PACK_NAMES    = 'pack_names';
    const DATA_PACK_BUILDS   = 'pack_builds';
    const DATA_SLOTS         = 'slots';
    const DATA_USERS         = 'users';
    const DATA_SESSIONS      = 'sessions';

    /**
     * @return null|\Admin\App|\Slim\App
     */
    public static function i () 
    {
        return self::getInstance();
    }

    public static function getInstance(): self
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct($container = [])
    {
        parent::__construct($container);

        // FIXME: just a hack to make old flow works
        self::$instance = $this;
    }

    public function loadRoutes()
    {
        $routesPath = ROOT_DIR . '/routes.php';
        if (!is_file($routesPath)) {
            throw new \Exception('Routes could not be loaded! Check is routes file exists!');
        }

        $app = $this;
        require $routesPath;
    }

    public function view(): \Admin\View
    {
        return $this->getContainer()->get('view');
    }

    public function getRequest(): \Slim\Http\Request
    {
        return $this->getContainer()->get('request');
    }

    public function getCookiesPipe(): \Service\Util\CookiesPipe
    {
        return $this->getContainer()->get('cookies');
    }

    public function getResponse(): \Slim\Http\Response
    {
        return $this->getContainer()->get('response');
    }

    public function getAuth(): \User\Auth
    {
        return $this->getContainer()->get('auth');
    }

    public function getLogger(): Logger
    {
        return $this->getContainer()->get('logger');
    }

    /**
     * @param string $url
     * @return void
     * @throws SlimException
     */
    public function stopAndRedirectTo(string $url): void
    {
        $this->stopAndRedirectWithResponse($url, $this->getResponse());
    }

    /**
     * @param string $url
     * @param Response $response
     * @return void
     * @throws SlimException
     */
    public function stopAndRedirectWithResponse(string $url, Response $response): void
    {
        throw new SlimException(
            $this->getRequest(),
            $response->withRedirect($url)
        );
    }

    public function json(array $dataArray, $code = 200): Response
    {
        /** @var \Slim\Http\Response $response */
        $response = $this->getContainer()->get('response');
        return $response->withHeader('Content-Type', 'application/json')
            ->withStatus($code)
            ->write(json_encode($dataArray));
    }
    
    private $directory;
    
    /**
     * @return Directory
     */
    public function directory()
    {
        if (!$this->directory) {
            $this->directory = new Directory();
            $this->directory->setSitesDir(REPOS_DIR . '/');
        }
        
        return $this->directory;
    }
    
    public function log ($info, $form = null, $startTime = null)
    {
        if ($startTime) {
            $form .= ' ('.round(microtime(true) - $startTime,4).')';
        }
        
        $this->getLogger()->debug($info, [$form]);
    }
    
    public function doRoute ($module = 'web', $controller = 'user', $action = 'index', $id = 0)
    {
        $module     = ucfirst($module);
        $controller = ucfirst($controller);
        
        $this->itemId = $id;
        if (is_numeric($action) && $action) {
            $this->itemId = $action;
            $action = 'index';
        }

        $class = "\\Interaction\\{$module}\\Controller\\{$controller}";
        if (!class_exists($class)) {
            $class .= 'Controller';
            if (!class_exists($class)) {
                $this->notFound();
            }
        }
        
        $this->view()->setTemplatesDirectory("app/Interaction/{$module}/Templates");
        
        /* @var $controllerModel \Interaction\Base\Controller\ControllerProto */
        $controllerModel = new $class;
        $controllerModel->setApp($this);
        $controllerModel->setController(lcfirst($controller));
        $controllerModel->setAction($action);
        
        $this->log('Routing to: '.get_class($controllerModel).'->'.$action.'()', __METHOD__);
        
        return $controllerModel->run();
    }
    
    public function getIdentify () 
    {
        if (!$this->identify) {
            $this->identify = @gethostname(); 
        }
        
        return $this->identify;
    }

    public function getLangStringForKey($key, $lang = 'en')
    {
        if (!array_key_exists($lang, $this->lang)) {
            $langFile = ROOT_DIR . "/lang/{$lang}.php";
            if (!file_exists($langFile) || !is_readable($langFile)) {
                throw new \Exception("Lang file not exists or not readable for '{$lang}' language");
            }

            $this->lang[$lang] = require_once $langFile;
        }

        return $this->lang[$lang][$key] ?? null;
    }

    /**
     * Stop application correctly, write logs
     */
    public function terminate(): void
    {
        file_put_contents(
            LOGS_DIR . '/app.log',
            implode("\n", $this->getLogger()->getLogs()),
            FILE_APPEND
        );
    }
}

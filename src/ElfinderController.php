<?php namespace Barryvdh\Elfinder;

use Illuminate\Http\Request;
use League\Flysystem\Filesystem;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\File;
// use Illuminate\Support\Facades\Request;
use Illuminate\Foundation\Application;
use League\Flysystem\Cached\CachedAdapter;
use League\Flysystem\Cached\Storage\Memory;
use Illuminate\Filesystem\FilesystemAdapter;
use Barryvdh\Elfinder\Session\LaravelSession;

class ElfinderController extends Controller
{
    protected $package = 'elfinder';

    /**
     * The application instance.
     *
     * @var \Illuminate\Foundation\Application
     */
    protected $app;

    public $rootDir;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function showIndex(Request $request)
    {
        $this->rootDir = request('rootDir');

        return $this->app['view']
            ->make($this->package . '::elfinder')
            ->with($this->getViewVars());
    }

    public function showTinyMCE()
    {
        return $this->app['view']
            ->make($this->package . '::tinymce')
            ->with($this->getViewVars());
    }

    public function showTinyMCE4()
    {
        return $this->app['view']
            ->make($this->package . '::tinymce4')
            ->with($this->getViewVars());
    }

    public function showTinyMCE5()
    {
        return $this->app['view']
            ->make($this->package . '::tinymce5')
            ->with($this->getViewVars());
    }

    public function showCKeditor4()
    {
        return $this->app['view']
            ->make($this->package . '::ckeditor4')
            ->with($this->getViewVars());
    }

    public function showPopup($input_id)
    {
        return $this->app['view']
            ->make($this->package . '::standalonepopup')
            ->with($this->getViewVars())
            ->with(compact('input_id'));
    }

    public function showFilePicker($input_id)
    {
        $type = Request::input('type');
        $mimeTypes = implode(',', array_map(function ($t) {
            return "'".$t."'";
        }, explode(',', $type)));
        return $this->app['view']
            ->make($this->package . '::filepicker')
            ->with($this->getViewVars())
            ->with(compact('input_id', 'type', 'mimeTypes'));
    }

    public function showConnector(Request $request)
    {
        $roots = $this->app->config->get('elfinder.roots', []);

        if (empty($roots)) {
            $rootDir = request('rootDir');
            if (!$rootDir) {
                $dirs = (array) $this->app['config']->get('elfinder.dir', []);
            } else {
                $dirs[] = $rootDir;
                $path = public_path($rootDir);
                if (!File::exists($path)) {
                    File::makeDirectory($path, $mode = 0777, true, true);
                }
            }
            foreach ($dirs as $dir) {
                $roots[] = [
                    'driver' => 'LocalFileSystem', // driver for accessing file system (REQUIRED)
                    'path' => public_path($dir), // path to files (REQUIRED)
                    'URL' => url($dir), // URL to files (REQUIRED)
                    'accessControl' => $this->app->config->get('elfinder.access') // filter callback (OPTIONAL)
                ];
            }

            $disks = (array) $this->app['config']->get('elfinder.disks', []);
            foreach ($disks as $key => $root) {
                if (is_string($root)) {
                    $key = $root;
                    $root = [];
                }
                $disk = app('filesystem')->disk($key);
                if ($disk instanceof FilesystemAdapter) {
                    $defaults = [
                        'driver' => 'Flysystem',
                        'filesystem' => $disk->getDriver(),
                        'alias' => $key,
                    ];
                    $roots[] = array_merge($defaults, $root);
                }
            }
        }

        if (app()->bound('session.store')) {
            $sessionStore = app('session.store');
            $session = new LaravelSession($sessionStore);
        } else {
            $session = null;
        }

        $rootOptions = $this->app->config->get('elfinder.root_options', array());
        foreach ($roots as $key => $root) {
            $roots[$key] = array_merge($rootOptions, $root);
        }

        $opts = $this->app->config->get('elfinder.options', array());
        $opts = array_merge($opts, ['roots' => $roots, 'session' => $session]);

        // run elFinder
        $connector = new Connector(new \elFinder($opts));
        $connector->run();
        return $connector->getResponse();
    }

    protected function getViewVars()
    {
        $dir = 'packages/kazak71/' . $this->package;
        $locale = str_replace("-", "_", $this->app->config->get('app.locale'));
        if (!file_exists($this->app['path.public'] . "/$dir/js/i18n/elfinder.$locale.js")) {
            $locale = false;
        }
        $csrf = true;

        $rootDir = $this->rootDir;
        return compact('dir', 'locale', 'csrf', 'rootDir');
    }
}

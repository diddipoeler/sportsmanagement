<?php
/**
 * Regression checks for Joomla 5/6 update installation, image deletion and random player IDs.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\Application {
    class AdministratorApplication
    {
        public array $userState = [];
        public array $messages = [];
        public bool $adminAllowed = false;
        public bool $deleteAllowed = false;

        public function getIdentity(): object
        {
            return new class($this) {
                public int $id = 99;

                public function __construct(private AdministratorApplication $app) {}

                public function authorise(string $action, string $asset): bool
                {
                    return $asset === 'com_sportsmanagement'
                        && (($action === 'core.admin' && $this->app->adminAllowed)
                        || ($action === 'core.delete' && $this->app->deleteAllowed));
                }
            };
        }

        public function setUserState(string $name, mixed $value): void
        {
            $this->userState[$name] = $value;
        }

        public function enqueueMessage(string $message, string $type = 'message'): void
        {
            $this->messages[] = [$message, $type];
        }

        public function get(string $name): string
        {
            return $name === 'tmp_path' ? sys_get_temp_dir() : '';
        }
    }
}

namespace Joomla\CMS\MVC\Controller {
    class BaseController
    {
        protected $app;
        protected $input;
        public mixed $testModel = false;
        public bool $tokenValid = true;
        public array $redirect = [];
        public int $modelLookups = 0;

        public function __construct($config = [], $factory = null, $app = null, $input = null)
        {
            $this->app = $app;
            $this->input = $input;
        }

        public function checkToken(): void
        {
            if (!$this->tokenValid) {
                throw new \RuntimeException('Missing administrator form token.', 403);
            }
        }

        public function getModel($name = '', $prefix = '', $config = [])
        {
            ++$this->modelLookups;
            return $this->testModel;
        }

        public function setRedirect(string $url, string $message = '', string $type = 'message'): void
        {
            $this->redirect = compact('url', 'message', 'type');
        }
    }
}

namespace Joomla\CMS\MVC\Model {
    class BaseDatabaseModel {}
}

namespace Joomla\CMS\Component {
    class ComponentHelper
    {
        public static function getParams(string $name): object
        {
            return new class {
                public function get(string $name, mixed $default = null): string
                {
                    return 'https://github.com/diddipoeler/sportsmanagement/archive/refs/heads/joomla5-6.zip';
                }
            };
        }
    }
}

namespace Joomla\CMS\Language {
    class Text
    {
        public static function _(string $key): string
        {
            return $key;
        }
    }
}

namespace Joomla\CMS\Router {
    class Route
    {
        public static function _(string $url, bool $xhtml = true): string
        {
            return $url;
        }
    }
}

namespace Joomla\CMS\Session {
    class Session
    {
        public static string $lastMethod = '';
        public static bool $valid = true;

        public static function checkToken(string $method = 'post'): bool
        {
            self::$lastMethod = $method;
            return self::$valid;
        }
    }
}

namespace Joomla\CMS\Factory {
}

namespace Joomla\CMS {
    class Factory
    {
        public static ?object $app = null;

        public static function getContainer(): object
        {
            return new class {
                public function get(string $name): object
                {
                    return \Joomla\CMS\Factory::$app;
                }
            };
        }
    }
}

namespace Joomla\CMS\Installer {
    class InstallerHelper
    {
        public static function downloadPackage(string $url): string
        {
            return 'sportsmanagement-security-test.zip';
        }
    }
}

namespace Joomla\Archive {
    class Archive
    {
        public function __construct(array $options = []) {}

        public function extract(string $file, string $destination): bool
        {
            return true;
        }
    }
}

namespace Joomla\Filesystem {
    class File
    {
        public static function exists(string $path): bool
        {
            return true;
        }
    }

    class Folder
    {
        public static function exists(string $path): bool
        {
            return false;
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Helper {
    class ImageSelectHelper
    {
        public static function getFolder(string $type): string
        {
            // Prevent filesystem changes while exercising the POST handler.
            return '';
        }
    }
}

namespace {
    use Diddipoeler\Component\SportsManagement\Administrator\Controller\GithubinstallController;
    use Diddipoeler\Component\SportsManagement\Administrator\Model\GithubinstallModel;
    use Diddipoeler\Component\SportsManagement\Site\Controller\ImagehandlerController;
    use Diddipoeler\Module\SportsManagementRandomPlayer\Site\Helper\RandomPlayerHelper;
    use Joomla\CMS\Application\AdministratorApplication;
    use Joomla\CMS\Factory;
    use Joomla\CMS\Session\Session;

    \define('_JEXEC', 1);
    \define('JPATH_SITE', '/nonexistent-joomla-site');

    $root = dirname(__DIR__, 2);
    require_once $root . '/admin/src/Controller/GithubinstallController.php';
    require_once $root . '/admin/src/Model/GithubinstallModel.php';
    require_once $root . '/site/src/Controller/ImagehandlerController.php';
    require_once $root . '/modules/mod_sportsmanagement_randomplayer/src/Helper/RandomPlayerHelper.php';

    function check(bool $condition, string $message): void
    {
        if (!$condition) {
            throw new \RuntimeException($message);
        }
    }

    $app = new AdministratorApplication();
    Factory::$app = $app;

    $model = new class {
        public int $downloads = 0;
        public int $installs = 0;
        public function CopyGithubLink(string $url): array
        {
            ++$this->downloads;
            return [];
        }
        public function installfolder(): void
        {
            ++$this->installs;
        }
    };

    $controller = new GithubinstallController([], null, $app);
    $controller->testModel = $model;

    try {
        $controller->CopyGithubLink();
        throw new \RuntimeException('GitHub package installation bypassed administrator authorization.');
    } catch (\RuntimeException $e) {
        check($e->getCode() === 403, 'Unauthorized update must return HTTP 403.');
    }
    check($model->downloads === 0 && $model->installs === 0 && $controller->modelLookups === 0,
        'Unauthorized requests must never download packages or resolve installation models.');

    $app->adminAllowed = true;
    $controller->tokenValid = false;
    try {
        $controller->CopyGithubLink();
        throw new \RuntimeException('GitHub package installation accepted a missing form token.');
    } catch (\RuntimeException $e) {
        check($e->getCode() === 403, 'Missing update token must return HTTP 403.');
    }
    check($model->downloads === 0 && $model->installs === 0,
        'Invalid CSRF requests must never download or install packages.');

    $controller->tokenValid = true;
    $controller->CopyGithubLink();
    check($model->downloads === 1 && $model->installs === 1,
        'Authorized GitHub update requests must preserve the installation workflow.');

    $githubModel = new GithubinstallModel();
    $app->userState['com_sportsmanagement.github_update_dir'] = '/stale/extraction';
    check($githubModel->CopyGithubLink('https://github.com/example/repo/archive.zip') === false,
        'An update with no extracted folder must fail.');
    check(array_key_exists('com_sportsmanagement.github_update_dir', $app->userState)
        && $app->userState['com_sportsmanagement.github_update_dir'] === null,
        'Failed updates must clear the previous extracted directory.');
    check(($app->messages[0][1] ?? '') === 'error',
        'Missing update directory must produce an error message.');

    $input = new class {
        public object $post;
        public function __construct()
        {
            $this->post = new class {
                public array $requested = [];
                public function get(string $name, mixed $default = null, string $filter = ''): mixed
                {
                    $this->requested[] = $name;
                    return $name === 'rm' ? ['image.png'] : $default;
                }
            };
        }
        public function getCmd(string $name, string $default = ''): string
        {
            return $name === 'type' ? 'team' : $default;
        }
        public function get(string $name, mixed $default = null, string $filter = ''): mixed
        {
            throw new \RuntimeException('Image deletion unexpectedly read values from GET.');
        }
    };

    $imageController = new ImagehandlerController([], null, $app, $input);
    Session::$valid = false;
    try {
        $imageController->delete();
        throw new \RuntimeException('Image deletion accepted a missing POST CSRF token.');
    } catch (\RuntimeException $e) {
        check($e->getCode() === 403, 'Invalid image deletion token must return HTTP 403.');
    }
    check(Session::$lastMethod === 'post', 'Image deletion must check a POST token.');

    Session::$valid = true;
    $app->deleteAllowed = false;
    try {
        $imageController->delete();
        throw new \RuntimeException('Image deletion accepted a user without delete rights.');
    } catch (\RuntimeException $e) {
        check($e->getCode() === 403, 'Denied image deletion must return HTTP 403.');
    }

    $app->deleteAllowed = true;
    $imageController->delete();
    check(in_array('rm', $input->post->requested, true),
        'Image deletion must read the selected files from POST.');
    check(str_contains($imageController->redirect['url'] ?? '', 'view=imagehandler'),
        'Image deletion must return to the component image selector.');

    $form = file_get_contents($root . '/site/views/imagehandler/tmpl/default.php');
    $tile = file_get_contents($root . '/site/views/imagehandler/tmpl/default_image.php');
    check($form !== false && str_contains($form, 'name="type"')
        && str_contains($form, "HTMLHelper::_('form.token')"),
        'Image selector POST form must include image type and CSRF token.');
    check($tile !== false && str_contains($tile, '<button type="submit"')
        && str_contains($tile, 'name="rm[]"')
        && str_contains($tile, "this.form.elements['task'].value='imagehandler.delete';")
        && !str_contains($tile, '$deleteUrl'),
        'Image selector must submit deletion as POST, not a GET URL.');

    $randomPlayer = new RandomPlayerHelper();
    $ids = new \ReflectionMethod($randomPlayer, 'normaliseIds');
    check($ids->invoke($randomPlayer, '3,003:team 5;7;999999999999999999999999999999;8broken') === [3, 5, 7],
        'RandomPlayer must preserve valid IDs and discard overflow/partial matches.');
    check($ids->invoke($randomPlayer, [true, false, 2.5, ['2'], null]) === [],
        'RandomPlayer must reject boolean, floating-point and nested array IDs.');
    check($ids->invoke($randomPlayer, [12, '12:alias', '13']) === [12, 13],
        'RandomPlayer must deduplicate valid Joomla multiselect IDs.');

    echo "Joomla 5/6 update, image deletion and RandomPlayer security regressions OK.\n";
}

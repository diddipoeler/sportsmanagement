<?php
/**
 * Joomla 5/6 frontend image manager and clubicons security regressions.
 *
 * Usage: php .github/tests/joomla56_image_manager_clubicons_acl.php
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\MVC\Controller {
    class BaseController
    {
        public object $app;
        public object $input;
    }
}

namespace Joomla\CMS\Language {
    class Text
    {
        public static function _(string $key): string { return $key; }
    }
}

namespace Joomla\CMS\Session {
    final class Session
    {
        public static bool $valid = true;
        public static function checkToken(string $method): bool { return self::$valid; }
    }
}

namespace {
    define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);

    require_once $root . '/site/src/Controller/ImagehandlerController.php';
    require_once $root . '/modules/mod_sportsmanagement_clubicons/src/Helper/ClubiconsHelper.php';

    final class FakeIdentity
    {
        public function __construct(public int $id = 0, public array $actions = []) {}
        public function authorise(string $action, string $asset): bool
        {
            if ($asset !== 'com_sportsmanagement') {
                throw new \RuntimeException('Image ACL checked an unexpected component.');
            }
            return (bool) ($this->actions[$action] ?? false);
        }
    }

    final class FakeApplication
    {
        public function __construct(public ?FakeIdentity $identity) {}
        public function getIdentity(): ?FakeIdentity { return $this->identity; }
    }

    $controller = new \Diddipoeler\Component\SportsManagement\Site\Controller\ImagehandlerController();
    $permissions = new \ReflectionMethod($controller, 'assertImagePermission');
    $assertRejected = static function (string $action) use ($controller, $permissions): void {
        try {
            $permissions->invoke($controller, $action);
        } catch (\RuntimeException $error) {
            if ($error->getCode() === 403) {
                return;
            }
            throw $error;
        }

        throw new \RuntimeException('Image manager accepted an unauthorized ' . $action);
    };

    $controller->app = new FakeApplication(null);
    $assertRejected('upload');
    $assertRejected('delete');
    $controller->app = new FakeApplication(new FakeIdentity(0, [
        'core.create' => true, 'core.edit' => true, 'core.delete' => true,
    ]));
    $assertRejected('upload');
    $assertRejected('delete');

    $identity = new FakeIdentity(17);
    $controller->app = new FakeApplication($identity);
    $assertRejected('upload');
    $assertRejected('delete');

    // A valid session token must not permit a write without component ACL.
    $controller->input = (object) [];
    foreach (['upload', 'delete'] as $task) {
        try {
            $controller->{$task}();
        } catch (\RuntimeException $error) {
            if ($error->getCode() === 403) {
                continue;
            }
            throw $error;
        }
        throw new \RuntimeException('Image manager ' . $task . ' did not enforce ACL.');
    }

    $identity->actions = ['core.create' => true];
    $permissions->invoke($controller, 'upload');
    $assertRejected('delete');

    $identity->actions = ['core.edit' => true];
    $permissions->invoke($controller, 'upload');
    $assertRejected('delete');

    $identity->actions = ['core.delete' => true];
    $assertRejected('upload');
    $permissions->invoke($controller, 'delete');

    \Joomla\CMS\Session\Session::$valid = false;
    foreach (['upload', 'delete'] as $task) {
        try {
            $controller->{$task}();
        } catch (\RuntimeException $error) {
            if ($error->getCode() === 403 && $error->getMessage() === 'JINVALID_TOKEN') {
                continue;
            }
            throw $error;
        }
        throw new \RuntimeException('Image manager ignored invalid CSRF token.');
    }

    $remoteUrl = new \ReflectionMethod($controller, 'validateRemoteImageUrl');
    $remoteCases = [
        ['https://1.1.1.1/team.png', true],
        ['http://8.8.8.8/team.jpg', true],
        ['https://1.1.1.1:443/team.jpg', true],
        ['http://127.0.0.1/team.png', false],
        ['http://10.10.0.2/image.png', false],
        ['http://172.16.1.1/image.png', false],
        ['http://192.168.1.1/image.png', false],
        ['http://169.254.169.254/latest/meta-data', false],
        ['http://100.100.100.100/image.png', false],
        ['http://198.18.0.1/image.png', false],
        ['http://[::1]/image.png', false],
        ['https://localhost/logo.png', false],
        ['https://service.internal/logo.png', false],
        ['https://domain.home.arpa/image.png', false],
        ['https://1.1.1.1:8443/image.png', false],
        ['http://1.1.1.1:8080/image.png', false],
        ['https://user:secret@1.1.1.1/image.png', false],
        ['file:///etc/passwd', false],
        ['https://1.1.1.1\\@127.0.0.1/image.png', false],
        ["https://1.1.1.1/\r\nInjected", false],
    ];

    foreach ($remoteCases as [$provided, $expected]) {
        if (($remoteUrl->invoke($controller, $provided) !== null) !== $expected) {
            throw new \RuntimeException('Image manager accepted an unsafe remote HTTP target: ' . $provided);
        }
    }

    $imageSource = file_get_contents($root . '/site/src/Controller/ImagehandlerController.php');
    if (!str_contains($imageSource, "['follow_location' => false]")
        || !str_contains($imageSource, "['Curl', 'Stream']")) {
        throw new \RuntimeException('Remote image HTTP client may follow redirects into private networks.');
    }

    $clubicons = new \Diddipoeler\Module\SportsManagementClubicons\Site\Helper\ClubiconsHelper();
    $firstId = new \ReflectionMethod($clubicons, 'firstId');

    foreach ([
        [5, 5], ['5', 5], ['5:team-alias', 5],
        [['5:team-alias', '8:other'], 5],
        ['5garbage', 0], ['5:alias:extra', 0], ['0', 0], [-1, 0],
        [[], 0], [[['5']], 0], [false, 0], [5.7, 0], [null, 0],
        ['99999999999999999999999999', 0],
    ] as [$value, $expected]) {
        if ($firstId->invoke($clubicons, $value) !== $expected) {
            throw new \RuntimeException('Clubicons parsed malformed team/project ID.');
        }
    }

    $url = new \ReflectionMethod($clubicons, 'externalWebsiteUrl');
    foreach ([
        ['https://sports.example/club', 'https://sports.example/club'],
        ['http://sports.example/', 'http://sports.example/'],
        [' https://sports.example/path?a=1 ', 'https://sports.example/path?a=1'],
        ['javascript:alert(1)', ''], ['data:text/html,unsafe', ''],
        ['//sports.example/club', ''], ['ftp://sports.example', ''],
        ["https://sports.example/\nInjected", ''],
        ['https://user:password@sports.example/', ''],
        ['https://user@sports.example/', ''],
        ['https://sports.example\\@evil.example/', ''],
        ['', ''],
    ] as [$provided, $expected]) {
        if ($url->invoke($clubicons, $provided) !== $expected) {
            throw new \RuntimeException('Clubicons accepted an unsafe external link: ' . $provided);
        }
    }

    $code = file_get_contents($root . '/modules/mod_sportsmanagement_clubicons/src/Helper/ClubiconsHelper.php');
    if (!str_contains($code, 'externalWebsiteUrl((string)')) {
        throw new \RuntimeException('Clubicons does not validate website links before displaying them.');
    }

    echo "Joomla 5/6 image ACL, clubicons IDs and safe website links OK\n";
}

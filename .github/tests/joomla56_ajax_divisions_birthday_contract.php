<?php
/**
 * Joomla 5/6 regression checks for site AJAX, division writes and birthday selectors.
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
        protected mixed $app = null;

        public function setTestApplication(object $app): void
        {
            $this->app = $app;
        }
    }

    class AdminController extends BaseController
    {
    }
}

namespace Joomla\CMS\Language {
    class Text
    {
        public static function _(string $key): string
        {
            return 'translated:' . $key;
        }
    }
}

namespace Joomla\CMS\Log {
    class Log
    {
        public const ERROR = 3;
        public static array $messages = [];

        public static function add(string $message, int $severity, string $category): void
        {
            self::$messages[] = compact('message', 'severity', 'category');
        }
    }
}

namespace {
    define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);

    require_once $root . '/site/src/Controller/AjaxController.php';

    $ajax = (new \ReflectionClass(
        \Diddipoeler\Component\SportsManagement\Site\Controller\AjaxController::class
    ))->newInstanceWithoutConstructor();

    $app = new class {
        public array $headers = [];
        public int $closes = 0;

        public function setHeader(string $name, string $value, bool $replace): void
        {
            $this->headers[$name] = $value;
        }

        public function close(): void
        {
            ++$this->closes;
        }
    };

    $ajax->setTestApplication($app);
    $formatError = new \ReflectionMethod($ajax, 'ajaxErrorMessage');
    $secret = 'SQL password=user_password should stay private';
    $errorStage = $formatError->invoke($ajax, 'fallback', new \RuntimeException($secret));

    if ($errorStage !== 'fallback'
        || count(\Joomla\CMS\Log\Log::$messages) !== 1
        || !str_contains(\Joomla\CMS\Log\Log::$messages[0]['message'], $secret)
        || \Joomla\CMS\Log\Log::$messages[0]['category'] !== 'jsmerror'
    ) {
        throw new \RuntimeException('Site AJAX must log internal failures on the server.');
    }

    ob_start();
    (new \ReflectionMethod($ajax, 'sendAjaxError'))->invoke($ajax, 'getcountryassoc', [$errorStage]);
    $json = ob_get_clean();
    $payload = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

    if ($payload['success'] !== false
        || $payload['message'] !== 'translated:JERROR_AN_ERROR_HAS_OCCURRED'
        || str_contains($json, $secret)
        || $app->closes !== 1
        || ($app->headers['Content-Type'] ?? '') !== 'application/json; charset=utf-8'
    ) {
        throw new \RuntimeException('Site AJAX error response must be generic UTF-8 JSON.');
    }

    $divisions = file_get_contents($root . '/admin/src/Controller/DivisionsController.php');

    if (!is_string($divisions)) {
        throw new \RuntimeException('Unable to read DivisionsController.');
    }

    foreach ([
        'massadd' => "'core.create'",
        'divisiontoproject' => "'core.create'",
        'saveOrder' => "'core.admin'",
        'saveshort' => '',
    ] as $action => $permission) {
        $pattern = '~function\s+' . preg_quote($action, '~') . '\(\):\s*void\s*\{\s*'
            . '\$this->assertWritePermission\(' . preg_quote($permission, '~') . '\);~';

        if (preg_match($pattern, $divisions) !== 1) {
            throw new \RuntimeException('Missing POST token or ACL guard in Divisions::' . $action);
        }
    }

    if (!str_contains($divisions, "\$input->post->get('cid', [], 'array')")
        || !str_contains($divisions, "\$input->post->get('order', [], 'array')")) {
        throw new \RuntimeException('Division sorting must read identifiers from POST.');
    }

    $predictionMembers = file_get_contents($root . '/admin/src/Controller/PredictionmembersController.php');

    foreach ([
        'save_memberlist' => '',
        'reminder' => '',
        'remove' => "'core.delete'",
        'setApprovalState' => "'core.edit.state'",
    ] as $action => $permission) {
        $signature = $action === 'setApprovalState'
            ? 'function\s+setApprovalState\(int\s+\$state\)'
            : 'function\s+' . preg_quote($action, '~') . '\(\)';

        $pattern = '~' . $signature . ':\s*void\s*\{\s*'
            . '\$this->assertWritePermission\(' . preg_quote($permission, '~') . '\);~';

        if (!is_string($predictionMembers) || preg_match($pattern, $predictionMembers) !== 1) {
            throw new \RuntimeException('Missing permission guard in Predictionmembers::' . $action);
        }
    }

    require_once $root . '/modules/mod_sportsmanagement_birthday/src/Helper/BirthdayHelper.php';

    $birthday = new \Diddipoeler\Module\SportsManagementBirthday\Site\Helper\BirthdayHelper();
    $normalize = new \ReflectionMethod($birthday, 'normaliseIds');
    $actual = $normalize->invoke(
        $birthday,
        ['1, 2;3', '3', '4trailing', '0', '-4', '9999999999999999999999999', [5], '6']
    );

    if ($actual !== [1, 2, 3, 6]) {
        throw new \RuntimeException('Birthday selectors accepted malformed database IDs: ' . json_encode($actual));
    }

    echo "Joomla 5/6 site AJAX, admin divisions and birthday selector checks: OK\n";
}

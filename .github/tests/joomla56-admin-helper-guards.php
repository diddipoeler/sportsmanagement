<?php
/**
 * Standalone Joomla 5/6 regression coverage for administrator helper guards.
 *
 * Usage: php .github/tests/joomla56-admin-helper-guards.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\CMS\Language {
    /** Translation stub needed to capture administrator action-log values. */
    final class Text
    {
        /**
         * Return the translation identifier unchanged in isolated regression tests.
         *
         * @param string $key Joomla language key.
         * @return string Original translation key.
         */
        public static function _(string $key): string { return $key; }
    }
}

namespace Joomla\Component\Actionlogs\Administrator\Model {
    /** Captures native Joomla action-log messages for assertions. */
    final class ActionlogModel
    {
        public static array $entries = [];

        /**
         * Capture one action-log request instead of using Joomla persistence.
         *
         * @param array<int,array<string,mixed>> $messages Action metadata rows.
         * @param string $messageLanguageKey Translation key for the action.
         * @param string $context Joomla extension/view action context.
         * @param int $userId Acting administrator identifier.
         * @return void
         */
        public function addLog(array $messages, string $messageLanguageKey, string $context, int $userId): void
        {
            self::$entries[] = compact('messages', 'messageLanguageKey', 'context', 'userId');
        }
    }
}

namespace {
    \defined('_JEXEC') || \define('_JEXEC', 1);

    require_once dirname(__DIR__, 2) . '/admin/src/Helper/ActionLogHelper.php';
    require_once dirname(__DIR__, 2) . '/admin/src/Helper/ExtensionLanguageHelper.php';

    use Diddipoeler\Component\SportsManagement\Administrator\Helper\ActionLogHelper;
    use Diddipoeler\Component\SportsManagement\Administrator\Helper\ExtensionLanguageHelper;
    use Joomla\Component\Actionlogs\Administrator\Model\ActionlogModel;

    $user = (object) ['id' => 17, 'username' => 'administrator'];

    ActionLogHelper::record($user, ['id' => 0, 'name' => 'Missing'], true, 'club');
    ActionLogHelper::record((object) ['id' => 0, 'username' => 'unknown'], ['id' => 15], false, 'club');

    if (ActionlogModel::$entries !== []) {
        throw new \RuntimeException('Unsaved records or unauthenticated users must not be action-logged.');
    }

    ActionLogHelper::record($user, ['id' => 42, 'name' => 'Sports Club'], true, 'club');

    if (count(ActionlogModel::$entries) !== 1
        || ActionlogModel::$entries[0]['userId'] !== 17
        || ActionlogModel::$entries[0]['messages'][0]['id'] !== 42) {
        throw new \RuntimeException('Expected a valid administrator action-log entry.');
    }

    foreach (['', '../club', 'club/path', 'club?task=delete', str_repeat('x', 101)] as $view) {
        if (ExtensionLanguageHelper::forView($view) !== []) {
            throw new \RuntimeException('Unsafe extension language path accepted: ' . $view);
        }
    }

    echo "Joomla 5/6 administrator helper guards passed.\n";
}

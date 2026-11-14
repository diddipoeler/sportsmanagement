<?php
/**
 * Regression coverage for Joomla 5/6 club history, administrator logging and module options.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\CMS\HTML {
    final class HTMLHelper
    {
        /**
         * Provide predictable markup for the club-history image fixture.
         *
         * @param string $path Image location.
         * @param string $alt Alternative text.
         * @param array $attributes Optional HTML attributes.
         * @return string Test image element.
         */
        public static function image(string $path, string $alt = '', array $attributes = []): string
        {
            return '<img src="' . htmlspecialchars($path, ENT_QUOTES, 'UTF-8') . '">';
        }

        /**
         * Render a minimal anchor so nested list structure can be asserted.
         *
         * @param string $url Encoded link destination.
         * @param string $text Already escaped visible label.
         * @return string Predictable test anchor.
         */
        public static function link(string $url, string $text): string
        {
            return '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '">' . $text . '</a>';
        }
    }
}

namespace Joomla\CMS\Language {
    final class Text
    {
        /**
         * Keep translation keys stable for framework-independent tests.
         *
         * @param string $key Language key requested by production code.
         * @return string Untranslated key for easy markup comparison.
         */
        public static function _(string $key): string
        {
            return $key;
        }
    }
}

namespace Joomla\Component\Actionlogs\Administrator\Model {
    final class ActionlogModel
    {
        public static array $calls = [];

        /**
         * Capture an action-log payload without requiring Joomla or a database.
         *
         * @param array $messages Logged administrator entity information.
         * @param string $text Log description.
         * @param string $context Action-log extension context.
         * @param int $userId Authenticated actor identifier.
         * @return void
         */
        public function addLog(array $messages, string $text, string $context, int $userId): void
        {
            self::$calls[] = ['messages' => $messages, 'userId' => $userId];
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Helper {
    final class SiteRouteHelper
    {
        /**
         * Replace Joomla's router with a predictable component link.
         *
         * @param string $view Native component view.
         * @param array $params Route selector parameters.
         * @return string Deterministic fake route.
         */
        public static function view(string $view, array $params = []): string
        {
            return 'index.php?view=' . rawurlencode($view);
        }
    }
}

namespace {
    define('_JEXEC', 1);

    require dirname(__DIR__, 2) . '/site/src/Helper/ClubHistoryPresentationHelper.php';
    require dirname(__DIR__, 2) . '/admin/src/Helper/ActionLogHelper.php';
    require dirname(__DIR__, 2) . '/modules/mod_sportsmanagement_navigation_menu/src/Helper/NavigationMenuHelper.php';

    $relations = [
        (object) ['id' => 2, 'new_club_id' => 1, 'name' => 'Former A'],
        (object) ['id' => 3, 'new_club_id' => 2, 'name' => 'Former B'],
        (object) ['id' => 4, 'new_club_id' => 3, 'name' => '<unsafe>'],
    ];
    $history = \Diddipoeler\Component\SportsManagement\Site\Helper\ClubHistoryPresentationHelper::renderSuccessorHistory(
        $relations, 1, (object) ['id' => 1, 'name' => 'Current'], 0
    );

    if (!str_contains($history, '</a><ul><li>')
        || !str_contains($history, '</li></ul></li>')
        || !str_contains($history, '&lt;unsafe&gt;')
        || str_contains($history, '<unsafe>')) {
        throw new \RuntimeException('Club history must nest descendant lists inside parent items and escape names.');
    }

    $relations[] = (object) ['id' => 1, 'new_club_id' => 4, 'name' => 'Cycle'];
    $tree = \Diddipoeler\Component\SportsManagement\Site\Helper\ClubHistoryPresentationHelper::renderPredecessorTree(
        $relations, 1, 1, 0
    );

    if (substr_count($tree, '<li>') !== 3) {
        throw new \RuntimeException('Cyclic club history should not re-enter a visited club.');
    }

    $actor = (object) ['id' => 12, 'username' => 'administrator'];
    $badIds = [[1], true, 2.5, '7garbage', '9223372036854775808', '-7', '0'];

    foreach ($badIds as $badId) {
        \Diddipoeler\Component\SportsManagement\Administrator\Helper\ActionLogHelper::record(
            $actor, ['id' => $badId, 'name' => 'Club'], true, 'club'
        );
        \Diddipoeler\Component\SportsManagement\Administrator\Helper\ActionLogHelper::record(
            (object) ['id' => $badId], ['id' => 7, 'name' => 'Club'], true, 'club'
        );
    }

    if (\Joomla\Component\Actionlogs\Administrator\Model\ActionlogModel::$calls !== []) {
        throw new \RuntimeException('Malformed actor/entity identifiers must never be action-logged.');
    }

    \Diddipoeler\Component\SportsManagement\Administrator\Helper\ActionLogHelper::record(
        (object) ['id' => '00012', 'username' => 'administrator'],
        ['id' => '0007', 'name' => 'Club'], true, 'club'
    );

    $calls = \Joomla\Component\Actionlogs\Administrator\Model\ActionlogModel::$calls;
    if (count($calls) !== 1 || $calls[0]['userId'] !== 12
        || $calls[0]['messages'][0]['id'] !== 7) {
        throw new \RuntimeException('Valid numeric database IDs must remain usable for action logs.');
    }

    $helperClass = \Diddipoeler\Module\SportsManagementNavigationMenu\Site\Helper\NavigationMenuHelper::class;
    $itemMethod = new \ReflectionMethod($helperClass, 'normaliseMenuItemId');
    $viewMethod = new \ReflectionMethod($helperClass, 'normaliseDefaultView');

    foreach ([true, false, [], new \stdClass(), 2.5, '-3', '13oops', '9223372036854775808'] as $bad) {
        if ($itemMethod->invoke(null, $bad) !== 0) {
            throw new \RuntimeException('Malformed module menu IDs must fail closed to zero.');
        }
    }

    if ($itemMethod->invoke(null, '00024') !== 24
        || $itemMethod->invoke(null, 13) !== 13
        || $viewMethod->invoke(null, 'resultsrankingmatrix') !== 'resultsrankingmatrix') {
        throw new \RuntimeException('Valid module menu IDs and native views must be preserved.');
    }

    foreach ([[], new \stdClass(), ' ../results', 'ranking/../../', ''] as $bad) {
        if ($viewMethod->invoke(null, $bad) !== 'ranking') {
            throw new \RuntimeException('Malformed start views must return the ranking fallback.');
        }
    }

    echo "Club history nesting, action-log IDs and navigation module options: OK\n";
}

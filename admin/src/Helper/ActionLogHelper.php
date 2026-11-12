<?php
/**
 * Joomla 5/6 SportsManagement administrator helper.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Language\Text;
use Joomla\Component\Actionlogs\Administrator\Model\ActionlogModel;

/**
 * Joomla 5/6 action-log bridge for SportsManagement entity saves.
 */
final class ActionLogHelper
{
    /**
     * Resolve the active administrator application for logging entity changes.
     *
     * Uses the shared Joomla 5/6 dependency-injection resolver rather than
     * the removed global application helper.
     *
     * @return CMSApplicationInterface Active administrator application.
     */
    private static function administratorApplication(): CMSApplicationInterface
    {
        return SportsManagementAdministratorApplicationResolver::resolve();
    }

    /**
     * Record a SportsManagement entity creation or update in Joomla action logs.
     *
     * Derives an administrator view name and a concise entity label, then
     * sends the associated user, action and edit link to Joomla's native
     * ActionlogModel. Nonpositive IDs are ignored because they cannot refer
     * to a persisted entity or accountable Joomla user. Rejects malformed
     * view identifiers instead of transforming them into different tasks.
     * Ignores malformed non-scalar labels from imported legacy records rather
     * than producing PHP 8 array-to-string conversion warnings.
     *
     * @param object $user Acting Joomla user with id and username properties.
     * @param array<string,mixed> $transaction Saved entity row and identifier.
     * @param bool $isNew Whether the entity was newly created.
     * @param string|null $view Optional administrator entity view identifier.
     * @return void
     */
    public static function record(object $user, array $transaction, bool $isNew, ?string $view = null): void
    {
        $id = (int) ($transaction['id'] ?? 0);
        $userId = (int) ($user->id ?? 0);

        if ($id <= 0 || $userId <= 0) {
            return;
        }

        if ($view === null || $view === '') {
            $app = self::administratorApplication();

            $view = $app->getInput()->getCmd('view', 'cpanel');
        }

        // A log target must never silently change to another controller.
        if (!is_string($view) || $view === '' || strlen($view) > 100
            || preg_match('/^[A-Za-z][A-Za-z0-9_-]*$/D', $view) !== 1) {
            return;
        }

        $view = strtolower($view);
        $type = Text::_($isNew ? 'JTOOLBAR_NEW' : 'JLIB_INSTALLER_UPDATE');

        if ($view === 'player') {
            $first = $transaction['firstname'] ?? '';
            $last = $transaction['lastname'] ?? '';
            $label = trim(
                (is_scalar($first) ? (string) $first : '')
                . ' '
                . (is_scalar($last) ? (string) $last : '')
            );
        } else {
            $name = $transaction['name'] ?? '';
            $label = is_scalar($name) ? trim((string) $name) : '';
        }

        $message = [
            'action' => $view,
            'type' => trim($type . ' ' . $label),
            'id' => $id,
            'title' => 'com_sportsmanagement',
            'extension_name' => 'com_sportsmanagement',
            'itemlink' => 'index.php?option=com_sportsmanagement&task=' . $view . '.edit&id=' . $id,
            'userid' => $userId,
            'username' => is_scalar($user->username ?? null) ? (string) $user->username : '',
            'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $userId,
        ];

        (new ActionlogModel())->addLog(
            [$message],
            Text::_('COM_SPORTSMANAGEMENT_TRANSACTION_LINK'),
            'com_sportsmanagement.' . $view,
            $userId
        );
    }
}

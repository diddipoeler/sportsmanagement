<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Component\Actionlogs\Administrator\Model\ActionlogModel;

/**
 * Joomla 5/6 action-log bridge for SportsManagement entity saves.
 */
final class ActionLogHelper
{
    public static function record(object $user, array $transaction, bool $isNew): void
    {
        $view = Factory::getApplication()->getInput()->getCmd('view', 'cpanel');
        $id = (int) ($transaction['id'] ?? 0);
        $type = Text::_($isNew ? 'JTOOLBAR_NEW' : 'JLIB_INSTALLER_UPDATE');

        if ($view === 'player') {
            $label = trim(
                (string) ($transaction['firstname'] ?? '')
                . ' '
                . (string) ($transaction['lastname'] ?? '')
            );
        } else {
            $label = trim((string) ($transaction['name'] ?? ''));
        }

        $message = [
            'action' => $view,
            'type' => trim($type . ' ' . $label),
            'id' => $id,
            'title' => 'com_sportsmanagement',
            'extension_name' => 'com_sportsmanagement',
            'itemlink' => 'index.php?option=com_sportsmanagement&task=' . $view . '.edit&id=' . $id,
            'userid' => (int) $user->id,
            'username' => (string) $user->username,
            'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . (int) $user->id,
        ];

        (new ActionlogModel())->addLog(
            [$message],
            Text::_('COM_SPORTSMANAGEMENT_TRANSACTION_LINK'),
            'com_sportsmanagement.' . $view,
            (int) $user->id
        );
    }
}

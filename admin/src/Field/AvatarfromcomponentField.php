<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;

/** Lists avatar providers which are actually installed in the Joomla application. */
final class AvatarfromcomponentField extends ListField
{
    protected $type = 'avatarfromcomponent';

    protected function getOptions(): array
    {
        $providers = [
            'com_kunena' => 'COM_SPORTSMANAGEMENT_GLOBAL_AVATAR_FROM_KUNENA',
            'com_cbe' => 'COM_SPORTSMANAGEMENT_GLOBAL_AVATAR_FROM_JOOMLA_CBE',
            'com_comprofiler' => 'COM_SPORTSMANAGEMENT_GLOBAL_AVATAR_FROM_CB_ENHANCED',
        ];

        $options = [
            HTMLHelper::_('select.option', 'com_users', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_AVATAR_FROM_JOOMLA')),
        ];

        /** @var DatabaseInterface $db */
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $installed = [];

        try {
            $query = $db->getQuery(true)
                ->select($db->quoteName('element'))
                ->from($db->quoteName('#__extensions'))
                ->where($db->quoteName('type') . ' = ' . $db->quote('component'))
                ->where(
                    $db->quoteName('element') . ' IN ('
                    . implode(',', array_map([$db, 'quote'], array_keys($providers)))
                    . ')'
                );
            $db->setQuery($query);
            $installed = array_fill_keys($db->loadColumn() ?: [], true);
        } catch (\Throwable) {
            // Keep the Joomla user avatar option available even if extension discovery fails.
        }

        foreach ($providers as $element => $label) {
            if (isset($installed[$element])) {
                $options[] = HTMLHelper::_('select.option', $element, Text::_($label));
            }
        }

        return array_merge(parent::getOptions(), $options);
    }
}

<?php
/**
 * Joomla 5/6 native season list field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

final class SeasonlistField extends SportsManagementListField
{
    protected $type = 'seasonlist';

    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        $view = Factory::getApplication()->getInput()->getCmd('view');
        $element['class'] = 'form-select';

        if (in_array($view, ['club', 'league', 'playground'], true)) {
            $element['multiple'] = 'true';
        }

        $result = parent::setup($element, $value, $group);

        if (!$result) {
            return false;
        }

        if (in_array($view, ['clubs', 'projects'], true) && $this->name !== 'filter[copytoseason]') {
            $this->onchange = 'this.form.submit();';
        } elseif ($view === 'project') {
            $this->onchange = 'setseasonname();';
        }

        return true;
    }

    protected function getOptions(): array
    {
        $app = Factory::getApplication();
        $option = $app->getInput()->getCmd('option');
        $context = $option === 'com_modules' ? 'params' : 'request';
        $whichDatabase = $this->form->getValue('cfg_which_database', $context);
        $db = $this->getSportsManagementDatabase($whichDatabase);
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('id', 'value'),
                $db->quoteName('name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_season'))
            ->order($db->quoteName('name') . ' DESC');
        $db->setQuery($query);

        $placeholder = $this->name === 'filter[copytoseason]'
            ? Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_COPYTOSEASON_FILTER')
            : Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_SEASON_FILTER');
        $options = [
            (object) [
                'value' => '',
                'text' => $placeholder,
            ],
        ];

        return array_merge($options, $db->loadObjectList() ?: []);
    }
}

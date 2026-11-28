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

use Diddipoeler\Component\SportsManagement\Administrator\Service\SportsManagementAdministratorApplicationResolver;
use Joomla\CMS\Language\Text;

final class SeasonlistField extends SportsManagementListField
{
    protected $type = 'seasonlist';

    /** Native Joomla searchable list layout; retains the original option values. */
    protected $layout = 'joomla.form.field.list-fancy-select';

    /**
     * Configure season selection for the current administrator view.
     * Enables multiple selection for supported edit forms and preserves
     * historical submit or season-name callbacks for project workflows only
     * when the XML definition has not supplied its own change handler.
     * @param \SimpleXMLElement $element Field definition.
     * @param mixed $value Current field value.
     * @param string|null $group Optional form group.
     * @return bool Whether native field setup succeeds.
     */
    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        $app = SportsManagementAdministratorApplicationResolver::resolve();
        $view = $app->getInput()->getCmd('view');
        $element['class'] = 'form-select';

        if (in_array($view, ['club', 'league', 'playground'], true)) {
            $element['multiple'] = 'true';
        }

        $result = parent::setup($element, $value, $group);

        if (!$result) {
            return false;
        }

        // Respect explicitly configured callbacks, including dependent fields.
        if (trim((string) $this->onchange) !== '') {
            return true;
        }

        if (in_array($view, ['clubs', 'projects'], true) && $this->name !== 'filter[copytoseason]') {
            $this->onchange = 'this.form.submit();';
        } elseif ($view === 'project') {
            $this->onchange = 'setseasonname();';
        }

        return true;
    }

    /**
     * Load seasons from the database selected in the current form context.
     * Retains the historical descending name order and the context-specific
     * translated placeholder for season selection or project copying.
     * @return array Joomla list options.
     */
    protected function getOptions(): array
    {
        $app = SportsManagementAdministratorApplicationResolver::resolve();
        $option = $app->getInput()->getCmd('option');
        $context = $option === 'com_modules' ? 'params' : 'request';
        $whichDatabase = $this->form->getValue('cfg_which_database', $context);
        $db = $this->getSportsManagementDatabase($whichDatabase);
        $query = $db->createQuery()
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

        $rows = $db->loadObjectList();
        foreach (is_array($rows) ? $rows : [] as $item) {
            if (!is_object($item)
                || (!is_int($item->value ?? null) && !is_string($item->value ?? null))
                || !is_string($item->text ?? null)) {
                continue;
            }

            $options[] = $item;
        }

        return $options;
    }
}

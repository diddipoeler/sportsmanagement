<?php
/**
 * Joomla 5/6 native sports type list field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

final class SportstypelistField extends SportsManagementListField
{
    protected $type = 'sportstypelist';

    /**
     * Render the sport-type filter with an optional form submit handler.
     *
     * Resolves Joomla's administrator application and preserves the league
     * edit view, which does not auto-submit. Other views automatically
     * submit only when the XML field lacks a usable onchange callback.
     * Avoids converting nested legacy XML values to strings under PHP 8.
     *
     * @return string HTML rendered by Joomla's parent select field.
     */
    protected function getInput(): string
    {
        /** @var AdministratorApplication $app */
        $app = Factory::getContainer()->get(AdministratorApplication::class);
        $view = $app->getInput()->getCmd('view', '');

        $onchange = $this->element['onchange'] ?? '';
        $onchange = is_string($onchange) || $onchange instanceof \Stringable
            ? (string) $onchange : '';

        if ($view !== 'league' && trim($onchange) === '') {
            $this->element['onchange'] = 'this.form.submit();';
        }

        return parent::getInput();
    }

    /**
     * Load translated sport types for Joomla administrator selection.
     *
     * Queries the configured SportsManagement sports-type table in
     * alphabetical order, translates each label using Joomla's language
     * service and retains inherited select options for legacy forms.
     *
     * @return array<int,object> Sport type IDs and translated labels.
     */
    protected function getOptions(): array
    {
        $db = $this->getSportsManagementDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('id', 'value'),
                $db->quoteName('name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_sports_type'))
            ->order($db->quoteName('name'));
        $db->setQuery($query);

        $options = [];

        foreach ($db->loadObjectList() ?: [] as $item) {
            $options[] = (object) [
                'value' => (string) $item->value,
                'text' => Text::_((string) $item->text),
            ];
        }

        return array_merge(parent::getOptions(), $options);
    }
}

<?php
/**
 * Joomla 5/6 native user fields selector.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Service\SportsManagementAdministratorApplicationResolver;

final class UserfieldsField extends SportsManagementListField
{
    protected $type = 'userfields';

    /**
     * Configure the field selector to submit changes to the administrator form.
     * @param \SimpleXMLElement $element Field definition.
     * @param mixed $value Saved selection.
     * @param string|null $group Optional form group.
     * @return bool Parent setup result.
     */
    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        $element['class'] = 'form-select';
        $element['style'] = 'width:225px';
        $element['size'] = '1';
        $element['onchange'] = 'this.form.submit();';

        return parent::setup($element, $value, $group);
    }

    /**
     * Load extra fields matching the active project or team editor context.
     * Preserves inherited XML options and alphabetical ordering.
     * @return array Joomla extra field options.
     */
    protected function getOptions(): array
    {
        $view = SportsManagementAdministratorApplicationResolver::resolve()->getInput()->getCmd('view');
        $db = $this->getSportsManagementDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('id', 'value'),
                $db->quoteName('name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_user_extra_fields'))
            ->order($db->quoteName('name'));

        if (in_array($view, ['projects', 'project'], true)) {
            $query->where($db->quoteName('template_backend') . ' = ' . $db->quote('project'));
        } elseif (in_array($view, ['teams', 'team'], true)) {
            $query->where($db->quoteName('template_backend') . ' = ' . $db->quote('team'));
        }

        $db->setQuery($query);

        return array_merge(parent::getOptions(), $db->loadObjectList() ?: []);
    }
}

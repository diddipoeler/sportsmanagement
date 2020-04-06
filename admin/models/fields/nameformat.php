<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       nameformat.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage fields
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Factory;

/**
 * FormFieldNameFormat
 *
 * @package
 * @author    diddi
 * @copyright 2015
 * @version   $Id$
 * @access    public
 */
class JFormFieldNameFormat extends FormField
{
    protected $type = 'nameformat';

    /**
     * FormFieldNameFormat::getInput()
     *
     * @return
     */
    function getInput()
    {
        $lang = Factory::getLanguage();
        $extension = "com_sportsmanagement";
        $source = JPath::clean(JPATH_ADMINISTRATOR . '/components/' . $extension);
        $lang->load($extension, JPATH_ADMINISTRATOR, null, false, false)
        ||    $lang->load($extension, $source, null, false, false)
        ||    $lang->load($extension, JPATH_ADMINISTRATOR, $lang->getDefault(), false, false)
        ||    $lang->load($extension, $source, $lang->getDefault(), false, false);
      
        $mitems = array();
        $mitems[] = HTMLHelper::_('select.option', 0, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NAME_FORMAT_FIRST_NICK_LAST'));
        $mitems[] = HTMLHelper::_('select.option', 1, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NAME_FORMAT_LAST_NICK_FIRST'));
        $mitems[] = HTMLHelper::_('select.option', 2, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NAME_FORMAT_LAST_FIRST_NICK'));
        $mitems[] = HTMLHelper::_('select.option', 3, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NAME_FORMAT_FIRST_LAST'));
        $mitems[] = HTMLHelper::_('select.option', 4, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NAME_FORMAT_LAST_FIRST'));
        $mitems[] = HTMLHelper::_('select.option', 5, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NAME_FORMAT_NICK_FIRST_LAST'));
        $mitems[] = HTMLHelper::_('select.option', 6, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NAME_FORMAT_NICK_LAST_FIRST'));
        $mitems[] = HTMLHelper::_('select.option', 7, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NAME_FORMAT_FIRST_LAST_NICK'));
        $mitems[] = HTMLHelper::_('select.option', 8, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NAME_FORMAT_FIRST_LAST2'));
        $mitems[] = HTMLHelper::_('select.option', 9, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NAME_FORMAT_LAST_FIRST2'));
        $mitems[] = HTMLHelper::_('select.option', 10, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NAME_FORMAT_LAST'));
        $mitems[] = HTMLHelper::_('select.option', 11, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NAME_FORMAT_FIRST_NICK_LAST2'));
        $mitems[] = HTMLHelper::_('select.option', 12, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NAME_FORMAT_NICK'));
        $mitems[] = HTMLHelper::_('select.option', 13, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NAME_FORMAT_FIRST_LAST3'));
        $mitems[] = HTMLHelper::_('select.option', 14, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NAME_FORMAT_LAST2_FIRST'));
        $mitems[] = HTMLHelper::_('select.option', 15, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NAME_FORMAT_LAST_NEWLINE_FIRST'));
        $mitems[] = HTMLHelper::_('select.option', 16, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NAME_FORMAT_FIRST_NEWLINE_LAST'));
        $mitems[] = HTMLHelper::_('select.option', 17, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NAME_FORMAT_LAST_FIRST_NICK'));
        $mitems[] = HTMLHelper::_('select.option', 18, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NAME_FORMAT_LAST_FIRSTNAME_FIRST_CHAR_DOT'));
      
        $output= HTMLHelper::_(
            'select.genericlist',  $mitems,
            $this->name,
            'class="inputbox" size="1"',
            'value', 'text', $this->value, $this->id
        );
        return $output;
    }
}

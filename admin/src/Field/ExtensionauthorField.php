<?php
/**
 * Joomla 5/6 native extension-author field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;

final class ExtensionauthorField extends FormField
{
    protected $type = 'ExtensionAuthor';

    protected function getLabel(): string
    {
        $language = Factory::getApplication()->getLanguage();
        $language->load('com_sportsmanagement', JPATH_ADMINISTRATOR, $language->getTag(), true);

        return '<div style="clear: both;">' . Text::_('COM_SPORTSMANAGEMENT_AUTHOR_LABEL') . '</div>';
    }

    protected function getInput(): string
    {
        return '<div style="padding-top: 5px; overflow: inherit">'
            . 'Dieter Pl&ouml;ger @ <a href="https://www.fussballineuropa.de" target="_blank" rel="noopener noreferrer">Fussball in Europa</a>'
            . '</div>';
    }
}

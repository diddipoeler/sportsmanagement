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

use Diddipoeler\Component\SportsManagement\Administrator\Service\SportsManagementAdministratorApplicationResolver;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;

final class ExtensionauthorField extends FormField
{
    protected $type = 'ExtensionAuthor';

    /**
     * Render the localized extension-author field label for administrators.
     *
     * Escapes translated text so language overrides cannot inject markup.
     *
     * @return string HTML wrapper containing the translated author label.
     */
    protected function getLabel(): string
    {
        $language = SportsManagementAdministratorApplicationResolver::resolve()->getLanguage();
        $language->load('com_sportsmanagement', JPATH_ADMINISTRATOR, $language->getTag(), true);

        return '<div style="clear: both;">'
            . htmlspecialchars(Text::_('COM_SPORTSMANAGEMENT_AUTHOR_LABEL'), ENT_QUOTES, 'UTF-8')
            . '</div>';
    }

    /**
     * Render static author attribution with a safe external-site link.
     *
     * @return string HTML identifying the extension's original author.
     */
    protected function getInput(): string
    {
        return '<div style="padding-top: 5px; overflow: inherit">'
            . 'Dieter Pl&ouml;ger @ <a href="https://www.fussballineuropa.de" target="_blank" rel="noopener noreferrer">Fussball in Europa</a>'
            . '</div>';
    }
}

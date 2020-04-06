<?PHP
/**
*
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       extensionlink.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage fields
 */


defined('_JEXEC') or die ;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/**
 * FormFieldExtensionLink
 *
 * @package
 * @author    Dieter Plöger
 * @copyright 2017
 * @version   $Id$
 * @access    public
 */
class JFormFieldExtensionLink extends FormField
{
      
    public $type = 'ExtensionLink';

    /**
     * Method to get the field options.
     */
    /**
     * FormFieldExtensionLink::getLabel()
     *
     * @return
     */
    protected function getLabel()
    {
      
        $html = '';
      
        $lang = Factory::getLanguage();
        $extension = 'com_sportsmanagement';
        $base_dir = JPATH_ADMINISTRATOR;
        $language_tag = $lang->getTag();
        $reload = true;
        $lang->load($extension, $base_dir, $language_tag, $reload);
      
        $version = new JVersion();
        $jversion = explode('.', $version->getShortVersion());
      
        $type = $this->element['linktype'];
      
        if (intval($jversion[0]) > 2) {
            $html .= '<div style="clear: both;">';
        } else {
            $html .= '<div style="overflow: hidden; margin: 5px 0">';
            $html .= '<label style="margin: 0">';
        }
      
        $image = '';
        $title = '';
        switch ($type) {
        case 'forum': $image = 'chat.png'; $title = 'COM_SPORTSMANAGEMENT_EXTENSIONLINK_FORUM_LABEL';
            break;
        case 'demo': $image = 'visibility.png'; $title = 'COM_SPORTSMANAGEMENT_EXTENSIONLINK_DEMO_LABEL';
            break;
        case 'review': $image = 'thumb-up.png'; $title = 'COM_SPORTSMANAGEMENT_EXTENSIONLINK_REVIEW_LABEL';
            break;
        case 'donate': $image = 'paypal.png'; $title = 'COM_SPORTSMANAGEMENT_EXTENSIONLINK_DONATE_LABEL';
            break;
        case 'upgrade': $image = 'wallet-membership.png'; $title = 'COM_SPORTSMANAGEMENT_EXTENSIONLINK_UPGRADE_LABEL';
            break;
        case 'doc': $image = 'local-library.png'; $title = 'COM_SPORTSMANAGEMENT_EXTENSIONLINK_DOC_LABEL';
            break;
        case 'onlinedoc': $image = 'local-library.png'; $title = 'COM_SPORTSMANAGEMENT_EXTENSIONLINK_ONLINEDOC_LABEL';
            break;
        case 'report': $image = 'bug-report.png'; $title = 'COM_SPORTSMANAGEMENT_EXTENSIONLINK_BUGREPORT_LABEL';
            break;
        case 'support': $image = 'lifebuoy.png'; $title = 'COM_SPORTSMANAGEMENT_EXTENSIONLINK_SUPPORT_LABEL';
            break;
        case 'translate': $image = 'translate.png'; $title = 'COM_SPORTSMANAGEMENT_EXTENSIONLINK_TRANSLATE_LABEL';
            break;
        }
      
        if (intval($jversion[0]) > 2) {
            $html .= '<span class="label label-info">';
        }
                  
        if (!empty($image)) {
            $html .= '<img src="'.Uri::root().'administrator/components/'.$extension.'/assets/images/'.$image.'" style="margin-right: 5px;">';
            $html .= '<span style="vertical-align: middle">'.Text::_($title).'</span>';
        } else {
            $html .= Text::_($title);
        }
      
        if (intval($jversion[0]) > 2) {
            $html .= '</span>';
        }
      
        if (intval($jversion[0]) > 2) {
            $html .= '</div>';
        } else {
            $html .= '</label>';
        }
      
        return $html;
    }

    /**
     * Method to get the field input markup.
     */
    /**
     * FormFieldExtensionLink::getInput()
     *
     * @return
     */
    protected function getInput()
    {
      
        $lang = Factory::getLanguage();
        $extension = 'com_sportsmanagement';
        $base_dir = JPATH_ADMINISTRATOR;
        $language_tag = $lang->getTag();
        $reload = true;
        $lang->load($extension, $base_dir, $language_tag, $reload);
      
        $version = new JVersion();
        $jversion = explode('.', $version->getShortVersion());
      
        $type = $this->element['linktype'];
        $link = $this->element['link'];
        $specific_desc = $this->element['description'];
      
        $desc = '';
        switch ($type) {
        case 'forum': $image = true; $desc = 'COM_SPORTSMANAGEMENT_EXTENSIONLINK_FORUM_DESC';
            break;
        case 'demo': $image = true; $desc = 'COM_SPORTSMANAGEMENT_EXTENSIONLINK_DEMO_DESC';
            break;
        case 'review': $image = true; $desc = 'COM_SPORTSMANAGEMENT_EXTENSIONLINK_REVIEW_DESC';
            break;
        case 'donate': $image = true; $desc = 'COM_SPORTSMANAGEMENT_EXTENSIONLINK_DONATE_DESC';
            break;
        case 'upgrade': $image = true; $desc = 'COM_SPORTSMANAGEMENT_EXTENSIONLINK_UPGRADE_DESC';
            break;
        case 'doc': $image = true; $desc = 'COM_SPORTSMANAGEMENT_EXTENSIONLINK_DOC_DESC';
            break;
        case 'onlinedoc': $image = true; $desc = 'COM_SPORTSMANAGEMENT_EXTENSIONLINK_ONLINEDOC_DESC';
            break;
        case 'report': $image = true; $desc = 'COM_SPORTSMANAGEMENT_EXTENSIONLINK_BUGREPORT_DESC';
            break;
        case 'support': $image = true; $desc = 'COM_SPORTSMANAGEMENT_EXTENSIONLINK_SUPPORT_DESC';
            break;
        case 'translate': $image = true; $desc = 'COM_SPORTSMANAGEMENT_EXTENSIONLINK_TRANSLATE_DESC';
            break;
        }
      
        if (intval($jversion[0]) > 2 || ($image && intval($jversion[0]) < 3)) {
            $html = '<div style="padding-top: 5px; overflow: inherit">';
        } else {
            $html = '<div>';
        }
          
        if (isset($specific_desc)) {
            if (isset($link)) {
                $html .= Text::sprintf($specific_desc, $link);
            } else {
                $html .= Text::_($specific_desc);
            }
        } else {
            if (isset($link)) {
                $html .= Text::sprintf($desc, $link);
            } else {
                $html .= Text::_($desc);
            }
        }
      
        if (intval($jversion[0]) > 2) {
            // J3+
        } else {
            $html .= '</div>';
        }
      
        $html .= '</div>';

        return $html;
    }

}
?>

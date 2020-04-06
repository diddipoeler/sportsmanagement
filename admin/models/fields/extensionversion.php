<?php
/**
* 
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
* @version   1.0.58
* @file 
* @author    diddipoeler, stony, svdoldie (diddipoeler@gmx.de)
* @copyright Copyright: ? 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license   GNU General Public License version 2 or later; see LICENSE.txt
 */


defined('_JEXEC') or die ;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Factory;

//jimport('joomla.form.formfield');

/**
 * FormFieldExtensionVersion
 * 
 * @package 
 * @author    Dieter Plöger
 * @copyright 2017
 * @version   $Id$
 * @access    public
 */
class JFormFieldExtensionVersion extends FormField
{
        
    public $type = 'ExtensionVersion';
    
    protected $version;

    /**
     * FormFieldExtensionVersion::getLabel()
     * 
     * @return
     */
    protected function getLabel() 
    {        
        $lang = Factory::getLanguage();
        $extension = 'com_sportsmanagement';
        $base_dir = JPATH_ADMINISTRATOR;
        $language_tag = $lang->getTag();
        $reload = true;
        $lang->load($extension, $base_dir, $language_tag, $reload);
        $html = '';
        $html .= '<div style="clear: both;">'.Text::_('COM_SPORTSMANAGEMENT_VERSION_LABEL').'</div>';
        return $html;
    }

    /**
     * FormFieldExtensionVersion::getInput()
     * 
     * @return
     */
    protected function getInput() 
    {
        $html = '<div style="padding-top: 5px; overflow: inherit">';
        $html .= '<span class="label">'.$this->version.'</span>';
        $html .= '</div>';
        return $html;
    }
    
    /**
     * FormFieldExtensionVersion::setup()
     * 
     * @param  mixed $element
     * @param  mixed $value
     * @param  mixed $group
     * @return
     */
    public function setup(SimpleXMLElement $element, $value, $group = null)
    {
        $return = parent::setup($element, $value, $group);
    
        if ($return) {
            $this->version = isset($this->element['version']) ? $this->element['version'] : '';
        }
    
        return $return;
    }

}
?>

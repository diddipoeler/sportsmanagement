<?php
/**
* 
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       jsmsubtitle.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage fields
 */


defined('_JEXEC') or die ;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\FormField;


/**
 * FormFieldJSMSubtitle
 * 
 * @package 
 * @author    Dieter Plöger
 * @copyright 2018
 * @version   $Id$
 * @access    public
 */
class JFormFieldJSMSubtitle extends FormField
{
        
    public $type = 'JSMSubtitle';

    /**
     * Method to get the field options.
     *
     * @return array    The field option objects.
     * @since  1.6
     */
    protected function getLabel() 
    {
        
        $html = '';
        $value = trim($this->element['title']);

        $html .= '<div style="clear: both;"></div>';
        $html .= '<div style="margin: 20px 0 20px 20px; font-weight: bold; padding: 5px; color: #444444; border-bottom: 1px solid #444444;">';
        if ($value) {
            $html .= Text::_($value);
        }
        $html .= '</div>';

        return $html;
    }

    /**
     * Method to get the field input markup.
     *
     * @return string    The field input markup.
     * @since  1.6
     */
    protected function getInput() 
    {
        return '';
    }

}
?>

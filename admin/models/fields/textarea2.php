<?php
/**
* 
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       textarea2.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage fields
 */

defined('_JEXEC') or die();
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Language\Text;
FormHelper::loadFieldClass('textarea');

/**
 * FormFieldTextarea2
 * 
 * @package 
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
 */
class JFormFieldTextarea2 extends JFormFieldTextarea
{
    protected $type = 'Textarea2';

    /**
     * FormFieldTextarea2::getInput()
     * 
     * @return
     */
    public function getInput()
    {
        $buffer = parent::getInput();
        if(isset($this->element->description)) {
            $buffer .= '<label></label>';
            $buffer .= '<div style="float:left;">'.Text::_($this->element->description).'</div>';
        }
        return $buffer;
    }

    /**
     * FormFieldTextarea2::setup()
     * 
     * @param  mixed $element
     * @param  mixed $value
     * @param  mixed $group
     * @return
     */
    public function setup(SimpleXMLElement $element, $value, $group = null)
    {
        if(isset($element->content) && empty($value)) {
            $value = $element->content;
        }
        return parent::setup($element, $value, $group);
    }
}

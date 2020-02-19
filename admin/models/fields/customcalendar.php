<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      customcalendar.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage fields
 */

defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;

jimport('joomla.form.helper');
FormHelper::loadFieldClass('calendar');

/**
 * FormFieldCustomCalendar
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class JFormFieldCustomCalendar extends FormFieldCalendar
{

    public $type = 'CustomCalendar';

    protected $defaultFormat = 'd-m-Y';

    /**
     * FormFieldCustomCalendar::getInput()
     * 
     * @return
     */
    protected function getInput()
    {
        $app = Factory::getApplication();
        parent::getInput();

        // Build the attributes array.
        $attributes = array();

        empty($this->size)      ? null : $attributes['size'] = $this->size;
        empty($this->maxlength) ? null : $attributes['maxlength'] = $this->maxlength;
        empty($this->class)     ? null : $attributes['class'] = $this->class;
        !$this->readonly        ? null : $attributes['readonly'] = '';
        !$this->disabled        ? null : $attributes['disabled'] = '';
        empty($this->onchange)  ? null : $attributes['onchange'] = $this->onchange;
        empty($hint)            ? null : $attributes['placeholder'] = $hint;
        $this->autocomplete     ? null : $attributes['autocomplete'] = 'off';
        !$this->autofocus       ? null : $attributes['autofocus'] = '';

        if ($this->required) {
            $attributes['required'] = '';
            $attributes['aria-required'] = 'true';
        }

        $date = new DateTime("now");

        $format = $this->element['format'] ? (string) $this->element['format'] : $this->defaultFormat;
        $validFormat = preg_replace('/%/', '', $format);
      
        if ( $this->value == '' || is_null($this->value) )
        {
        $this->value = '0000-00-00';
        }
            
        if ( $this->value != '0000-00-00' && $this->value != '' )
        {
        $date = new JDate($this->value);
        $this->value = $date->format($this->defaultFormat);
        }
        else
        {
        $this->value = '00-00-0000';    
        }
       
        return HTMLHelper::_('calendar', $this->value, $this->name, $this->id, $format, $attributes);
    }

}
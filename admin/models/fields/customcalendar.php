<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
* @copyright        Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license                This file is part of SportsManagement.
*
* SportsManagement is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* SportsManagement is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with SportsManagement.  If not, see <http://www.gnu.org/licenses/>.
*
* Diese Datei ist Teil von SportsManagement.
*
* SportsManagement ist Freie Software: Sie können es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
* veröffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es nützlich sein wird, aber
* OHNE JEDE GEWÄHELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

defined('_JEXEC') or die;
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('calendar');

/**
 * JFormFieldCustomCalendar
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class JFormFieldCustomCalendar extends JFormFieldCalendar
{

    public $type = 'CustomCalendar';

    protected $defaultFormat = 'd-m-Y';

    /**
     * JFormFieldCustomCalendar::getInput()
     * 
     * @return
     */
    protected function getInput()
    {
        $app = JFactory::getApplication();
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
        
        //$app->enqueueMessage(__METHOD__.' '.__LINE__.' format -> <br><pre>'.print_r($format, true).'</pre><br>','Notice');
        //$app->enqueueMessage(__METHOD__.' '.__LINE__.' validFormat -> <br><pre>'.print_r($validFormat, true).'</pre><br>','Notice');
        //$app->enqueueMessage(__METHOD__.' '.__LINE__.' value vorher -> <br><pre>'.print_r($this->value, true).'</pre><br>','Notice');
        
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

        //$app->enqueueMessage(__METHOD__.' '.__LINE__.' value nachher -> <br><pre>'.print_r($this->value, true).'</pre><br>','Notice');
        
        return JHtml::_('calendar', $this->value, $this->name, $this->id, $format, $attributes);
    }

}
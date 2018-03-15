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

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.filesystem.folder');
JFormHelper::loadFieldClass('list');
jimport('joomla.html.html');
jimport('joomla.form.formfield');

/**
 * JFormFieldseasonteamperson
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class JFormFieldseasonteamperson extends JFormField
{
	/**
	 * field type
	 * @var string
	 */
	public $type = 'checkboxes';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   11.1
	 */
	protected function getInput()
	{
		$app = JFactory::getApplication();
        $option = JFactory::getApplication()->input->getCmd('option');
        $select_id = JFactory::getApplication()->input->getVar('id');
        $this->value = explode(",", $this->value);
        $targettable = $this->element['targettable'];
        $targetid = $this->element['targetid'];
        
        
        //$app->enqueueMessage(JText::_('JFormFieldseasoncheckbox getInput targettable<br><pre>'.print_r($targettable,true).'</pre>'),'');
        //$app->enqueueMessage(JText::_('JFormFieldseasoncheckbox getInput targetid<br><pre>'.print_r($targetid,true).'</pre>'),'');
    
    
        // Initialize variables.
		//$options = array();
    
    // teilnehmende saisons selektieren
    if ( $select_id )
    {
    //$db = JFactory::getDbo();
    $query = JFactory::getDbo()->getQuery(true);
			// saisons selektieren
			$query->select('stp.season_id,stp.team_id, t.name as teamname, s.name as seasonname, c.logo_big as clublogo');
			$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_'.$targettable.' as stp');
            $query->join('INNER', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t ON t.id = stp.team_id');
            $query->join('INNER', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_club AS c ON c.id = t.club_id');
            $query->join('INNER', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_season AS s ON s.id = stp.season_id');
            
			$query->where($targetid.'='.$select_id);
            $query->order('s.name');
            
            $starttime = microtime(); 
            
			JFactory::getDbo()->setQuery($query);
            
            if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
            $options = JFactory::getDbo()->loadObjectList();
	}
    else
    {
        $options = '';
    }		
    
    //$app->enqueueMessage(JText::_('JFormFieldseasonteamperson getInput query<br><pre>'.print_r($query,true).'</pre>'),'');
    //$app->enqueueMessage(JText::_('JFormFieldseasoncheckbox getInput value<br><pre>'.print_r($this->value,true).'</pre>'),'');
    //$app->enqueueMessage(JText::_('JFormFieldseasoncheckbox getInput options<br><pre>'.print_r($options,true).'</pre>'),'');
   


// Initialize variables.
            $html = '';
            $attribs['width'] = '25px';
            $attribs['height'] = '25px';
            
            if ( $options )
            {
            $html .= '<table>';
            foreach ($options as $i => $option)
            {
            
            $html .= '<tr>';
            $html .= '<td>'.$option->seasonname.'</td>';
            
            $html .= '<td>'.JHtml::image($option->clublogo, '',	$attribs).'</td>';
            $html .= '<td>'.$option->teamname.'</td>';
            
            $html .= '</tr>';    
            }   
            
            $html .= '</table>';
            } 
            else
            {
                $html .= '<div class="alert alert-no-items">';
                $html .=JText::_('JGLOBAL_NO_MATCHING_RESULTS');
			$html .= '</div>';
            }
    
            return $html;    
    
    }
}

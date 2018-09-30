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

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
/**
 * JFormFieldrankingcolumns
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class JFormFieldrankingcolumns extends JFormField
{

	protected $type = 'rankingcolumns';

	
	/**
	 * JFormFieldrankingcolumns::getInput()
	 * 
	 * @return
	 */
	function getInput()
	{
		$result = array();
		$db = sportsmanagementHelper::getDBConnection();
        $app = JFactory::getApplication();
		$lang = JFactory::getLanguage();
        $option = JFactory::getApplication()->input->getCmd('option');
        
        $selrankingcol = (int) ($this->element['selrankingcol']);

    if ( $selrankingcol )
    {
$mitems[] = HTMLHelper::_('select.option', 'PLAYED', Text::_('COM_SPORTSMANAGEMENT_FES_RANKING_PARAM_ORDERED_COLUMN_PLAYED'));
$mitems[] = HTMLHelper::_('select.option', 'WINS', Text::_('COM_SPORTSMANAGEMENT_FES_RANKING_PARAM_ORDERED_COLUMN_WINS'));
$mitems[] = HTMLHelper::_('select.option', 'LOSSES', Text::_('COM_SPORTSMANAGEMENT_FES_RANKING_PARAM_ORDERED_COLUMN_LOSSES'));
$mitems[] = HTMLHelper::_('select.option', 'TIES', Text::_('COM_SPORTSMANAGEMENT_FES_RANKING_PARAM_ORDERED_COLUMN_TIES'));
$mitems[] = HTMLHelper::_('select.option', 'WOT', Text::_('COM_SPORTSMANAGEMENT_FES_RANKING_PARAM_ORDERED_COLUMN_WOT'));
$mitems[] = HTMLHelper::_('select.option', 'WSO', Text::_('COM_SPORTSMANAGEMENT_FES_RANKING_PARAM_ORDERED_COLUMN_WSO'));
$mitems[] = HTMLHelper::_('select.option', 'LOT', Text::_('COM_SPORTSMANAGEMENT_FES_RANKING_PARAM_ORDERED_COLUMN_LOT'));
$mitems[] = HTMLHelper::_('select.option', 'LSO', Text::_('COM_SPORTSMANAGEMENT_FES_RANKING_PARAM_ORDERED_COLUMN_LSO'));
$mitems[] = HTMLHelper::_('select.option', 'SCOREFOR', Text::_('COM_SPORTSMANAGEMENT_FES_RANKING_PARAM_ORDERED_COLUMN_SCOREFOR'));
$mitems[] = HTMLHelper::_('select.option', 'SCOREAGAINST', Text::_('COM_SPORTSMANAGEMENT_FES_RANKING_PARAM_ORDERED_COLUMN_SCOREAGAINST'));
$mitems[] = HTMLHelper::_('select.option', 'SCOREPCT', Text::_('COM_SPORTSMANAGEMENT_FES_RANKING_PARAM_ORDERED_COLUMN_SCOREPCT'));
$mitems[] = HTMLHelper::_('select.option', 'RESULTS', Text::_('COM_SPORTSMANAGEMENT_FES_RANKING_PARAM_ORDERED_COLUMN_RESULTS'));
$mitems[] = HTMLHelper::_('select.option', 'DIFF', Text::_('COM_SPORTSMANAGEMENT_FES_RANKING_PARAM_ORDERED_COLUMN_DIFF'));
$mitems[] = HTMLHelper::_('select.option', 'POINTS', Text::_('COM_SPORTSMANAGEMENT_FES_RANKING_PARAM_ORDERED_COLUMN_POINTS'));
$mitems[] = HTMLHelper::_('select.option', 'BONUS', Text::_('COM_SPORTSMANAGEMENT_FES_RANKING_PARAM_ORDERED_COLUMN_BONUS'));
$mitems[] = HTMLHelper::_('select.option', 'START', Text::_('COM_SPORTSMANAGEMENT_FES_RANKING_PARAM_ORDERED_COLUMN_START'));
$mitems[] = HTMLHelper::_('select.option', 'LEGS', Text::_('COM_SPORTSMANAGEMENT_FES_RANKING_PARAM_ORDERED_COLUMN_LEGS'));
$mitems[] = HTMLHelper::_('select.option', 'LEGS_DIFF', Text::_('COM_SPORTSMANAGEMENT_FES_RANKING_PARAM_ORDERED_COLUMN_LEGS_DIFF'));
$mitems[] = HTMLHelper::_('select.option', 'GB', Text::_('COM_SPORTSMANAGEMENT_FES_RANKING_PARAM_ORDERED_COLUMN_GB'));
$mitems[] = HTMLHelper::_('select.option', 'LEGS_RATIO', Text::_('COM_SPORTSMANAGEMENT_FES_RANKING_PARAM_ORDERED_COLUMN_LEGS_RATIO'));
$mitems[] = HTMLHelper::_('select.option', 'WINPCT', Text::_('COM_SPORTSMANAGEMENT_FES_RANKING_PARAM_ORDERED_COLUMN_WINPCT'));
$mitems[] = HTMLHelper::_('select.option', 'QUOT', Text::_('COM_SPORTSMANAGEMENT_FES_RANKING_PARAM_ORDERED_COLUMN_QUOT'));
$mitems[] = HTMLHelper::_('select.option', 'NEGPOINTS', Text::_('COM_SPORTSMANAGEMENT_FES_RANKING_PARAM_ORDERED_COLUMN_NEGPOINTS'));
$mitems[] = HTMLHelper::_('select.option', 'PENALTYPOINTS', Text::_('COM_SPORTSMANAGEMENT_FES_RANKING_PARAM_ORDERED_COLUMN_PENALTYPOINTS'));
$mitems[] = HTMLHelper::_('select.option', 'OLDNEGPOINTS', Text::_('COM_SPORTSMANAGEMENT_FES_RANKING_PARAM_ORDERED_COLUMN_OLDNEGPOINTS'));
$mitems[] = HTMLHelper::_('select.option', 'POINTS_RATIO', Text::_('COM_SPORTSMANAGEMENT_FES_RANKING_PARAM_ORDERED_COLUMN_POINTS_RATIO'));
$mitems[] = HTMLHelper::_('select.option', 'TADMIN', Text::_('COM_SPORTSMANAGEMENT_FES_RANKING_PARAM_ORDERED_COLUMN_TADMIN'));
$mitems[] = HTMLHelper::_('select.option', 'GFA', Text::_('COM_SPORTSMANAGEMENT_FES_RANKING_PARAM_ORDERED_COLUMN_GFA'));
$mitems[] = HTMLHelper::_('select.option', 'GAA', Text::_('COM_SPORTSMANAGEMENT_FES_RANKING_PARAM_ORDERED_COLUMN_GAA'));
$mitems[] = HTMLHelper::_('select.option', 'PPG', Text::_('COM_SPORTSMANAGEMENT_FES_RANKING_PARAM_ORDERED_COLUMN_PPG'));
$mitems[] = HTMLHelper::_('select.option', 'PPP', Text::_('COM_SPORTSMANAGEMENT_FES_RANKING_PARAM_ORDERED_COLUMN_PPP'));
$mitems[] = HTMLHelper::_('select.option', 'LASTGAMES', Text::_('COM_SPORTSMANAGEMENT_FES_RANKING_PARAM_ORDERED_COLUMN_LASTGAMES'));		    
    
    
    }
    else
    {
        
    foreach( $this->value as $key => $value )
    {
        $mitems[] = HTMLHelper::_('select.option', $value, Text::_('COM_SPORTSMANAGEMENT_FES_RANKING_PARAM_ORDERED_COLUMN_'.$value));
    }    
        
        
    }
    
//$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' mitems<br><pre>'.print_r($mitems,true).'</pre>'),'Notice');
    
return HTMLHelper::_('select.genericlist',  $mitems, $this->name, 
				'class="inputbox" size="10" multiple="true" ', 'value', 'text', $this->value, $this->id);   
                                
	}
}
 
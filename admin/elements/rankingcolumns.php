<?php
/**
* 
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       rankingcolumns.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage elements
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;

/**
 * JFormFieldrankingcolumns
 * 
 * @package 
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
 */
class JFormFieldrankingcolumns extends FormField
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
        $app = Factory::getApplication();
        $lang = Factory::getLanguage();
        $option = Factory::getApplication()->input->getCmd('option');
        $mitems = array();
        
        $selrankingcol = (int) ($this->element['selrankingcol']);

        if ($selrankingcol ) {
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
            if ($this->value ) {    
                foreach( $this->value as $key => $value )
                {
                       $mitems[] = HTMLHelper::_('select.option', $value, Text::_('COM_SPORTSMANAGEMENT_FES_RANKING_PARAM_ORDERED_COLUMN_'.$value));
                }    
            }    
        }
  
        return HTMLHelper::_(
            'select.genericlist',  $mitems, $this->name, 
            'class="inputbox" size="10" multiple="true" ', 'value', 'text', $this->value, $this->id
        );   
                                
    }
}
 

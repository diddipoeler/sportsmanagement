<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      checkboxes.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage fields
 */

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Form\FormHelper;

jimport('joomla.filesystem.folder');
FormHelper::loadFieldClass('list');
jimport('joomla.html.html');
jimport('joomla.form.formfield');

/**
 * FormFieldseasonteamperson
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class FormFieldseasonteamperson extends FormField
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
        
        
        //$app->enqueueMessage(JText::_('FormFieldseasoncheckbox getInput targettable<br><pre>'.print_r($targettable,true).'</pre>'),'');
        //$app->enqueueMessage(JText::_('FormFieldseasoncheckbox getInput targetid<br><pre>'.print_r($targetid,true).'</pre>'),'');
    
    
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
    
    //$app->enqueueMessage(JText::_('FormFieldseasonteamperson getInput query<br><pre>'.print_r($query,true).'</pre>'),'');
    //$app->enqueueMessage(JText::_('FormFieldseasoncheckbox getInput value<br><pre>'.print_r($this->value,true).'</pre>'),'');
    //$app->enqueueMessage(JText::_('FormFieldseasoncheckbox getInput options<br><pre>'.print_r($options,true).'</pre>'),'');
   


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

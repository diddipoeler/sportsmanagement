<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage fields
 * @file       seasoncheckbox.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;

FormHelper::loadFieldClass('list');
jimport('joomla.html.html');
jimport('joomla.form.formfield');

/**
 * FormFieldseasoncheckbox
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class JFormFieldseasoncheckbox extends FormField
{
	/**
	 * field type
	 *
	 * @var string
	 */
	public $type = 'checkboxes';

	/**
	 * Method to get the field options.
	 *
	 * @return array  The field option objects.
	 *
	 * @since 11.1
	 */
	protected function getInput()
	{
		$app         = Factory::getApplication();
		$option      = Factory::getApplication()->input->getCmd('option');
		$select_id   = Factory::getApplication()->input->getVar('id');
		$this->value = explode(",", $this->value);
		$targettable = $this->element['targettable'];
		$targetid    = $this->element['targetid'];
        $this->teamvalue = array();
		$options = array();
		$optionsclub = array();
		$optionsposition = array();

		$query = Factory::getDbo()->getQuery(true);

		$query->select('id AS value, name AS text');
		$query->from('#__sportsmanagement_season');
		$query->order('name DESC');
		Factory::getDbo()->setQuery($query);
		$options = Factory::getDbo()->loadObjectList();
		switch ( $targettable )
		{
		case 'season_person_id':
        if ( $select_id )
        {
        $query->clear();
        $query->select('st.name');
		$query->from('#__sportsmanagement_sports_type as st');
        $query->join('INNER', '#__sportsmanagement_person AS p ON p.sports_type_id = st.id ');
        $query->where('p.id =' . $select_id);
        Factory::getDbo()->setQuery($query);
        $sports_type_name = Factory::getDbo()->loadResult();
        }
        
		$query->clear();
		$query->select('id AS value, name AS text');
		$query->from('#__sportsmanagement_club');
		$query->order('name DESC');
		Factory::getDbo()->setQuery($query);
		$optionsclub = Factory::getDbo()->loadObjectList();
		$query->clear();
		$query->select('id AS value, name AS text');
		$query->from('#__sportsmanagement_position');
		$query->order('name DESC');
		Factory::getDbo()->setQuery($query);
		$optionsposition = Factory::getDbo()->loadObjectList();
		$mitemsclub = array(HTMLHelper::_('select.option', '', Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_SELECT_CLUB')));
		foreach ($optionsclub as $club)
		{
		$mitemsclub[] = HTMLHelper::_('select.option', $club->value,  $club->text );
		}
				
		$mitemsposition = array(HTMLHelper::_('select.option', '', Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_SELECT_POSITION')));
		foreach ($optionsposition as $position)
		{
		$mitemsposition[] = HTMLHelper::_('select.option', $position->value,  $position->text );
		}		
				
		break;
		}
		
		

		$starttime = microtime();
		

		/** Teilnehmende saisons selektieren */
		if ($select_id)
		{
			$query = Factory::getDbo()->getQuery(true);

			switch ( $targettable )
			{
			case 'season_team_id':
			$query->select('season_id,teamname,season_teamname');
			$query->from('#__sportsmanagement_' . $targettable);
			break;
			case 'season_person_id':
			$query->select('season_id,position_id,club_id');
			$query->from('#__sportsmanagement_' . $targettable);
			break;
			}
			
			$query->where($targetid . '=' . $select_id);
			$query->group('season_id');
			$starttime = microtime();
try
{
Factory::getDbo()->setQuery($query);
$this->value = Factory::getDbo()->loadColumn();
$this->teamvalue = Factory::getDbo()->loadAssocList('season_id');
}
catch (Exception $e)
{
$app->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), 'notice');
$app->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', __FILE__, __LINE__), 'notice');
$this->value = '';
}
            
		}
		else
		{
			$this->value = '';
		}

		$html = array();
		$class = $this->element['class'] ? ' class="checkboxes ' . (string) $this->element['class'] . '"' : ' class="checkboxes"';
		$html[] = '<fieldset id="' . $this->id . '"' . $class . '>';
		$html[] = '<table class="table-striped">';
        $html[]  = '<thead><tr>';
        $html[]  = '<th scope="col">#</th>';
      $html[]  = '<th scope="col">Saison</th>';
      $html[]  = '<th scope="col">1 Teamname</th>';
      $html[]  = '<th scope="col">2 Teamname</th>';
        $html[]  = '</tr></thead>';
        

		foreach ($options as $i => $option)
		{
		  
            if ( !array_key_exists($option->value, $this->teamvalue) ) {
            $this->teamvalue[$option->value] = array();
            }
            if ( !array_key_exists('teamname', $this->teamvalue[$option->value]) ) {
            $this->teamvalue[$option->value]['teamname'] = '';
            }
            
			$checked  = (in_array((string) $option->value, (array) $this->value) ? ' checked="checked"' : '');
			$class    = !empty($option->class) ? ' class="' . $option->class . '"' : '';
			$disabled = !empty($option->disable) ? ' disabled="disabled"' : '';

			$onclick = !empty($option->onclick) ? ' onclick="' . $option->onclick . '"' : '';
			$html[]  = '<tr><td>';
			$html[]  = '<input type="checkbox" id="' . $this->id . $i . '" name="' . $this->name . '[]"' . ' value="'
				. htmlspecialchars($option->value, ENT_COMPAT, 'UTF-8') . '"' . $checked . $class . $onclick . $disabled . '/>';
$html[] = '</td>';
          $html[]  = '<td>';
			$html[] = '<label for="' . $this->id . $i . '"' . $class . '>' . Text::_($option->text) . '</label>';
			$html[] = '</td>';
            
            switch ( $targettable )
			{
			case 'season_team_id':
			$html[]  = '<td>';
          $html[]  = '<input size="70" type="text" id="' . 'jform_teamvalue' . $i . '" name="' . 'jform[teamvalue]['.$option->value.']"' . ' value="'
				. $this->teamvalue[$option->value]['teamname']. '"' .  '/>';
          $html[] = '</td>';
          
          
           $html[]  = '<td>';
          $html[]  = '<input size="70" type="text" id="' . 'jform_season_teamname' . $i . '" name="' . 'jform[season_teamname]['.$option->value.']"' . ' value="'
				. $this->teamvalue[$option->value]['season_teamname']. '"' .  '/>';
          $html[] = '</td>';
          
			break;
			case 'season_person_id':
			
			break;
			}
          /**
          $html[]  = '<td>';
          $html[]  = '<input type="text" id="' . 'jform_teamvalue' . $i . '" name="' . 'jform[teamvalue]['.$option->value.']"' . ' value="'
				. $this->teamvalue[$option->value]['teamname']. '"' .  '/>';
          $html[] = '</td>';
			*/
            
            /**
            $html[]  = '<td>';
          $html[]  = '<input type="text" id="' . 'jform_teamvalue2' . $i . '" name="' . 'jform[teamvalue2]['.$option->value.']"' . ' value="'
				. $this->teamvalue2[$option->value]['season_teamname']. '"' .  '/>';
          $html[] = '</td>';
          */
switch ( $targettable )
{
case 'season_person_id':

$html_club_id = array_key_exists('club_id', $this->teamvalue[$option->value]) ? $this->teamvalue[$option->value]['club_id'] : 0;
$html_position_id = array_key_exists('position_id', $this->teamvalue[$option->value]) ? $this->teamvalue[$option->value]['position_id'] : 0;

if ( ComponentHelper::getParams('com_sportsmanagement')->get('assign_club_position_to_player', 0) || $sports_type_name == 'COM_SPORTSMANAGEMENT_ST_TABLETENNIS' )
{
$html[]  = '<td>';
$html[] = HTMLHelper::_('select.genericlist', $mitemsclub, 'season_person_club_id' . '[]', 'class="inputbox" ', 'value', 'text', $html_club_id, 'id');		
$html[] = '</td>';		
$html[]  = '<td>';
$html[] = HTMLHelper::_('select.genericlist', $mitemsposition, 'season_person_position_id' . '[]', 'class="inputbox" ', 'value', 'text', $html_position_id, 'id');		
$html[] = '</td>';		
}		
break;
}			
			
			
          $html[] = '</tr>';
          
		}

		$html[] = '</table>';
		$html[] = '</fieldset>';

		return implode($html);

	}
}

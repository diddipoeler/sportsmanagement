<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      seasoncheckbox.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage fields
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Filesystem\Folder;
FormHelper::loadFieldClass('list');
jimport('joomla.html.html');
jimport('joomla.form.formfield');

/**
 * FormFieldseasoncheckbox
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class JFormFieldseasoncheckbox extends FormField
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
	$app = Factory::getApplication();
        $option = Factory::getApplication()->input->getCmd('option');
        $select_id = Factory::getApplication()->input->getVar('id');
        $this->value = explode(",", $this->value);
        $targettable = $this->element['targettable'];
        $targetid = $this->element['targetid'];
        // Initialize variables.
	$query = Factory::getDbo()->getQuery(true);
	// saisons selektieren
	$query->select('id AS value, name AS text');
	$query->from('#__sportsmanagement_season');
	$query->order('name DESC');
            
        $starttime = microtime(); 
	Factory::getDbo()->setQuery($query);
	$options = Factory::getDbo()->loadObjectList();
    
    // teilnehmende saisons selektieren
    if ( $select_id )
    {
    $query = Factory::getDbo()->getQuery(true);
			// saisons selektieren
			$query->select('season_id');
			$query->from('#__sportsmanagement_'.$targettable);
			$query->where($targetid.'='.$select_id);
            $query->group('season_id');
            $starttime = microtime(); 
			Factory::getDbo()->setQuery($query);
			$this->value = Factory::getDbo()->loadColumn();
    }
    else
    {
        $this->value = '';
    }

// Initialize variables.
            $html = array();
            // Initialize some field attributes.
            $class = $this->element['class'] ? ' class="checkboxes ' . (string) $this->element['class'] . '"' : ' class="checkboxes"';
            // Start the checkbox field output.
            $html[] = '<fieldset id="' . $this->id . '"' . $class . '>';
            // Build the checkbox field output.
            $html[] = '<ul>';
            foreach ($options as $i => $option)
            {
                // Initialize some option attributes.
                $checked = (in_array((string) $option->value, (array) $this->value) ? ' checked="checked"' : '');
                $class = !empty($option->class) ? ' class="' . $option->class . '"' : '';
                $disabled = !empty($option->disable) ? ' disabled="disabled"' : '';
                // Initialize some JavaScript option attributes.
                $onclick = !empty($option->onclick) ? ' onclick="' . $option->onclick . '"' : '';
                $html[] = '<li>';
                $html[] = '<input type="checkbox" id="' . $this->id . $i . '" name="' . $this->name . '[]"' . ' value="'
                    . htmlspecialchars($option->value, ENT_COMPAT, 'UTF-8') . '"' . $checked . $class . $onclick . $disabled . '/>';
    
                $html[] = '<label for="' . $this->id . $i . '"' . $class . '>' . Text::_($option->text) . '</label>';
                $html[] = '</li>';
            }
            $html[] = '</ul>';
            // End the checkbox field output.
            $html[] = '</fieldset>';
    
            return implode($html);    
    
    }
}

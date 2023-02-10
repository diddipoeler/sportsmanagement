<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage fields
 * @file       clublist.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Form\FormHelper;

FormHelper::loadFieldClass('list');

/**
 * FormFieldClublist
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class JFormFieldClublist extends \JFormFieldList
{
	/**
	 * field type
	 *
	 * @var string
	 */
	public $type = 'clublist';

	/**
	 * Method to get the field options.
	 *
	 * @return array  The field option objects.
	 *
	 * @since 11.1
	 */
	protected function getOptions()
	{
   
      //echo 'this value<pre>'.print_r($this->value,true).'</pre>';
    //echo 'label<pre>'.print_r($this->label,true).'</pre>';
    //echo 'name<pre>'.print_r($this->name,true).'</pre>';
    //echo 'fieldname<pre>'.print_r($this->fieldname,true).'</pre>';
      
      //echo 'element<pre>'.print_r($this->element,true).'</pre>';
      //echo 'element<pre>'.print_r($this->element['target'],true).'</pre>';
      
      
      $sport_type = (string) $this->element->attributes()->target;
      //echo 'sport_type<pre>'.print_r($sport_type,true).'</pre>';
      
      
		// Initialize variables.
		$options = array();

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select('c.id AS value, c.name AS text');
		$query->from('#__sportsmanagement_club as c');
      $query->join('LEFT', '#__sportsmanagement_team AS t ON t.club_id = c.id');
      
      if ( $sport_type )
      {
      $query->join('INNER', '#__sportsmanagement_sports_type AS st ON st.id = t.sports_type_id');  
      $query->where("st.name LIKE '" . $sport_type . "'");
      }
      $query->group('c.id');
		$query->order('c.name');
		$db->setQuery($query);
		$options = $db->loadObjectList();

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}

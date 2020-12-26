<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage fields
 * @file       playgroundlist.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
FormHelper::loadFieldClass('list');

/**
 * FormFieldplaygroundlist
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class JFormFieldplaygroundlist2 extends \JFormFieldList
{
	/**
	 * field type
	 *
	 * @var string
	 */
	public $type = 'playgroundlist2';

  
  protected function getInput()
	{
    // The active item id field.
		$thisvalue = (int) $this->value > 0 ? (int) $this->value : '';
    // Build the script.
		$script = array();
    
       
    
    //echo 'thisvalue<pre>'.print_r($thisvalue,true).'</pre>';
    //echo 'this value<pre>'.print_r($this->value,true).'</pre>';
    //echo 'label<pre>'.print_r($this->label,true).'</pre>';
    //echo 'name<pre>'.print_r($this->name,true).'</pre>';
    //echo 'fieldname<pre>'.print_r($this->fieldname,true).'</pre>';
    
    $db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select('id AS value, name AS text,picture as itempicture');
		$query->from('#__sportsmanagement_playground');
		$query->order('name');
		$db->setQuery($query);
		$options = $db->loadObjectList();
  
    $script[] = 'var '.$this->fieldname.' = new Array;';
    foreach ($options as $key => $value)
			{
				if (!$value->itempicture)
				{
					$value->itempicture = sportsmanagementHelper::getDefaultPlaceholder("playgrounds");
				}

				$script[] = $this->fieldname.'[' . ($key) . ']=\'' . $value->itempicture . "';";
			}
    
    
    Factory::getDocument()->addScriptDeclaration(implode("\n", $script));
    
    Factory::getDocument()->addStyleDeclaration(
			'
img.item {
    padding-right: 10px;
    vertical-align: middle;
}
img.car {
    height: 25px;
}'
		);
    
// String $opt - second parameter of formbehavior2::select2
		// for details http://ivaynberg.github.io/select2/
		$opt = ' allowClear: true,
   width: "100%",
   formatResult: function format(state)
   {
   var originalOption = state.element;
   var picture;
   picture = '.$this->fieldname.'[state.id];
   if (!state.id)
   return state.text;
   return "<img class=\'item car\' src=\'' . Uri::root() . '" + picture + "\' />" + state.text;
   },
 
   escapeMarkup: function(m) { return m; }
';
    
    // Setup variables for display.
	  $options = array_merge(parent::getOptions(), $options);
	  $append = '';
		$html = array();
    $html[] = HTMLHelper::_('formbehavior2.select2', '.'.$this->fieldname, $opt);
    
    $html[] = HTMLHelper::_(
				'select.genericlist', $options, $this->name,
				'style="width:225px;" class="'.$this->fieldname.'" size="1"' . $append, 'value', 'text', $this->value 
			);
    
    
    
    return implode("\n", $html);
  }
  
  
	/**
	 * Method to get the field options.
	 *
	 * @return array  The field option objects.
	 *
	 * @since 11.1
	 */
	protected function getOptions()
	{
		/**
		 *          Initialize variables.
		 */
		$options = array();

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select('id AS value, name AS text');
		$query->from('#__sportsmanagement_playground');
		$query->order('name');
		$db->setQuery($query);
		$options = $db->loadObjectList();

		/**
		 *          Merge any additional options in the XML definition.
		 */
		$options = array_merge(parent::getOptions(), $options);

		//return $options;
	}
}

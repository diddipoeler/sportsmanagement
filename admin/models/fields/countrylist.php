<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage fields
 * @file       countrylist.php
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
use Joomla\CMS\Language\Text;
FormHelper::loadFieldClass('list');

/**
 * FormFieldcountrylist
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class JFormFieldcountrylist extends \JFormFieldList
{
	/**
	 * field type
	 *
	 * @var string
	 */
	public $type = 'countrylist';

  
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

		$query->select('alpha3 AS value, name AS text,alpha2 as itempicture');
		$query->from('#__sportsmanagement_countries');
		$query->order('name');
		$db->setQuery($query);
		$options = $db->loadObjectList();
  
    
    $script[] = 'var '.$this->fieldname.' = new Array;';
$script[] = $this->fieldname.'[\'' . '\']=\'' . "';";	  
	  
	  
    foreach ($options as $key => $value)
			{
      $value->text = Text::_($value->text);
      
      
      $value->itempicture = JSMCountries::getIso3Flag($value->value);
      
      
      
				if (!$value->itempicture)
				{
					$value->itempicture = sportsmanagementHelper::getDefaultPlaceholder("playgrounds");
				}
if ( $value->value )
{
				$script[] = $this->fieldname.'[\'' . ($value->value) . '\']=\'' . $value->itempicture . "';";
}
			}
    
    
    Factory::getDocument()->addScriptDeclaration(implode("\n", $script));
    
    Factory::getDocument()->addStyleDeclaration(
			'
img.item {
    padding-right: 10px;
    vertical-align: middle;
}
img.car {
    height: 15px;
}'
		);
    
// String $opt - second parameter of formbehavior2::select2
		// for details http://ivaynberg.github.io/select2/
		$opt = ' allowClear: true,
   width: "400px",
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
	  $append = 'onchange="this.form.submit();"';
		$html = array();
    $html[] = HTMLHelper::_('formbehavior2.select2', '.'.$this->fieldname, $opt);
    
    $html[] = HTMLHelper::_(
				'select.genericlist', $options, $this->name,
				'style="width:225px;" class="'.$this->fieldname.'" size="1"' . $append, 'value', 'text', $this->value 
			);
    
    
    
    return implode("\n", $html);
  }
  
  
	
}

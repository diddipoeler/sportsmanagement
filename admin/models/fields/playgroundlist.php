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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Form\FormHelper;
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
class JFormFieldplaygroundlist extends \JFormFieldList
{
	/**
	 * field type
	 *
	 * @var string
	 */
	public $type = 'playgroundlist';

  /**
   * JFormFieldplaygroundlist::getInput()
   * 
   * @return
   */
  protected function getInput()
	{
	   $document = Factory::getDocument();
       /** Some CSS */
		$document->addStyleDeclaration(
			'
img.item {
    padding-right: 10px;
    vertical-align: middle;
}
img.car {
    height: 25px;
}'
		);
    /**
		 *          Initialize variables.
		 */
		$options = array();
        $script = array();
$html = '';


		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select('id AS value, name AS text, picture as teampicture');
		$query->from('#__sportsmanagement_playground');
		$query->order('name');
		$db->setQuery($query);
		$options = $db->loadObjectList();
        
    //echo '<pre>'.print_r($options,true).'</pre>';
    
$script[] = "var teampicture = new Array;";        
foreach ($options as $key => $value)
			{
				if (!$value->teampicture)
				{
					$value->teampicture = sportsmanagementHelper::getDefaultPlaceholder("playgrounds");
				}

				$script[] = 'teampicture[' . ($key) . ']=\'' . $value->teampicture . "';\n";
			}        
Factory::getDocument()->addScriptDeclaration(implode("\n", $script));        
/**
 * String $opt - second parameter of formbehavior2::select2
 * for details http://ivaynberg.github.io/select2/
 */
      $opt = ' allowClear: true,
   width: "100%",

   formatResult: function format(state)
   {
   var originalOption = state.element;
   var picture;
   //picture = "images/com_sportsmanagement/database/playgrounds/placeholder_stadium.png";
   picture = teampicture[state.id];
   if (!state.id)
   return state.text;
   return "<img class=\'item car\' src=\'' . Uri::root() . '" + picture + "\' />" + state.text;
   },
 
   escapeMarkup: function(m) { return m; }
';		

      $append = '';
		$html .= HTMLHelper::_('formbehavior2.select2', '.test1', $opt);
      $html .= HTMLHelper::_(
				'select.genericlist', $options, 'playground_id',
				'style="width:225px;" class="test1" size="6"' . $append, 'value', 'text', $this->value, $this->id
			);
      
      
		return $html;
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

	}
}

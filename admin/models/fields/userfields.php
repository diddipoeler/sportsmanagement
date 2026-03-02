<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage fields
 * @file       userfields.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;
FormHelper::loadFieldClass('list');



/**
 * FormFieldactseason
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class JFormFielduserfields extends \JFormFieldList
{
	protected $type = 'userfields';

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

	   $view = Factory::getApplication()->input->getVar('view');


		$query->select('s.id AS value, s.name AS text, s.name as itempicture');
		$query->from('#__sportsmanagement_user_extra_fields as s');
		$query->order('s.name');

switch ( $view )
	{
		case 'projects':
		case 'project':
$query->where('template_backend LIKE '.$db->Quote(''.'project'.''));
		break;
		case 'teams':
		case 'team':
$query->where('template_backend LIKE '.$db->Quote(''.'team'.''));
		break;

	}
		$db->setQuery($query);
		$options = $db->loadObjectList();

    $script[] = 'var '.$this->fieldname.' = new Array;';
    foreach ($options as $key => $value)
			{
				if (!$value->itempicture)
				{
					$value->itempicture = sportsmanagementHelper::getDefaultPlaceholder("ph_logo_small");
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
	  $append = 'onchange="this.form.submit();"';
		$html = array();

if (version_compare( substr(JVERSION, 0, 3), '5.0', 'ge'))
{
$html[] = HTMLHelper::_('formbehavior.chosen', '.'.$this->fieldname, $opt);
}
else
{
$html[] = HTMLHelper::_('formbehavior2.select2', '.'.$this->fieldname, $opt);
}

    $html[] = HTMLHelper::_(
				'select.genericlist', $options, $this->name,
				'style="width:225px;" class="'.$this->fieldname.' form-select " size="1"' . $append, 'value', 'text', $this->value
			);



    return implode("\n", $html);
  }

	/**
	 * FormFieldactseason::getOptions()
	 *
	 * @return
	 */
	protected function getOptions()
	{
	    /**
		$options = array();
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

$view = Factory::getApplication()->input->getVar('view');


		$query->select('s.id AS value, s.name AS text');
		$query->from('#__sportsmanagement_user_extra_fields as s');
		$query->order('s.name');

switch ( $view )
	{
		case 'projects':
		case 'project':
$query->where('template_backend LIKE '.$db->Quote(''.'project'.''));
		break;
		case 'teams':
		case 'team':
$query->where('template_backend LIKE '.$db->Quote(''.'team'.''));
		break;

	}


		$db->setQuery($query);
		$options = $db->loadObjectList();


		$options = array_merge(parent::getOptions(), $options);

		return $options;
        */
	}


}

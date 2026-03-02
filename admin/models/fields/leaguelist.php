<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage fields
 * @file       leaguelist.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Component\ComponentHelper;
FormHelper::loadFieldClass('list');


/**
 * FormFieldleaguelist
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class JFormFieldleaguelist extends \JFormFieldList
{
	/**
	 * field type
	 *
	 * @var string
	 */
	public $type = 'leaguelist';


 protected function getInput()
	{
     $options = array();
     $script = array();
$db    = Factory::getDbo();
		$query = $db->getQuery(true);
      $app = Factory::getApplication();
		$jinput                 = $app->input;
		$projectid        = (int) $jinput->get('id', 0, '');
      $view = $jinput->getVar("view");
      $country_result = '';
      $post = Factory::getApplication()->input->post->getArray();

      //Factory::getApplication()->enqueueMessage('<pre>'.print_r($post,true)      .'</pre>', 'error');

      switch ($view)
{
	case 'project':
          if ( $projectid )
          {
		$query->clear();
		$query->select('l.country');
		$query->from('#__sportsmanagement_league as l');
		$query->join('INNER', '#__sportsmanagement_project as p on p.league_id = l.id');
		$query->where('p.id = ' . $projectid);
        $db->setQuery($query);
			$country_result = $db->loadResult();
            //echo '<pre>'.print_r($country_result,true).'</pre>';

          }
          break;
          case 'projects':
          $country_result = $post['filter']['search_nation'];
          break;
      }




$query->clear();
		$query->select('l.id AS value, concat(l.name, \' (\' , l.id, \')\') AS text, l.picture as itempicture');
		$query->from('#__sportsmanagement_league as l');
       if ( $country_result )
            {
            $query->where('l.country LIKE ' . $db->Quote('' . $country_result . ''));

            }
		$query->order('text');
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
	 * Method to get the field options.
	 *
	 * @return array  The field option objects.
	 *
	 * @since 11.1
	 */
	protected function getOptions()
	{
	    /**
		$options = array();
$db    = Factory::getDbo();
		$query = $db->getQuery(true);
      $app = Factory::getApplication();
		$jinput                 = $app->input;
		$projectid        = (int) $jinput->get('id', 0, '');
      $view = $jinput->getVar("view");
      $country_result = '';
      $post = Factory::getApplication()->input->post->getArray();
      
      //Factory::getApplication()->enqueueMessage('<pre>'.print_r($post,true)      .'</pre>', 'error');

      switch ($view)
{
	case 'project':
          if ( $projectid )
          {
		$query->clear();
		$query->select('l.country');
		$query->from('#__sportsmanagement_league as l');
		$query->join('INNER', '#__sportsmanagement_project as p on p.league_id = l.id');
		$query->where('p.id = ' . $projectid);  
        $db->setQuery($query);
			$country_result = $db->loadResult();
            //echo '<pre>'.print_r($country_result,true).'</pre>';

          }
          break;
          case 'projects':
          $country_result = $post['filter']['search_nation'];
          break;
      }
      


		
$query->clear();
		$query->select('l.id AS value, concat(l.name, \' (\' , l.id, \')\') AS text');
		$query->from('#__sportsmanagement_league as l');
       if ( $country_result )
            {
            $query->where('l.country LIKE ' . $db->Quote('' . $country_result . ''));

            }
		$query->order('text');
		$db->setQuery($query);
		$options = $db->loadObjectList();

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
        */
	}
}

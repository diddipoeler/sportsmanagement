<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage fields
 * @file       leaguelist.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Form\FormHelper;

jimport('joomla.filesystem.folder');
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

	/**
	 * Method to get the field options.
	 *
	 * @return array  The field option objects.
	 *
	 * @since 11.1
	 */
	protected function getOptions()
	{
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
/** Holen wir uns das land der liga */
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
		$query->select('l.id AS value, l.name AS text');
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
	}
}

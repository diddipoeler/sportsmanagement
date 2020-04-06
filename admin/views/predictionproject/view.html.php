<?php
/**
* 
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage predictionproject
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Log\Log;

/**
 * sportsmanagementViewpredictionproject
 * 
 * @package 
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
 */
class sportsmanagementViewpredictionproject extends sportsmanagementView
{
    /**
     * sportsmanagementViewpredictionproject::init()
     * 
     * @return
     */
    public function init()
    {
        
        // get the Data
        $this->form = $this->get('Form');
        $this->item = $this->get('Item');
        $this->script = $this->get('Script');
        if(version_compare(substr(JVERSION, 0, 3), '4.0', 'ge')) {
             $this->form->setFieldAttribute('champ', 'type', 'radio');                    
             $this->form->setFieldAttribute('champ', 'class', 'switcher');                    
             $this->form->setFieldAttribute('joker', 'type', 'radio');                    
             $this->form->setFieldAttribute('joker', 'class', 'switcher');                
             $this->form->setFieldAttribute('published', 'type', 'radio');                    
             $this->form->setFieldAttribute('published', 'class', 'switcher');                
             $this->form->setFieldAttribute('mode', 'type', 'radio');                    
             $this->form->setFieldAttribute('mode', 'class', 'switcher');                
             $this->form->setFieldAttribute('overview', 'type', 'radio');                    
             $this->form->setFieldAttribute('overview', 'class', 'switcher');                    
        }    
        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            Log::add(implode('<br />', $errors));
            return false;
        }

        $this->item->name = '';
        
        $this->app->setUserState("$this->option.pid", $this->item->project_id);
     
        // Set the document
        $this->setDocument();
        
        switch ( $this->getLayout() )
        {
        case 'edit';
        case 'edit_3';
        case 'edit_4';
            $this->setLayout('edit_3'); 
            break;
        }
        
    
    }
    
    /**
     * Method to set up the document properties
     *
     * @return void
     */
    protected function setDocument() 
    {
        $isNew = $this->item->id == 0;
        //$this->name = $this->item->name;
        $document = Factory::getDocument();
        $document->setTitle($isNew ? Text::_('COM_HELLOWORLD_HELLOWORLD_CREATING') : Text::_('COM_HELLOWORLD_HELLOWORLD_EDITING'));
        $document->addScript(Uri::root() . $this->script);
        $document->addScript(Uri::root() . "/administrator/components/com_sportsmanagement/views/sportsmanagement/submitbutton.js");
        Text::script('COM_HELLOWORLD_HELLOWORLD_ERROR_UNACCEPTABLE');
    }
    
}
?>

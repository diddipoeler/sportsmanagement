<?PHP
/**
 * SportsManagement ein Programm zur Verwaltung fűr alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage ranking
 * @file       deafult_joomla_vier.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

//HTMLHelper::_('behavior.switcher');

$this->startPane             = 'startTabSet';
$this->endPane               = 'endTabSet';
$this->addPanel              = 'addTab';
$this->endPanel              = 'endTab';
$this->config['table_class'] = 'table table-striped';

/*
// Define tabs options for version of Joomla! 4.0
$tabsOptions = array(
    "active" => "tab1id" // It is the ID of the active tab.
);
*/

$templatesToLoad = array('globalviews');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);

echo $this->loadTemplate('projectheading');

?>
   <!-- <div class="<?php echo $this->divclasscontainer; ?>"> -->
        <div class="<?php echo $this->divclassrow; ?>">
			<?PHP
            if ($this->config['show_result_tabs'] == 'show_tabs')
			{
				echo $this->loadTemplate('tabs');
			}
            else
            {
			//echo $this->loadTemplate('ranking');
echo HTMLHelper::_('bootstrap.' . $this->startPane, 'myTab', array('active' => 'tab1id'));

if ($this->config['show_table_1'])
{
	echo HTMLHelper::_('bootstrap.' . $this->addPanel, 'myTab', 'tab1id', Text::_($this->config['table_text_1']));

	?>
    <div class="<?php echo $this->divclasscontainer; ?>">
        <div class="<?php echo $this->divclassrow; ?>">
			<?PHP
			echo $this->loadTemplate('ranking');
			?>
        </div>
    </div>
	<?PHP
	echo HTMLHelper::_('bootstrap.' . $this->endPanel);
}


if ($this->config['show_table_2'])
{
	echo HTMLHelper::_('bootstrap.' . $this->addPanel, 'myTab', 'tab2id', Text::_($this->config['table_text_2']));

	?>
    <div class="<?php echo $this->divclasscontainer; ?>">
        <div class="<?php echo $this->divclassrow; ?>">
			<?PHP
			echo $this->loadTemplate('ranking_home');
			?>
        </div>
    </div>
	<?PHP
	echo HTMLHelper::_('bootstrap.' . $this->endPanel);
}


if ($this->config['show_table_3'])
{
	echo HTMLHelper::_('bootstrap.' . $this->addPanel, 'myTab', 'tab3id', Text::_($this->config['table_text_3']));

	?>
    <div class="<?php echo $this->divclasscontainer; ?>">
        <div class="<?php echo $this->divclassrow; ?>">
			<?PHP
			echo $this->loadTemplate('ranking_away');
			?>
        </div>
    </div>
	<?PHP
	echo HTMLHelper::_('bootstrap.' . $this->endPanel);
}


if ($this->config['show_table_4'])
{
	echo HTMLHelper::_('bootstrap.' . $this->addPanel, 'myTab', 'tab4id', Text::_($this->config['table_text_4']));

	?>
    <div class="<?php echo $this->divclasscontainer; ?>">
        <div class="<?php echo $this->divclassrow; ?>">
			<?PHP
			echo $this->loadTemplate('ranking_first');
			?>
        </div>
    </div>
	<?PHP
	echo HTMLHelper::_('bootstrap.' . $this->endPanel);
}


if ($this->config['show_table_5'])
{
	echo HTMLHelper::_('bootstrap.' . $this->addPanel, 'myTab', 'tab5id', Text::_($this->config['table_text_5']));

	?>
    <div class="<?php echo $this->divclasscontainer; ?>">
        <div class="<?php echo $this->divclassrow; ?>">
			<?PHP
			echo $this->loadTemplate('ranking_second');
			?>
        </div>
    </div>
	<?PHP
}

echo HTMLHelper::_('bootstrap.' . $this->endPanel);
echo HTMLHelper::_('bootstrap.' . $this->endPane, 'myTab');		    
		    
		    
			echo $this->loadTemplate('jsminfo');
            }
			?>
        </div>
    <!-- </div> -->
	<?PHP
    
/*
echo HTMLHelper::_('bootstrap.' . $this->startPane, 'myTab', array('active' => 'tab1id'));

if ($this->config['show_table_1'])
{
	echo HTMLHelper::_('bootstrap.' . $this->addPanel, 'myTab', 'tab1id', Text::_($this->config['table_text_1']));

	?>
    <div class="<?php echo $this->divclasscontainer; ?>">
        <div class="<?php echo $this->divclassrow; ?>">
			<?PHP
			echo $this->loadTemplate('ranking');
			?>
        </div>
    </div>
	<?PHP
	echo HTMLHelper::_('bootstrap.' . $this->endPanel);
}


if ($this->config['show_table_2'])
{
	echo HTMLHelper::_('bootstrap.' . $this->addPanel, 'myTab', 'tab2id', Text::_($this->config['table_text_2']));

	?>
    <div class="<?php echo $this->divclasscontainer; ?>">
        <div class="<?php echo $this->divclassrow; ?>">
			<?PHP
			echo $this->loadTemplate('ranking_home');
			?>
        </div>
    </div>
	<?PHP
	echo HTMLHelper::_('bootstrap.' . $this->endPanel);
}


if ($this->config['show_table_3'])
{
	echo HTMLHelper::_('bootstrap.' . $this->addPanel, 'myTab', 'tab3id', Text::_($this->config['table_text_3']));

	?>
    <div class="<?php echo $this->divclasscontainer; ?>">
        <div class="<?php echo $this->divclassrow; ?>">
			<?PHP
			echo $this->loadTemplate('ranking_away');
			?>
        </div>
    </div>
	<?PHP
	echo HTMLHelper::_('bootstrap.' . $this->endPanel);
}


if ($this->config['show_table_4'])
{
	echo HTMLHelper::_('bootstrap.' . $this->addPanel, 'myTab', 'tab4id', Text::_($this->config['table_text_4']));

	?>
    <div class="<?php echo $this->divclasscontainer; ?>">
        <div class="<?php echo $this->divclassrow; ?>">
			<?PHP
			echo $this->loadTemplate('ranking_first');
			?>
        </div>
    </div>
	<?PHP
	echo HTMLHelper::_('bootstrap.' . $this->endPanel);
}


if ($this->config['show_table_5'])
{
	echo HTMLHelper::_('bootstrap.' . $this->addPanel, 'myTab', 'tab5id', Text::_($this->config['table_text_5']));

	?>
    <div class="<?php echo $this->divclasscontainer; ?>">
        <div class="<?php echo $this->divclassrow; ?>">
			<?PHP
			echo $this->loadTemplate('ranking_second');
			?>
        </div>
    </div>
	<?PHP
}

echo HTMLHelper::_('bootstrap.' . $this->endPanel);
echo HTMLHelper::_('bootstrap.' . $this->endPane, 'myTab');
*/
?>

<?PHP

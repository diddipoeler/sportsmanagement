<?php
/**
 * SportsManagement hit list compatibility view for Joomla 5/6.
 */

defined('_JEXEC') or die('Restricted access');

class sportsmanagementViewhitlist extends sportsmanagementView
{
    public function init(): void
    {
        $model = $this->getModel();

        $this->tableclass = $this->jinput->getString('table_class', 'table');
        $this->show_project = $this->jinput->getString('show_project', 'table');
        $this->show_club = $this->jinput->getString('show_club', 'table');
        $this->show_team = $this->jinput->getString('show_team', 'table');
        $this->show_person = $this->jinput->getString('show_person', 'table');
        $this->show_playground = $this->jinput->getString('show_playground', 'table');
        $this->max_hits = $this->jinput->getInt('max_hits', 0);

        foreach ([
            'project' => $this->show_project,
            'club' => $this->show_club,
            'team' => $this->show_team,
            'person' => $this->show_person,
            'playground' => $this->show_playground,
        ] as $table => $enabled) {
            if ($enabled) {
                $model->getSportsmanagementHits(null, $this->max_hits, $table);
            }
        }

        $this->model_hits = $model::$_success_text;
    }
}

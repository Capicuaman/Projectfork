<?php
/**
 * @package      Projectfork
 * @subpackage   Milestones
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.view');


class PFmilestonesViewMilestones extends JViewLegacy
{
    protected $items;
    protected $pagination;
    protected $state;
    protected $authors;
    protected $nulldate;


    /**
     * Displays the view.
     *
     */
    public function display($tpl = null)
    {
        // Get data from model
        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state      = $this->get('State');
        $this->authors    = $this->get('Authors');

        // Get database null date
        $this->nulldate = JFactory::getDbo()->getNullDate();

        // Check for errors
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode("\n", $errors));
            return false;
        }

        if ($this->getLayout() !== 'modal') $this->addToolbar();

        parent::display($tpl);
    }


    /**
     * Adds the page title and toolbar.
     *
     */
    protected function addToolbar()
    {
        $access = PFmilestonesHelper::getActions();

        JToolBarHelper::title(JText::_('COM_PROJECTFORK_MILESTONES_TITLE'), 'article.png');

        if ($access->get('core.create')) {
            JToolBarHelper::addNew('milestone.add');
        }

        if ($access->get('core.edit') || $access->get('core.edit.own')) {
            JToolBarHelper::editList('milestone.edit');
        }

        if ($access->get('core.edit.state')) {
            JToolBarHelper::divider();
            JToolBarHelper::publish('milestones.publish', 'JTOOLBAR_PUBLISH', true);
            JToolBarHelper::unpublish('milestones.unpublish', 'JTOOLBAR_UNPUBLISH', true);
            JToolBarHelper::divider();
            JToolBarHelper::archiveList('milestones.archive');
            JToolBarHelper::checkin('milestones.checkin');
        }

        if ($this->state->get('filter.published') == -2 && $access->get('core.delete')) {
            JToolBarHelper::deleteList('', 'milestones.delete','JTOOLBAR_EMPTY_TRASH');
            JToolBarHelper::divider();
        }
        elseif ($access->get('core.edit.state')) {
            JToolBarHelper::trash('milestones.trash');
            JToolBarHelper::divider();
        }

        if ($access->get('core.admin')) {
            JToolBarHelper::preferences('com_pfmilestones');
        }
    }
}

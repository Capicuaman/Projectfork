<?php
/**
 * @package      Projectfork
 * @subpackage   Timetracking
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.view');


class PFtimeViewTimesheet extends JViewLegacy
{
    /**
     * A list of topics
     *
     * @var    array
     */
    protected $items;

    /**
     * JPagination instance
     *
     * @var    object
     */
    protected $pagination;

    /**
     * State object
     *
     * @var    object
     */
    protected $state;

    /**
     * A list of authors
     *
     * @var    array
     */
    protected $authors;

    /**
     *
     * @var    string
     */
    protected $nulldate;


    /**
     * Display the view
     *
     * @param    string    $tpl    A template suffix
     * @retun    void
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
     * Add the page title and toolbar.
     *
     * @return    void
     */
    protected function addToolbar()
    {
        $access = PFtimeHelper::getActions();
        $user  = JFactory::getUser();

        JToolBarHelper::title(JText::_('COM_PROJECTFORK_TIMESHEET_TITLE'), 'article.png');

        if ($access->get('core.create')) {
            JToolBarHelper::addNew('time.add');
        }

        if ($access->get('core.edit') || $access->get('core.edit.own')) {
            JToolBarHelper::editList('time.edit');
        }

        if ($access->get('core.edit.state')) {
            JToolBarHelper::divider();
            JToolBarHelper::publish('timesheet.publish', 'JTOOLBAR_PUBLISH', true);
            JToolBarHelper::unpublish('timesheet.unpublish', 'JTOOLBAR_UNPUBLISH', true);
            JToolBarHelper::divider();
            JToolBarHelper::archiveList('timesheet.archive');
            JToolBarHelper::checkin('timesheet.checkin');
        }

        if ($this->state->get('filter.published') == -2 && $access->get('core.delete')) {
            JToolBarHelper::deleteList('', 'timesheet.delete','JTOOLBAR_EMPTY_TRASH');
            JToolBarHelper::divider();
        }
        elseif ($access->get('core.edit.state')) {
            JToolBarHelper::trash('timesheet.trash');
            JToolBarHelper::divider();
        }

        if ($user->authorise('core.admin')) {
            JToolBarHelper::preferences('com_pftime');
        }
    }
}

<?php
/**
* @package   Projectfork
* @copyright Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL, see license.txt
*
* This file is part of Projectfork.
*
* Projectfork is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
*
* Projectfork is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Projectfork. If not, see <http://www.gnu.org/licenses/gpl.html>.
**/

defined('_JEXEC') or die;

jimport('joomla.application.component.view');


class ProjectforkViewProjects extends JView
{
    protected $items;
	protected $pagination;
	protected $state;
	protected $authors;
    protected $nulldate;


	/**
	 * Displays the view.
	 */
	public function display($tpl = null)
	{
	    // Get data from model
	    $this->items	  = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state	  = $this->get('State');
		$this->authors	  = $this->get('Authors');
        $this->nulldate   = JFactory::getDbo()->getNullDate();

	    // Check for errors
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		if($this->getLayout() !== 'modal') $this->addToolbar();

		parent::display($tpl);
	}


	/**
	 * Adds the page title and toolbar.
	 *
	 */
	protected function addToolbar()
	{
		$acl  = ProjectforkHelper::getActions();
		$user = JFactory::getUser();

		JToolBarHelper::title(JText::_('COM_PROJECTFORK_PROJECTS_TITLE'), 'article.png');

        JToolBarHelper::addNew('project.add');
        JToolBarHelper::editList('project.edit');
        JToolBarHelper::divider();
        JToolBarHelper::publish('projects.publish', 'JTOOLBAR_PUBLISH', true);
		JToolBarHelper::unpublish('projects.unpublish', 'JTOOLBAR_UNPUBLISH', true);
		JToolBarHelper::divider();
		JToolBarHelper::archiveList('projects.archive');
		JToolBarHelper::checkin('projects.checkin');
        JToolBarHelper::deleteList('', 'projects.delete','JTOOLBAR_EMPTY_TRASH');
		JToolBarHelper::divider();
        JToolBarHelper::trash('projects.trash');
		JToolBarHelper::divider();
	}
}
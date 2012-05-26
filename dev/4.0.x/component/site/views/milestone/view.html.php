<?php
/**
* @package   Projectfork
* @copyright Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.php
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

// No direct access
defined('_JEXEC') or die;


jimport('joomla.application.component.view');


/**
 * HTML Milestone View class for the Projectfork component
 *
 */
class ProjectforkViewMilestone extends JView
{
	protected $item;
	protected $params;
	protected $print;
	protected $state;
	protected $user;


	function display($tpl = null)
	{
		// Initialise variables.
		$app		= JFactory::getApplication();
		$user		= JFactory::getUser();
		$userId		= $user->get('id');
		$dispatcher	= JDispatcher::getInstance();

		$this->item	 = $this->get('Item');
		$this->print = JRequest::getBool('print');
		$this->state = $this->get('State');
		$this->user  = $user;

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseWarning(500, implode("\n", $errors));

			return false;
		}


		// Create a shortcut for $item.
		$item = &$this->item;


		// Merge milestone params. If this is single-milestone view, menu params override milestone params
		// Otherwise, milestone params override menu item params
		$this->params = $this->state->get('params');
		$active	      = $app->getMenu()->getActive();
		$temp	      = clone ($this->params);

		// Check to see which parameters should take priority
		if ($active) {
			$currentLink = $active->link;

			if (strpos($currentLink, 'view=milestone') && (strpos($currentLink, '&id='.(string) $item->id))) {
				$item->params->merge($temp);
				// Load layout from active query (in case it is an alternative menu item)
				if (isset($active->query['layout'])) $this->setLayout($active->query['layout']);
			}
			else {
				// Merge the menu item params with the milestone params so that the milestone params take priority
				$temp->merge($item->params);
				$item->params = $temp;

				// Check for alternative layouts (since we are not in a single-milestone menu item)
				if ($layout = $item->params->get('milestone_layout')) $this->setLayout($layout);
			}
		}
		else {
			// Merge so that milestone params take priority
			$temp->merge($item->params);
			$item->params = $temp;

			// Check for alternative layouts (since we are not in a single-milestone menu item)
			if ($layout = $item->params->get('milestone_layout')) $this->setLayout($layout);
		}

		$offset = $this->state->get('list.offset');

		// Check the view access to the milestone (the model has already computed the values).
		if ($item->params->get('access-view') != true && (($item->params->get('show_noauth') != true &&  $user->get('guest') ))) {
		    JError::raiseWarning(403, JText::_('JERROR_ALERTNOAUTHOR'));
			return;
		}

		// Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($this->item->params->get('pageclass_sfx'));

		$this->_prepareDocument();

		parent::display($tpl);
	}


	/**
	 * Prepares the document
     *
	 */
	protected function _prepareDocument()
	{
		$app	 = JFactory::getApplication();
		$menus	 = $app->getMenu();
        $menu    = $menus->getActive();
		$pathway = $app->getPathway();
		$title   = null;

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		if ($menu) {
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else {
			$this->params->def('page_heading', JText::_('COM_PROJECTFORK_MILESTONE'));
		}

		$title = $this->params->get('page_title', '');
		$id    = (int) @$menu->query['id'];

		// If the menu item does not concern this item
		if($menu && ($menu->query['option'] != 'com_projectfork' || $menu->query['view'] != 'milestone' || $id != $this->item->id)) {
			// If this is not a single milestone menu item, set the page title to the milestone title
			if($this->item->title) $title = $this->item->title;

            $pid    = $this->item->project_id;
            $palias = $this->item->project_alias;

			$path   = array(array('title' => $this->item->title, 'link' => ''));
            $path[] = array('title' => $this->item->project_title, 'link' => JRoute::_("index.php?option=com_projectfork&view=dashboard&id=$pid:$palias"));

			$path = array_reverse($path);

			foreach($path as $item)
			{
				$pathway->addItem($item['title'], $item['link']);
			}
		}

		// Check for empty title and add site name if param is set
		if (empty($title)) {
			$title = $app->getCfg('sitename');
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 1) {
			$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 2) {
			$title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
		}
		if (empty($title)) {
			$title = $this->item->title;
		}

		$this->document->setTitle($title);


		if ($this->params->get('robots'))      $this->document->setMetadata('robots', $this->params->get('robots'));
		if ($app->getCfg('MetaAuthor') == '1') $this->document->setMetaData('author', $this->item->author);
		if ($this->print)                      $this->document->setMetaData('robots', 'noindex, nofollow');
	}
}

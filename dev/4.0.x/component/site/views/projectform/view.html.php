<?php
/**
 * @package      Projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.view');


/**
 * Project Form View Class for Projectfork component
 *
 */
class ProjectforkViewProjectForm extends JView
{
    protected $form;
    protected $item;
    protected $return_page;
    protected $state;


    public function display($tpl = null)
    {
        // Initialise variables.
        $app    = JFactory::getApplication();
        $user   = JFactory::getUser();

        // Get model data.
        $state       = $this->get('State');
        $item        = $this->get('Item');
        $form        = $this->get('Form');
        $return_page = $this->get('ReturnPage');

        // Permission check.
        if (empty($item->id)) {
            $access     = ProjectforkHelperAccess::getActions();
            $authorised = $access->get('project.create');
        }
        else {
            $authorised = $item->params->get('access-edit');
        }

        if ($authorised !== true) {
            JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
            return false;
        }

        // Bind form data.
        if (!empty($item)) $form->bind($item);

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseWarning(500, implode("\n", $errors));
            return false;
        }

        // Create a shortcut to the parameters.
        $params = &$state->params;

        //Escape strings for HTML output
        $this->pageclass_sfx = htmlspecialchars($params->get('pageclass_sfx'));

        // Assign references
        $this->assignRef('item',        $item);
        $this->assignRef('state',       $state);
        $this->assignRef('form',        $form);
        $this->assignRef('params',      $params);
        $this->assignRef('return_page', $return_page);
        $this->assignRef('user',        $user);

        // Prepare the document
        $this->_prepareDocument();

        // Display the view
        parent::display($tpl);
    }


    /**
     * Prepares the document
     *
     * @return void
     */
    protected function _prepareDocument()
    {
        $app     = JFactory::getApplication();
        $menus   = $app->getMenu();
        $pathway = $app->getPathway();
        $menu    = $menus->getActive();
        $title   = null;

        // Because the application sets a default page title,
        // we need to get it from the menu item itself
        if ($menu) {
            $this->params->def('page_heading', $this->params->get('page_title', $menu->title));
        }
        else {
            $this->params->def('page_heading', JText::_('COM_PROJECTFORK_FORM_EDIT_PROJECT'));
        }

        $title = $this->params->def('page_title', JText::_('COM_PROJECTFORK_FORM_EDIT_PROJECT'));

        if ($app->getCfg('sitename_pagetitles', 0) == 1) {
            $title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
        }
        elseif ($app->getCfg('sitename_pagetitles', 0) == 2) {
            $title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
        }

        $this->document->setTitle($title);


        $pathway = $app->getPathWay();
        $pathway->addItem($title, '');

        if ($this->params->get('menu-meta_description')) {
            $this->document->setDescription($this->params->get('menu-meta_description'));
        }

        if ($this->params->get('menu-meta_keywords')) {
            $this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
        }

        if ($this->params->get('robots')) {
            $this->document->setMetadata('robots', $this->params->get('robots'));
        }
    }
}

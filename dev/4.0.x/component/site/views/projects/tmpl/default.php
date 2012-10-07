<?php
/**
 * @package      Projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


$list_order = $this->escape($this->state->get('list.ordering'));
$list_dir   = $this->escape($this->state->get('list.direction'));
$user       = JFactory::getUser();
$uid        = $user->get('id');

$filter_in  = ($this->state->get('filter.isset') ? 'in ' : '');
?>
<div id="projectfork" class="category-list<?php echo $this->pageclass_sfx;?> view-projects">
    <?php if ($this->params->get('show_page_heading', 1)) : ?>
        <h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
    <?php endif; ?>

    <div class="clearfix"></div>

    <div class="grid">
        <form name="adminForm" id="adminForm" action="<?php echo JRoute::_(ProjectforkHelperRoute::getProjectsRoute()); ?>" method="post">

            <div class="btn-toolbar btn-toolbar-top">
                <?php echo $this->toolbar;?>
            </div>

            <div class="clearfix"></div>

            <div class="<?php echo $filter_in;?>collapse" id="filters">
                <div class="well btn-toolbar">
                    <div class="filter-search btn-group pull-left">
                        <input type="text" name="filter_search" placeholder="<?php echo JText::_('JSEARCH_FILTER_SEARCH'); ?>" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>"/>
                    </div>
                    <div class="filter-search-buttons btn-group pull-left">
                        <button type="submit" class="btn" rel="tooltip" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>">
                            <i class="icon-search"></i>
                        </button>
                        <button type="button" class="btn" rel="tooltip" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.id('filter_search').value='';this.form.submit();">
                            <i class="icon-remove"></i>
                        </button>
                    </div>

                    <div class="clearfix"> </div>
                    <hr />

                    <div class="filter-category btn-group">
                        <select name="filter_category" class="inputbox input-medium" onchange="this.form.submit()">
                            <option value=""><?php echo JText::_('JOPTION_SELECT_CATEGORY');?></option>
                            <?php echo JHtml::_('select.options', JHtml::_('category.options', 'com_projectfork'), 'value', 'text', $this->state->get('filter.category'));?>
                        </select>
                    </div>
                    <?php if ($this->access->get('project.edit.state') || $this->access->get('project.edit')) : ?>
                        <div class="filter-author btn-group">
                            <select name="filter_published" class="inputbox input-medium" onchange="this.form.submit()">
                                <option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
                                <?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true);?>
                            </select>
                        </div>
                    <?php endif; ?>
                    <div class="clearfix"> </div>
                </div>
            </div>

            <div class="clearfix"></div>

            <ul class="thumbnails">
                <?php
                $k = 0;
                foreach($this->items AS $i => $item) :
                    $access = ProjectforkHelperAccess::getActions('project', $item->id);
                    $link   = ProjectforkHelperRoute::getDashboardRoute($item->slug);

                    $can_create   = $access->get('project.create');
                    $can_edit     = $access->get('project.edit');
                    $can_checkin  = ($user->authorise('core.manage', 'com_checkin') || $item->checked_out == $uid || $item->checked_out == 0);
                    $can_edit_own = ($access->get('project.edit.own') && $item->created_by == $uid);
                    $can_change   = ($access->get('project.edit.state') && $can_checkin);

                    // Calculate project progress
                    $task_count = (int) $item->tasks;
                    $completed  = (int) $item->completed_tasks;
                    $progress   = ($task_count == 0) ? 0 : round($completed * (100 / $task_count));

                    if ($progress >= 67)  $progress_class = 'info';
                    if ($progress == 100) $progress_class = 'success';
                    if ($progress < 67)   $progress_class = 'warning';
                    if ($progress < 34)   $progress_class = 'danger label-important';
                ?>
                <li class="span3">
                    <div class="thumbnail">
                        <?php if (!empty($item->logo_img)) : ?>
                            <a href="<?php echo JRoute::_($link);?>">
                                <img src="<?php echo $item->logo_img;?>" alt="<?php echo $this->escape($item->title);?>" />
                            </a>
                        <?php endif ; ?>
                        <div class="caption">
                            <h3>
                                <?php if ($can_change) : ?>
                                    <label for="cb<?php echo $i; ?>" class="checkbox pull-left">
                                        <?php echo JHtml::_('grid.id', $i, $item->id); ?>
                                    </label>
                                <?php endif; ?>

                                <?php if ($item->checked_out) : ?>
                                    <i class="icon-lock"></i>
                                <?php endif; ?>

                                <a href="<?php echo JRoute::_($link);?>" rel="tooltip" data-placement="bottom">
                                    <?php echo $this->escape($item->title);?>
                                </a>

                                <?php if ($can_edit || $can_edit_own) : ?>
                                <div class="btn-group pull-right">
                                    <a class="btn btn-mini" href="<?php echo JRoute::_('index.php?option=com_projectfork&task=projectform.edit&id=' . $item->slug);?>">
                                        <i class="icon-edit"></i>
                                    </a>
                                </div>
                                <?php endif; ?>
                            </h3>
                            <div class="clearfix"></div>
                            <hr />
                            <div class="progress progress-<?php echo $progress_class;?> progress-striped progress-project">
                                <div class="bar" style="width: <?php echo ($progress > 0) ? $progress."%": "24px";?>">
                                    <span class="label label-<?php echo $progress_class;?> pull-right"><?php echo $progress;?>%</span>
                                </div>
                            </div>
                            <?php echo JHtml::_('projectfork.dateFormat', $item->end_date, $this->params->get('date_format')); ?>
                            <?php echo JHtml::_('projectfork.authorLabel', $item->author_name, $item->created, $this->params->get('date_format')); ?>
                        </div>
                  </div>
                </li>
                <?php
                    $k = 1 - $k;
                    endforeach;
                ?>
            </ul>

            <div class="filters btn-toolbar">
                <?php if ($this->pagination->get('pages.total') > 1) : ?>
                    <div class="btn-group pagination">
                        <p class="counter"><?php echo $this->pagination->getPagesCounter(); ?></p>
                        <?php echo $this->pagination->getPagesLinks(); ?>
                    </div>
                <?php endif; ?>

                <div class="btn-group display-limit">
                    <?php echo JText::_('JGLOBAL_DISPLAY_NUM'); ?>&#160;
                    <?php echo $this->pagination->getLimitBox(); ?>
                </div>
            </div>

            <input type="hidden" name="boxchecked" value="0" />
            <input type="hidden" name="filter_order" value="<?php echo $list_order; ?>" />
            <input type="hidden" name="filter_order_Dir" value="<?php echo $list_dir; ?>" />
            <input type="hidden" name="task" value="" />
            <?php echo JHtml::_('form.token'); ?>
        </form>
    </div>
</div>

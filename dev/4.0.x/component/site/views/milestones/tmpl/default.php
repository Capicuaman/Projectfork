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

defined('_JEXEC') or die;


$list_order = $this->escape($this->state->get('list.ordering'));
$list_dir   = $this->escape($this->state->get('list.direction'));
$user	    = JFactory::getUser();
$uid	    = $user->get('id');

$action_count = count($this->actions);
?>
<div id="projectfork" class="category-list<?php echo $this->pageclass_sfx;?> view-milestones">

    <div class="btn-toolbar">
        <?php if ($this->params->get('show_page_heading', 1)) : ?>
        	<div class="btn-group">
          	  <h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
            </div>
        <?php endif; ?>
        <div class="btn-group">
       	 <?php echo $this->toolbar;?>
        </div>
    </div>
    <div class="clearfix"></div>

    <div class="cat-items">

        <form name="adminForm" id="adminForm" action="<?php echo JRoute::_('index.php?option=com_projectfork&view=milestones'); ?>" method="post">

            <fieldset class="filters btn-toolbar">
				<?php if($this->params->get('filter_fields')) : ?>
					<div class="btn-group pull-right">
						<a data-toggle="collapse" data-target="#filters" class="btn"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?> <span class="caret"></span></a>
					</div>
				<?php endif; ?>
				<div class="filter-project btn-group pull-left">
				    <?php echo JHtml::_('projectfork.filterProject');?>
				</div>
			</fieldset>
			<div class="clearfix"> </div>
			<?php if($this->params->get('filter_fields')) : ?>
				<div class="collapse" id="filters">
					<div class="well btn-toolbar">
					    <div class="filter-search btn-group pull-left">
					        <input type="text" name="filter_search" placeholder="<?php echo JText::_('JSEARCH_FILTER'); ?>" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" />
						</div>
						<div class="filter-search-buttons btn-group pull-left">
					        <button type="submit" class="btn" rel="tooltip" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
					        <button type="button" class="btn" rel="tooltip" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.id('filter_search').value='';this.form.submit();"><i class="icon-remove"></i></button>
					    </div>
					    <?php if ($user->authorise('core.edit.state', 'com_projectfork') || $user->authorize('milestone.edit.state', 'com_projectfork')
					          ||  $user->authorise('core.edit', 'com_projectfork') || $user->authorize('milestone.edit', 'com_projectfork')) : ?>
							<div class="filter-published btn-group pull-left">
							    <select name="filter_published" class="inputbox input-medium" onchange="this.form.submit()">
							        <option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
							        <?php echo JHtml::_('select.options', $this->states,
					                                    'value', 'text', $this->state->get('filter.published'),
					                                    true
					                                   );
					                ?>
							    </select>
							</div>
					    <?php endif; ?>
					    <?php if(intval($this->state->get('filter.project')) != 0 && count($this->authors)) : ?>
					        <div class="filter-author btn-group pull-left">
					            <select id="filter_author" name="filter_author" class="inputbox" onchange="this.form.submit()">
							        <option value=""><?php echo JText::_('JOPTION_SELECT_AUTHOR');?></option>
							        <?php echo JHtml::_('select.options', $this->authors,
					                                    'value', 'text', $this->state->get('filter.author'),
					                                    true
					                                   );
					                ?>
							    </select>
					        </div>
					    <?php endif; ?>
				    </div>
			    </div>
			<?php endif; ?>

			<?php
            $k = 0;
            foreach($this->items AS $i => $item) :
                $asset_name = 'com_projectfork.milestone.'.$item->id;

	            $canCreate	= ($user->authorise('core.create', $asset_name) || $user->authorise('milestone.create', $asset_name));
	            $canEdit	= ($user->authorise('core.edit', $asset_name) || $user->authorise('milestone.edit', $asset_name));
	            $canCheckin	= ($user->authorise('core.manage', 'com_checkin') || $item->checked_out == $uid || $item->checked_out == 0);
	            $canEditOwn	= (($user->authorise('core.edit.own', $asset_name) || $user->authorise('milestone.edit.own', $asset_name)) && $item->created_by == $uid);
	            $canChange	= (($user->authorise('core.edit.state',	$asset_name) || $user->authorise('milestone.edit.state', $asset_name)) && $canCheckin);

                // Calculate milestone progress
                $task_count = (int) $item->tasks;
                $completed  = (int) $item->completed_tasks;
                $progress   = 0;

                if($task_count == 0) {
                    $progress = 0;
                }
                else {
                    $progress = round($completed * (100 / $task_count));
                }

                if($progress >= 66)  $progress_class = 'info';
                if($progress == 100) $progress_class = 'success';
                if($progress < 66)   $progress_class = 'warning';
                if($progress < 33)   $progress_class = 'danger';

            ?>
                <div class="well well-<?php echo $k;?>">
               		<h4>
               			 <div class="list-actions pull-left">
                            <?php
                                $this->menu->start();
                                $this->menu->itemEdit('milestoneform', $item->id, ($canEdit || $canEditOwn));
                                $this->menu->itemTrash('milestones', $i, ($canEdit || $canEditOwn));
                                $this->menu->end();

                                // echo $this->menu->render();
                            ?>
                   		</div>
                   		<i class="icon-map-marker"></i>
                        <a href="<?php echo JRoute::_(ProjectforkHelperRoute::getMilestoneRoute($item->slug, $item->project_slug));?>">
                            <?php if ($item->checked_out) : ?><i class="icon-lock"></i> <?php endif; ?>
                            <?php echo $this->escape($item->title);?>
                        </a>
                        <small>
                        <?php if($this->params->get('milestone_list_col_project')) : ?>
                           		in <a href="<?php echo JRoute::_(ProjectforkHelperRoute::getDashboardRoute($item->project_slug));?>">
                                   <?php echo $this->escape($item->project_title);?>
                                </a>
                        <?php endif; ?>

                        <?php if($this->params->get('milestone_list_col_author')) : ?>
                        	by <?php echo $this->escape($item->author_name);?>
                        <?php endif; ?>
                        <?php if($this->params->get('milestone_list_col_deadline')) : ?>
                            <?php if($item->end_date != $this->nulldate) {
                                echo '<span class="label label-info pull-right"><i class="icon-calendar icon-white"></i> ' . JHtml::_('date', $item->end_date, $this->escape( $this->params->get('deadline_format', JText::_('DATE_FORMAT_LC3')))). '</span>';
                            }
                        		?>
                        <?php endif; ?>
                        </small>
                        <a href="#milestone-<?php echo $item->id;?>" class="btn btn-mini" data-toggle="collapse"><?php echo JText::_('COM_PROJECTFORK_DETAILS_LABEL');?> <span class="caret"></span></a>
               		</h4>
               		<div class="collapse" id="milestone-<?php echo $item->id;?>">
	               		<hr />
	               		<div class="small">
	               			<?php if($this->params->get('milestone_list_col_access')) : ?>
	               			    <span class="label access pull-right">
	               						<i class="icon-user icon-white"></i> <?php echo $this->escape($item->access_level);?>
	               				</span>
	               			<?php endif; ?>
	               			<?php echo $this->escape($item->description);?>
	               			<?php if($this->params->get('milestone_list_col_created')) : ?>
	               				    <span class="list-created">
	               			   	    <?php echo JHtml::_('date', $item->created, $this->escape( $this->params->get('date_format', JText::_('DATE_FORMAT_LC4')))); ?>
	               					</span>
	               			<?php endif; ?>
	               			<?php if($this->params->get('milestone_list_col_sdate')) : ?>
	               				    <span class="list-sdate">
	               			   	    <?php if($item->start_date == $this->nulldate) {
	               			            echo JText::_('COM_PROJECTFORK_DATE_NOT_SET');
	               			        }
	               			        else {
	               			            echo JHtml::_('date', $item->start_date, $this->escape( $this->params->get('sdate_format', JText::_('DATE_FORMAT_LC4'))));
	               			        }
	               			   		?>
	               					</span>
	               			<?php endif; ?>
	               		</div>
	               		<div class="btn-toolbar">
	               			<div class="btn-group">
			                    <?php if($this->params->get('milestone_list_col_tasks')) : ?>
					               		<a class="btn" href="<?php echo JRoute::_(ProjectforkHelperRoute::getTasksRoute($item->project_id.':'.$item->project_alias, $item->id.':'.$item->alias));?>">
			                               <i class="icon-list"></i> <?php echo intval($item->tasklists).' '. JText::_('COM_PROJECTFORK_TASK_LISTS');?>
			                            </a>
			                            <a class="btn" href="<?php echo JRoute::_(ProjectforkHelperRoute::getTasksRoute($item->project_id.':'.$item->project_alias, $item->id.':'.$item->alias));?>">
			                               <i class="icon-ok"></i> <?php echo intval($item->tasks).' '. JText::_('COM_PROJECTFORK_TASKS');?>
			                            </a>
			                    <?php endif; ?>
	                    	</div>
	                    </div>
	                    <div class="clearfix"></div>
                    </div>
                    <hr />
                    <div class="progress progress-<?php echo $progress_class;?> progress-striped progress-milestone" rel="tooltip" title="<?php echo $progress;?>% <?php echo JText::_('COM_PROJECTFORK_FIELD_COMPLETE_LABEL');?>">
                      <div class="bar"
                           style="width: <?php echo $progress;?>%;"><span class="label label-<?php echo $progress_class;?>"><?php echo $progress;?>%</span></div>
                    </div>
               	</div>
            <?php
            $k = 1 - $k;
            endforeach;
            ?>

            <?php if($this->pagination->get('pages.total') > 1 && $this->params->get('show_pagination')) : ?>
                <div class="pagination">
                    <?php if ($this->params->get('show_pagination_results')) : ?>
    				    <p class="counter"><?php echo $this->pagination->getPagesCounter(); ?></p>
    				<?php endif; ?>
    		        <?php echo $this->pagination->getPagesLinks(); ?>
                </div>
            <?php endif; ?>

            <?php if ($this->params->get('show_pagination_limit')) : ?>
                <div class="filter-limit">
                    <?php echo $this->pagination->getLimitBox(); ?>
                </div>
            <?php endif; ?>

            <input type="hidden" name="boxchecked" value="0" />
            <input type="hidden" name="filter_order" value="<?php echo $list_order; ?>" />
	        <input type="hidden" name="filter_order_Dir" value="<?php echo $list_dir; ?>" />
            <input type="hidden" name="task" value="" />
	        <?php echo JHtml::_('form.token'); ?>
        </form>
    </div>
</div>
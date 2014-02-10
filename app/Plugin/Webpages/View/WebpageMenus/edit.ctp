<div class="menus edit">
    <h3><?php echo $this->request->data['WebpageMenu']['name']; ?></h3>
    <p>Drag and drop the menu items, and the position is saved each time you drop.</p>

    <div class="menus edit form">
        <?php
        $this->Tree->addTypeAttribute('data-identifier', $this->request->data['WebpageMenu']['id'], null, 'previous');
        echo $this->Tree->generate($this->request->data['children'], array(
            	'model' => 'WebpageMenu', 
    			'alias' => 'item_text', 
    			'class' => 'sortable sortableMenu '.$this->request->data['WebpageMenu']['type'], 
    			'id' => 'menu' . $this->request->data['WebpageMenu']['id'], 
    			'element' => 'item', 
    			'elementPlugin' => 'webpages')); ?>
        <h5>
        <?php echo $this->Html->link(__('Save'), '', array('class' => 'btn', 'onClick' => 'history.go(-1);return false;')); ?>
        <?php echo $this->Html->link(__('Save & Continue Editing'), '', array('class' => 'btn', 'onClick' => 'window.location.reload()')); ?>
        </h5>
    </div>

    <?php echo $this->Form->create('WebpageMenuItem', array('action' => 'add'));?>
    <fieldset>
 		<legend class="toggleClick"><?php echo __('Add Link to %s', $this->request->data['WebpageMenu']['name']); ?></legend>
  	    <?php
    	echo $this->Form->input('WebpageMenuItem.item_text', array('label' => 'Link Text'));
		echo $this->Form->input('WebpageMenuItem.item_url', array('label' => 'URL'));
		echo $this->Form->input('WebpageMenuItem.item_target', array('label' => 'Target', 'options' => array('', '_blank', '_self', '_parent', '_top')));
    	echo $this->Form->input('WebpageMenuItem.menu_id', array('type' => 'hidden', 'value' => $this->request->data['WebpageMenu']['id']));
		echo $this->Form->input('WebpageMenuItem.order', array('type' => 'hidden', 'value' => $this->request->data['WebpageMenu']['children'] + 1)); 
        echo $this->Form->end(__('Add Link'));?>
    </fieldset>

    <?php echo $this->Form->create('WebpageMenu');?>
	<fieldset>
 		<legend class="toggleClick"><?php echo __('Configure %s Options', $this->request->data['WebpageMenu']['name']); ?></legend>
	    <?php
    	echo $this->Form->input('WebpageMenu.id');
		echo $this->Form->input('WebpageMenu.name');
		echo $this->Form->input('WebpageMenu.type', array('empty' => '-- Optional --')); ?>
        <fieldset>
            <legend class="toggleClick"><?php echo __('Advanced'); ?></legend>
    	    <?php
    		echo $this->Form->input('WebpageMenu.params');
    		echo $this->Form->input('WebpageMenu.css_id', array('type' => 'text', 'label' => 'Css Id'));
    		echo $this->Form->input('WebpageMenu.css_class');
    		echo $this->Form->input('WebpageMenu.order'); ?>
    	</fieldset>
        <?php echo $this->Form->end(__('Submit', true));?>
    </fieldset>
</div>

<?php echo $this->Html->css('/css/jquery-ui/jquery-ui-1.9.2.custom.min'); ?>
<?php echo $this->Html->css('/webpages/menus/css/nestedSortable'); ?>
<?php echo $this->Html->script('/js/jquery-ui/jquery-ui-1.9.2.custom.min'); ?>
<?php echo $this->Html->script('/webpages/menus/js/jquery.ui.nestedSortable'); ?>

<script type="text/javascript">
$(function() {
    // maybe this is for editing item values???
	$('.sortableMenu a').click(function(e) {
		e.preventDefault();
	});

	$('.sortableMenu').nestedSortable({
		forcePlaceholderSize: true,
		listType: 'ul',
		handle: 'div',
		helper: 'clone',
		opacity: .6,
    	placeholder: 'placeholder',
        rootID: '<?php echo $this->request->data['WebpageMenu']['id']; ?>',
		items: "li",
		delay: 100,
		tolerance: 'pointer',
		toleranceElement: '> div',
		update: function(event, ui) {
			//$('#loadingimg').show();
		 	var order = $('ul.sortableMenu').nestedSortable('toArray');
			$.post('/webpages/webpage_menu_items/sort.json', {order:order}, 
				   function(data){
					  	var n = 1;
						$.each(data, function(i, item) {
							$('td.'+item).html(n);
							n++;
						});	
						//$('#loadingimg').hide()
				   }
			);
		}
	});
});
</script>



<?php 
// set the contextual menu items
$this->set('context_menu', array('menus' => array(
    array(
		'heading' => 'Menus',
		'items' => array(
            $this->Html->link(__('All'), array('action' => 'index')),
        	$this->Html->link(__('Edit'), array('action' => 'edit', $this->request->data['WebpageMenu']['id'])),
            $this->Html->link(__('Add'), array('action' => 'add')),
			$this->Html->link(__('Delete'), array('action' => 'delete', $this->Form->value('WebpageMenu.id')), null, __('Are you sure you want to delete the entire menu?'), $this->Form->value('WebpageMenu.name')),
			)
		),
	))); 
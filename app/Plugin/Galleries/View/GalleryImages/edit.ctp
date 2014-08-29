<div class="galleryImages form">
	<h2><?php echo __('Edit ') . $this->request->data['GalleryImage']['caption']; ?></h2>
	<img src="<?php echo $this->request->data['GalleryImage']['dir'] . 'thumb/small/' . $this->request->data['GalleryImage']['filename']; ?>" />	    
	<?php echo $this->Form->create('GalleryImage', array('enctype' => 'multipart/form-data')); ?>
	<fieldset>
		<legend><?php echo __('Edit Image'); ?></legend>
		<?php
		echo $this->Form->input('GalleryImage.id');
		echo $this->Form->input('GalleryImage.gallery_id', array('type' => 'hidden', 'value' => $this->request->data['GalleryImage']['gallery_id']));
		echo $this->Form->input('GalleryImage.filename', array('type' => 'file'));
		echo $this->Form->input('GalleryImage.caption', array('type' => 'text'));
		echo $this->Form->input('GalleryImage.link', array('type' => 'text'));
		echo $this->Form->input('GalleryImage.description', array('type' => 'richtext'));
		echo $this->Form->input('dir', array('type' => 'hidden'));
		echo $this->Form->input('mimetype', array('type' => 'hidden'));
		echo $this->Form->input('filesize', array('type' => 'hidden'));
		echo $this->Form->end('Submit');
		?>
    </fieldset>
</div>

<?php
// set the contextual menu items
$this->set('context_menu', array('menus' => array(
		array(
			'heading' => 'Galleries',
			'items' => array(
				$this->Html->link(__('Gallery', true), array('controller' => 'galleries', 'action' => 'view', $this->request->data['Gallery']['model'], $this->request->data['Gallery']['foreign_key'])),
				$this->Html->link(__('Galleries', true), array('controller' => 'galleries', 'action' => 'index')),
			)
		),
)));

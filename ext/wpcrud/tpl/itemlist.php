<div class="wrap">
	<div class="icon32 icon32-posts-post" id="icon-edit"><br/></div>
	<h2>
		<?php echo $title; ?>
		<?php if ($enableCreate) { ?>
			<a class="add-new-h2" href="<?php echo $addlink ;?>">Add new</a>
		<?php } ?>
	</h2>

	<?php if (!empty($description)) { ?>
		<p><?php echo $description; ?></p>
	<?php } ?>

	<?php if (!empty($notice)): ?>
		<div id="notice" class="error"><p><?php echo $notice ?></p></div>
	<?php endif;?>

	<?php if (!empty($message)): ?>
		<div id="message" class="updated"><p><?php echo $message ?></p></div>
	<?php endif;?>

	<form id="<?php echo $typeId; ?>_form" method="GET"
		action="<?php echo $formAction; ?>">
		<?php foreach ($crudUrlVars as $field=>$value) { ?>
			<input type="hidden" 
				name="<?php echo $field; ?>" 
				value="<?php echo $value; ?>">
		<?php } ?>
		<input type="hidden" name="crudUrl" value="<?php echo $crudUrl; ?>"/>

		<?php $listTable->display(); ?>
	</form>
</div>

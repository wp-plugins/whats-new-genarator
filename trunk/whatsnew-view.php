<div class='whatsnew'>
	<h2
	<?php if (!empty($info->background_color)){
		echo "style='background-color: {$info->background_color};'";
    }?>>
		<?php echo $info->title; ?>
	</h2>

	<dl>
		<?php foreach($info->items as $item): ?>
		<dt>
			<?php echo $item->date; ?>
		</dt>
		<dd>
			<a href="<?php echo $item->url; ?>"><?php echo $item->title; ?> </a>
		</dd>
		<?php endforeach; ?>
	</dl>
</div>

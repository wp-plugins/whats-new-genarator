<div class='whatsnew'>
	<p class='whatsnewtitle'
	<?php if (!empty($info->background_color)){
		echo "style='background-color: {$info->background_color};'";
    }?>>
		<?php echo $info->title; ?>
	</p>

	<dl>
		<?php foreach($info->items as $item): ?>
		<dt>
			<?php echo $item->date; ?>
		</dt>
		<dd>
			<?php if ( $item->newmark ): ?>
			<span class='newmark' >NEW!</span>
			<?php endif; ?>
			<a href="<?php echo $item->url; ?>"><?php echo $item->title; ?> </a>
		</dd>
		<?php endforeach; ?>
	</dl>
</div>

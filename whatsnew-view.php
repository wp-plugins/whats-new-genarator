<div class='whatsnew'>
	<div class='whatsnewtitle' <?php if (!empty($info->background_color)){
		echo "style='background-color: {$info->background_color};'";
    }?>>
		<div class='wntitle'>
			<?php echo $info->title; ?>
		</div>
		<?php if (!empty($info->postlist_url)): ?>
		<div class='all-post'>
			<a href="<?php echo $info->postlist_url; ?>">一覧へ</a>
		</div>
		<?php endif; ?>
	</div>

	<dl>
		<?php foreach($info->items as $item): ?>
		<dt>
			<?php echo $item->date; ?>
		</dt>
		<dd>
			<?php if ( $item->newmark ): ?>
			<span class='newmark'>NEW!</span>
			<?php endif; ?>
			<a href="<?php echo $item->url; ?>"><?php echo $item->title; ?> </a>
		</dd>
		<?php endforeach; ?>
	</dl>
</div>

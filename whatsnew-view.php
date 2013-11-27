<div class='whatsnew'>
	<div class='wn-head'
	<?php echo "style='background-color: {$info->background_color}; color : {$info->font_color};'" ; ?>>
		<div class='wn-title'>
			<?php echo $info->title; ?>
		</div>
		<?php if (!empty($info->postlist_url)): ?>
		<div class='wn-postlist'>
			<a href="<?php echo $info->postlist_url; ?>">一覧へ</a>
		</div>
		<?php endif; ?>
	</div>
	<?php foreach($info->items as $item): ?>
	<div class='wn-item'>
		<div class='wn-date'>
			<?php echo $item->date; ?>
		</div>
		<div class='wn-article'>
			<?php if ( $item->newmark ): ?>
			<span class='newmark'>NEW!</span>
			<?php endif; ?>
			<a href="<?php echo $item->url; ?>"><?php echo $item->title; ?> </a>
		</div>
	</div>
	<?php endforeach; ?>
</div>

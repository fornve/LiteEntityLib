<div>

	<div>
		<div style="width: 45%; float: left;">
		{foreach from=$categories item=category name=category_loop}
			{if $smarty.foreach.category_loop.index==15}</div><div style="width: 48%; float: left;">{/if}
			<div>
				<a href="/Advert/Browse/{$category->id}/{$category->name}/{$category->parent->name}" title="{$category->name}">{$category->parent->name} - <strong>{$category->name}</strong></a>
				<p style="padding: 0 0 20px 5px; margin: 0; font-size: 10px;"><a href="/{$category->advert->id}/{$category->advert->title|replace:'&amp ':'&amp; '}" title="{$category->advert->title}">{$category->advert->title}</a></p>
			</div>
		{/foreach}
		</div>
		<div style="clear: both;"></div>
	</div>
</div>

<div id="tag_cloud">
	<h2 class="flag">Tag cloud</h2>
	{include file=tags.tpl}
</div>


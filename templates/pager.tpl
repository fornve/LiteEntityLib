{if $pager}
{strip}
{assign var=min value=$pager->page*$pager->elements-5-$pager->elements}

<ul class="pager">
		<li class="page_info">Page: {$pager->page} of {$pager->CountPages()} </li>
	{if $pager->max > 1}
		{if $pager->max > 1 && $pager->page > 1}
			<li><span class="inactive"> &lt;&lt; </span><a href="{$pager->self}{$pager->option}">First</a></li>
		{else}
			<li class="inactive">First &lt;&lt; </li>
		{/if}
		{if $pager->page gt 1}
			<li><span class="inactive"> &lt;</span><a href="{$pager->self}/{$pager->page-1}/{$pager->option}">Previous</a></li>
		{else}
			<li class="inactive"> &lt;&nbsp;Previous</li>
		{/if}
	{if $pager->page < 6}
		{section name=pagerloop loop=$pager->elements_loop max=$pager->CountPages() start=1}
			{if 1 neq $smarty.section.pagerloop.index}<li class="divider"><span class="inactive"> | </span></li>{/if}
			{if $pager->page neq $smarty.section.pagerloop.index}
				<li><a href="{$pager->self}/{$smarty.section.pagerloop.index}/{$pager->option}">{$smarty.section.pagerloop.index}</a></li>
			{else}
				<li class="active"><span>{$smarty.section.pagerloop.index}</span></li>
			{/if}
		{/section}
	{else}
		{section name=pagerloop loop=$pager->CountPages()+1 start=$pager->page-5 max=$pager->elements_loop}
			{if $min neq $smarty.section.pagerloop.index}<li class="divider"><span class="inactive"> | </span></li>{/if}
			{if $pager->page neq $smarty.section.pagerloop.index}
				<li><a href="{$pager->self}/{$smarty.section.pagerloop.index}/{$pager->option}">{$smarty.section.pagerloop.index}</a></li>
			{else}
				<li class="active"><span>{$smarty.section.pagerloop.index}</span></li>
			{/if}
		{/section}
	{/if}
		{if $pager->page lt $pager->max/$pager->elements}
				<li><a href="{$pager->self}/{$pager->page+1}/{$pager->order}">Next</a><span class="inactive">&nbsp;&gt; </span></li>
			{if $pager->max gt 1}
				<li><a href="{$pager->self}/{$pager->countPages()}/{$pager->option}">Last</a><span class="inactive"> &gt;&gt; </span></li>
			{/if}
		{else}
				<li class="inactive">&gt; Next</li>
			{if $pager->max gt 1}
				<li class="inactive">&gt;&gt; Last</li>
			{/if}
		{/if}
{/if}
</ul>
{/strip}
{/if}

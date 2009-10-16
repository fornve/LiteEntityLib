{strip}
{assign var=min value=$pager->offset*$pager->elements-5-$pager->elements}

<ul class="pager">
		<li> <strong>Page: {$pager->offset} of {$pager->CountPages()}</strong> </li>
	{if $pager->max > 1}
		{if $pager->max > 1 && $pager->offset > 1}
			<li><a href="{$pager->self}{$pager->option}">First</a> &lt;&lt; </li>
		{else}
			<li>First &lt;&lt; </li>
		{/if}
		{if $pager->offset gt 1}
			<li><a href="{$pager->self}/{$pager->offset-1}/{$pager->option}">Previous</a> &lt;
		{else}
			<li>Previous &lt;&nbsp;
		{/if}
	{if $pager->offset < 6}
		{section name=pagerloop loop=$pager->elements_loop max=$pager->CountPages() start=1}
			{if 1 neq $smarty.section.pagerloop.index} | {/if}
			{if $pager->offset neq $smarty.section.pagerloop.index}
				<li><a href="{$pager->self}/{$smarty.section.pagerloop.index}/{$pager->option}">{$smarty.section.pagerloop.index}</a>
			{else}
				<li><strong>{$smarty.section.pagerloop.index}</strong></li>
			{/if}
		{/section}
	{else}
		{section name=pagerloop loop=$pager->CountPages()+1 start=$pager->offset-5 max=$pager->elements_loop}
			{if $min neq $smarty.section.pagerloop.index} | {/if}
			{if $pager->offset neq $smarty.section.pagerloop.index}
				<li><a href="{$pager->self}/{$smarty.section.pagerloop.index}/{$pager->option}">{$smarty.section.pagerloop.index}</a>
			{else}
				<li><strong>{$smarty.section.pagerloop.index}</strong></li>
			{/if}
		{/section}
	{/if}
		{if $pager->offset lt $pager->max/$pager->elements}
		&nbsp;&gt; <li><a href="{$pager->self}/{$pager->offset+1}/{$pager->order}">Next</a></li>
			{if $pager->max gt 1}
				<li> &gt;&gt; <a href="{$pager->self}/{$pager->countPages()}/{$pager->option}">Last</a></li>
			{/if}
		{else}
		&gt; <li>Next</li>
			{if $pager->max gt 1}
				<li>&gt;&gt; Last</li>
			{/if}
		{/if}
{/if}
</ul>
{/strip}

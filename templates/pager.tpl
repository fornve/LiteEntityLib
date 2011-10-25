{*
 * Copyright (C) 2009 Marek Dajnowski <marek@dajnowski.net>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *}
{if $apply_pager_css}
<style type="text/css">

ul.pager { border:0; margin: 10px 0 0 0; padding:0; }

.pager li
{
	color:#000;
	display:block;
	float:left;
	font-size:11px;
	list-style:none;
	display: inline;
	border:solid 1px #808080;
	margin-right:2px;
	padding: 3px 5px;
}

.pager .previous-off, .pager .next-off
{
	color:#888;
	display:block;
	float:left;
	font-weight:bold;
	margin-right:2px;
	padding:0px 4px;
}

.pager .next a, .pager .previous a
{
	font-weight:bold;
}

.pager li.active
{
	background:#808080;
}

.pager li a, .pager li span
{
	color:#000;
	padding:3px 6px;
	text-decoration:none;
}

.pager li:hover			{ border:solid 1px #a0a0a0; }
.pager .divider			{ display: none; }

</style>
{/if}

{if $pager}
{strip}
{assign var=min value=$pager->page*$pager->elements-5-$pager->elements}

<ul class="pager">
		<li class="page_info">Page: {$pager->page} of {$pager->CountPages()} </li>
	{if $pager->max > 1}
		{if $pager->max > 1 && $pager->page > 1}
			<li><span class="inactive"> &lt;&lt; </span><a href="{$pager->self}/1/{$pager->option}">First</a></li>
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
				<li><a href="{$pager->self}/{$pager->page+1}/{$pager->option}">Next</a><span class="inactive">&nbsp;&gt; </span></li>
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

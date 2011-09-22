<table class="calendar">
<tr>
   <td colspan="7">
      {if $calendar->month == 1}
         {assign var=t_month value=12}
         {math equation='( y - 1 )' y=$calendar->year assign=t_year}
      {else}
         {math equation='( m - 1 )' m=$calendar->month assign=t_month}
         {assign var=t_year value=$calendar->year}
      {/if}

      {if $month == 12}
         {assign var=v_month value=1}
         {math equation='( y + 1 )' y=$calendar->year assign=v_year}
      {else}
         {math equation='( m + 1 )' m=$calendar->month assign=v_month}
         {assign var=v_year value=$calendar->year}
      {/if}

      <a href="{$uri}/{$t_year}/{$t_month}">&lt;&lt;-</a>
      <b>{$calendar->months[$calendar->month]} : {$calendar->year}</b>
      <a href="{$uri}/{$v_year}/{$v_month}">-&gt;&gt;</a>
   </td>
</tr>
<tr>
{foreach from=$calendar->days item=i}
   <td align="center">
      <b>{$i}</b>
   </td>
{/foreach}
</tr>

{foreach from=$calendar->cal item=i}
<tr>
	{foreach from=$i item=o}
		<td>
			{if $o.num == null}
				&nbsp;
			{else}
				{if $calendar->events[ $o.ts ]}
					<strong>{$o.num}</strong>
					{foreach $calendar->events[ $o.ts ] as $event}
						<p>
							{if $event['url']}
								<a href="{$event['url']}">
							{/if}
							{if $event['name']}
								{$event['name']}
							{else}
								{$o.num}
							{/if}
							{if $event['url']}
								</a>
							{/if}
						</p>
					{/foreach}
				{else}
					{$o.num}
				{/if}
				{* you can also use $o.ts for a unix timestamp of this day *}
			{/if}
		</td>
	{/foreach}
</tr>
{/foreach}

</table>

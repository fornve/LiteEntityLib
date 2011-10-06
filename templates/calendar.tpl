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

<table class="calendar">
	<thead>
		<tr class="navigation">
			<td><a href="{$uri}/{$t_year}/{$t_month}"><img src="{$calendar->resources['arrow_left']}" alt="&lt;&lt;-" /></a></td>
			<td colspan="5">
				<strong>{$calendar->months[$calendar->month]} {$calendar->year}</strong>
			</td>
			<td>
				<a href="{$uri}/{$v_year}/{$v_month}"><img src="{$calendar->resources['arrow_right']}" alt="-&gt;&gt;" /></a>
			</td>
		</tr>
		<tr class="week-days">
		{foreach from=$calendar->days item=i}
			<td>{$i}</td>
		{/foreach}
		</tr>
	</thead>

	<tbody>
		{foreach from=$calendar->cal item=i}
			<tr>
				{foreach from=$i item=o}
					<td class="day{if $calendar->events[ $o.ts ]} has-event{/if}">{strip}
						{if $o.num == null}
							&nbsp;
						{else}
							{if $calendar->events[ $o.ts ]}
								{foreach $calendar->events[ $o.ts ] as $calendar_event}
									{if $calendar_event['url']}
										<a href="{$calendar_event['url']}">
									{/if}
									{if $calendar_event['name']}
										{$calendar_event['name']}
									{else}
										{$o.num}
									{/if}
									{if $calendar_event['url']}
										</a>
									{/if}
								{/foreach}
							{else}
								{$o.num}
							{/if}
							{* you can also use $o.ts for a unix timestamp of this day *}
						{/if}
					{/strip}</td>
				{/foreach}
			</tr>
		{/foreach}
	</tbody>

</table>

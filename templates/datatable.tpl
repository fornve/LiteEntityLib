{* This template draws table from result object *}
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

{assign var=object value=$data_objects.0}
{assign var=schema value=$object->GetSchema()}

<table class="datatable">
	<tr class="header">
	{foreach from=$schema item=column}
		<th>{$column}</th>
	{/foreach}
	</tr>

	{foreach from=$data_objects item=row}
	<tr class="item">
		{foreach from=$schema item=element}
			<td>{$row->$element}</td>
		{/foreach}
	</tr>
	{/foreach}

</table>

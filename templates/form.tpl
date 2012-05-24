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

{strip}
<form method="{$form->method}" action="{$form->action}"{if $form->file_upload} enctype="multipart/form-data"{/if} class="autoform {if $form->class}{$form->class}{/if}"{if $form->onsubmit} onsubmit="{$form->onsubmit}"{/if}{if $form->id || $form->jquery_validate} id="{if $form->id}{$form->id}{else}form-{$form->action|md5}{/if}"{/if}>
	<table>
		{foreach from=$form->fields key=name item=field}
		{if $field->type == 'hidden'}
			<input type="hidden" id="{$name}" name="{$name}" value="{if $field->value}{$field->value}{/if}" />
		{else}
			<tr{if $field->row_class} class="{$field->row_class}"{/if}>
			<th>
				<label for="{$name}">{$field->label}</label>
				{$field->label_html}
			</th>
			<td>
				{if $field->type == 'password'}
					<input type="password" id="{$name}" name="{$name}"
						{if $field->class} class="{$field->class}"{/if}
						{if $field->style} style="{$field->style}"{/if}
						{if $field->onclick} class="{$field->onclick}"{/if}
						{if $field->disabled} disabled="disabled"{/if}
					/>
				{elseif $field->type == 'textarea' || $field->type == 'tiny_mce'}
					<textarea name="{$name}" id="{if $field->id}{$field->id}{else}{$name}{/if}"
						{if $field->class} class="{$field->class}"{/if}
						{if $field->disabled} disabled="disabled"{/if}
						{if $field->style} style="{$field->style}"{/if}
						{if $field->onclick} class="{$field->onclick}"{/if}>{if $field->value}{$field->value}{/if}</textarea>

					{if $field->type == 'tiny_mce'}

						<script type="text/javascript">
							tinyMCE.execCommand('mceAddControl', true, "{$name}");
						</script>

					{/if}

				{elseif $field->type == 'select'}
					<select name="{$name}"
						{if $field->class} class="{$field->class}"{/if}
						{if $field->style} style="{$field->style}"{/if}
						{if $field->onclick} class="{$field->onclick}"{/if}
						{if $field->disabled} disabled="disabled"{/if}
						{if $field->multiple} multiple="multiple"{/if}
						>
						{foreach from=$field->options item=option key=value}
							{if $option instanceof Entity}
								<option	value="{$option->getId()}" {if $option->getId()==$field->value || ($field->multiple && in_array($option->getId(), $field->values))} selected="selected"{/if} >{$option->getName()}</option>
							{else}
								<option
									{if $value} value="{$value}"
									{if $value==$field->value || ($field->multiple && in_array($value, $field->values))} selected="selected"{/if}
										{else} value="{$option}"{if $option==$input->$name} selected="selected"{/if}
									{/if}
										>{$option}</option>
							{/if}
						{/foreach}
					</select>
				{elseif $field->type == 'select_country'}
					<select name="{$name}"
						{if $field->class} class="{$field->class}"{/if}
						{if $field->style} style="{$field->style}"{/if}
						{if $field->onclick} class="{$field->onclick}"{/if}
						{if $field->disabled} disabled="disabled"{/if}
						>
						{foreach from=$field->options item=country}
							<option value="{$country->code}"
								{if $country->code==$field->value} selected="selected"{/if}
							>{$country->name}</option>
						{/foreach}
					</select>
				{elseif $field->type == 'checkbox'}
					<input type="{$field->type}" id="{$name}" name="{$name}"
						{if $field->class} class="{$field->class}"{/if}
						{if $field->style} style="{$field->style}"{/if}
						value="{if $field->value}{$field->value}{else}1{/if}"
						{if $field->onclick} class="{$field->onclick}"{/if}
						{if $field->checked} checked="checked"{/if}
						{if $field->disabled} disabled="disabled"{/if}
					/>
				{elseif $field->type == 'radio'}
					<ul>
					{foreach from=$field->options item=option}
						<li{if $option.class} class="{$option.class}"{/if}>
							<input type="{$field->type}" name="{$name}"
								{if $field->class} class="{$field->class}"{/if}
								{if $field->style} style="{$field->style}"{/if}
								value="{$option.value}"
								{if $field->onclick} class="{$field->onclick}"{/if}
								{if $field->value==$option.value} checked="checked"{/if}
							/>
							<span>{$option.label}</span>
						</li>
					{/foreach}
					</ul>
				{elseif $field->type == 'file'}
					<input type="file" id="{$name}" name="{$name}"
						{if $field->class} class="{$field->class}"{/if}
						{if $field->maxlength} maxlength="{$field->maxlength}"{/if}
						{if $field->style} style="{$field->style}"{/if}
						value="{if $field->value}{$field->value}{/if}"
						{if $field->onclick} class="{$field->onclick}"{/if}
						{if $field->disabled} disabled="disabled"{/if}
					/>
				{else}
					<input type="text" id="{$name}" name="{$name}"
						{if $field->class} class="{$field->class}"{/if}
						{if $field->maxlength} maxlength="{$field->maxlength}"{/if}
						{if $field->style} style="{$field->style}"{/if}
						value="{if $field->value}{$field->value}{/if}"
						{if $field->onclick} class="{$field->onclick}"{/if}
						{if $field->disabled} disabled="disabled"{/if}
					/>
				{/if}
				{if $field->description}<br /><span class="field_description">{$field->description}</span>{/if}
				{$field->html}
			</td>
			<td class="error-label">
				{foreach from=$field->error item=error name=error_loop}
					{$error}{if !$smarty.foreach.error_loop.last}<br />{/if}
				{/foreach}
			</td>
		</tr>
		{/if}
		{/foreach}
		<tr{if $form->submit.row_class} class="{$form->submit.row_class}"{/if}>
			<td colspan="3">
				{if $form->submit.type == 'image'}
					<input type="image" src="{$form->submit.src}" alt="{$form->submit.value}"{if $form->submit.class} class="{$form->submit.class}"{/if} />
				{else}
					<input type="submit" value="{$form->submit.value}"{if $form->submit.class} class="{$form->submit.class}"{/if}{if $form->submit.onclick} onclick="{$form->submit.onclick}"{/if}{if $form->submit.style} style="{$form->submit.style}"{/if} />
				{/if}
				{$form->submit.html}
			</td>
		</tr>
	</table>
</form>
{/strip}

{if $form->jquery_validate}
<script type="text/javascript">
	jQuery(document).ready(function(){
		jQuery('#{if $form->id}{$form->id}{else}form-{$form->action|md5}{/if}').validate();
	});
</script>
{/if}

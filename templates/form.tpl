{strip}
<form method="{$form->method}" action="{$form->action}"{if $form->file_upload} enctype="multipart/form-data"{/if} class="autoform {if $form->class}{$form->class}{/if}"{if $form->onsubmit} onsubmit="{$form->onsubmit}"{/if}{if $form->id} id="{$form->id}"{/if}>
	<table>
		{foreach from=$form->fields key=name item=field}
		{if $field->type == 'hidden'}
			<input type="hidden" id="{$name}" name="{$name}" value="{if $field->value}{$field->value}{/if}" />
		{else}	
		<tr{if $field->error|@count} class="error"{/if}>
			<th>
				<label for="{$name}">{$field->label}</label>
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
						>
						{foreach from=$field->options item=option key=value}
							<option
								{if $value} value="{$value}"
								{if $value==$field->value} selected="selected"{/if}
									{else} value="{$option}"{if $option==$input->$name} selected="selected"{/if}
								{/if}
									>{$option}</option>
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
			</td>
			<td>
				{foreach from=$field->error item=error name=error_loop}
					{$error}{if !$smarty.foreach.error_loop.last}<br />{/if}
				{/foreach}
			</td>
		</tr>
		{/if}
		{/foreach}
		<tr>
			<td>
				{if $form->submit.type == 'image'}
					<input type="image" src="{$form->submit.src}" alt="{$form->submit.value}"{if $form->submit.class} class="{$form->submit.class}"{/if} />
				{else}
					<input type="submit" value="{$form->submit.value}"{if $form->submit.class} class="{$form->submit.class}"{/if}{if $form->submit.onclick} onclick="{$form->submit.onclick}"{/if}{if $form->submit.style} style="{$form->submit.style}"{/if} />
				{/if}
			</td>
		</tr>
	</table>
</form>
{/strip}

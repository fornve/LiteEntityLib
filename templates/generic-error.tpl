<div class="post">

	<h2 class="title">{$error}</h2>

	<div class="post_content">

		<p>We are sorry for any inconvenience.</p>

		{if !$config->get('production')}
		<table class="datatable">
			<thead>
				<tr>
					<th></th>
					<th>File</th>
					<th>Line</th>
					<th>Class</th>
					<th></th>
					<th>Function</th>
					<th>Args</th>

				</tr>
			</thead>
			<tbody>
				{foreach $error_details->getTrace() as $index => $path}
				<tr>
					<td>{$index+1}</td>
					<td>{$path['file']}</td>
					<td>{$path['line']}</td>
					<td>{$path['class']}</td>
					<td>{$path['type']}</td>
					<td>{$path['function']}</td>
					<td>{$path['args']|var_dump}</td>
				</tr>
				{/foreach}
			</tbody>
		</table>
		{/if}
	</div>
</div>
<div class="post_close"></div>

{use_macro file="Dataface_Record_Template.html"}
{fill_slot name="record_content"}



<table border="0" cellpadding="1" cellspacing="1" style="width: 8in;">
	<tbody>
		<tr>
			<td style="vertical-align: top;">
				<strong><span style="font-size: 24px;">Airite Air Conditioning, Inc.</span></strong><br>
				5334 West Crenshaw St.<br>
				Tampa, FL 33624<br>
				Phone: 813-886-2591<br>
				Fax: 813-886-6651<br>
				<br>

				<span style="font-size: 14px;">BILL TO:</span>
				{$customer_id}<br>
				{block name="billing_address"}
			</td>

			<td style="vertical-align: top; text-align: left; width: 20%;">
				<p>
					<strong><span style="font-size:16px;">SERVICE INVOICE</span></strong></p>
				<table border="0" cellpadding="1" cellspacing="1" style="width: 200px;">
					<tbody>
						<tr>
							<td>
								Invoice #</td>
							<td>
								{$job_id}</td>
						</tr>
						<tr>
							<td>
								Date</td>
							<td>
								(Today&#39;s date?)</td>
						</tr>
					</tbody>
				</table>
				<p>
					&nbsp;</p>
				<p>
					&nbsp;</p>
				<p>
					For Service Performed At:</p>
				<p>
					&lsaquo;{$site_id}</p>
			</td>
		</tr>
	</tbody>
</table>
<p>
	&nbsp;</p>
<table border="1" cellpadding="1" cellspacing="1" style="width: 8in;">
	<thead>
		<tr>
			<th scope="col">
				Description</th>
			<th scope="col">
				Hours</th>
			<th scope="col">
				Rate</th>
			<th scope="col">
				Amount</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>
				&nbsp;</td>
			<td>
				&nbsp;</td>
			<td>
				&nbsp;</td>
			<td>
				&nbsp;</td>
		</tr>
	</tbody>
</table>
<p>
	&nbsp;</p>
<p>
	&nbsp;</p>








{/fill_slot}
{/use_macro}
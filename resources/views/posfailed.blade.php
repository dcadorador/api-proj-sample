<style type="text/css">
.tg  {border-collapse:collapse;border-spacing:0;}
.tg td{font-family:Arial, sans-serif;font-size:14px;padding:10px 5px;border-style:none;border-width:1px;overflow:hidden;word-break:normal;}
.p {font-family:Arial, sans-serif;}
.tg th{font-family:Arial, sans-serif;font-size:14px;font-weight:normal;padding:10px 5px;border-style:none;border-width:1px;overflow:hidden;word-break:normal;}
.tg .tg-yw4l{vertical-align:top}
</style>
<table width="600px">
		<tr>
			<td>
				<table class="sub-header" width="100%">
					<tr>
						<td>
                            <p class="p">Hello, {{  $customer_name }}</p>
                            
                            <p class="p">Unfortunately, your order below was not processed due to a system error, and payment transaction was cancelled.</p>
                                
                            <p class="p">We are very sorry for any inconvenience.</p>
                        
                            <p class="p">For any inquries or concerns, you may email us at {{ $concept_email }}.</p>

                            <p class="p">Thank you.</p>
                            
                            <p class="p">Regards,</p>
                            <p class="p">{{ ucwords($concept_label) }}</p><br/>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td>
			<table class="tg" border="0" style="undefined;table-layout: fixed; width: 373px">
            <colgroup>
            <col style="width: 176px">
            <col style="width: 197px">
            </colgroup>
            <tr>
                                            				 <td class="tg-yw4l">ORDER ITEMS:</td>
                                            				 <td class="tg-yw4l"></td>
                                            				</tr>
            					        @foreach ($order_items as $o)
                                				<tr>
                                				 <td class="tg-yw4l">Item #{{ $loop->iteration }}:</td>
                                				 <td class="tg-yw4l">{{ $o->item->translate('en-us')->name }}</td>
                                				</tr>
                                				<tr>
            									    <td class="tg-yw4l">Quantity:</td>
            									    <td class="tg-yw4l">{{ $o->quantity }}</td>
            									</tr>
            									<tr>
            									    <td class="tg-yw4l">Price:</td>
            									    <td class="tg-yw4l">{{ $o->price }}</td>
            									</tr>
                                			</tr>
                                		@endforeach
            </table>
				<!--<table class="content" cellspacing="0" width="100%">
					<tr>
						<th>
							<p class="align-left">ITEM</p>
						</th>
						<th></th>
						<th>
							<p class="align-right">QTY</p>
						</th>
						<th>
							<p class="align-right">PRICE</p>
						</th>
					</tr>

					<tr>
						<td colspan="3" class="spacer"></td>
					</tr>

					        @foreach ($order_items as $o)
                    			<tr>
                    				<td class="order-item">
                    					<p class="align-left">{{ $o->item->translate('en-us')->name }}</p>
                    				</td>
                    				<td class="order-item"></td>
                    				<td class="order-item">
                    					<p class="align-right">{{ $o->quantity }}</p>
                    				</td>
                    				<td class="order-item">
                    					<p class="align-right">{{ $o->price }}</p>
                    				</td>
                    			</tr>
                    		@endforeach

					<tr>
						<td colspan="4" class="spacer"></td>
					</tr>

				</table>-->
			</td>
		</tr>
		<tr>
			<td>
			</td>
		</tr>
	</table>

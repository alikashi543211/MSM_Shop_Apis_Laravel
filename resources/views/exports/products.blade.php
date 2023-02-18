<table>
    <thead>
        <tr>
            <th>Title</th>
            <th>Description</th>
            <th>Publish Date</th>
            <th>Expire Date</th>
            <th>Stock</th>
            <th>Active</th>
            <th>Show Buying Options</th>
            <th>Show Add to Cart</th>
            <th>Attributes</th>
            <th>MB SKU</th>
            <th>MB Location</th>
            <th>MB Landed Cost</th>
            <th>MB Stock</th>
            <th>MB Discount Type</th>
            <th>MB Discount Value</th>
            <th>Merchant Name</th>
            <th>Merchant Link</th>
            <th>Merchant Retail Cost</th>
            <th>Merchant Duty</th>
            <th>Merchant Wharfage</th>
            <th>Merchant Shipping Cost</th>
            <th>Merchant Fuel Adjustment</th>
            <th>Merchant Insurance</th>
            <th>Merchant Estimated Landed Cost</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($products as $item)
            <tr>
                <td>{{ $item->title }}</td>
                <td>{{ $item->description }}</td>
                <td>
                    @if(isset($item->published_at))
                        {{ date("Y-m-d", strtotime($item->published_at)) }}
                    @endif
                </td>
                <td>
                    @if(isset($item->expired_at))
                        {{ date("Y-m-d", strtotime($item->published_at)) }}
                    @endif
                </td>
                <td>{{ $item->in_stock }}</td>
                <td>{{ $item->is_active == 1 || $item->is_active == true ? 'TRUE' : 'FALSE' }}</td>
                <td>{{ $item->show_buying_options == 1 || $item->show_buying_options == true ? 'TRUE' : 'FALSE' }}</td>
                <td>{{ $item->is_buy_now == 1 || $item->is_buy_now == true ? 'TRUE' : 'FALSE' }}</td>
                <td>{{ $item->product_attributes_formatted ?? "" }}</td>
                <td>{{ $item->mb_sku ?? "" }}</td>
                <td>{{ $item->mb_location ?? "" }}</td>
                <td>{{ $item->mb_landed_cost ?? "" }}</td>
                <td>{{ $item->mb_stock ?? "" }}</td>
                <td>{{ $item->mb_discount_type ?? "" }}</td>
                <td>{{ $item->mb_discount_value ?? "" }}</td>
                <td>{{ $item->merchant_name ?? "" }}</td>
                <td>{{ $item->merchant_link ?? "" }}</td>
                <td>{{ $item->merchant_retail_cost ?? "" }}</td>
                <td>{{ $item->merchant_duty ?? "" }}</td>
                <td>{{ $item->merchant_wharfage ?? "" }}</td>
                <td>{{ $item->merchant_shipping_cost ?? "" }}</td>
                <td>{{ $item->merchant_fuel_adjustments ?? "" }}</td>
                <td>{{ $item->merchant_insurance ?? "" }}</td>
                <td>{{ $item->merchant_estimated_landed_cost ?? "" }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

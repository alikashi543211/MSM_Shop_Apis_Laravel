@if (isset($changes['product_id']))

@php
$previousProduct = \App\Models\Product::find($original['product_id']);
$newProduct = \App\Models\Product::find($changes['product_id']);
@endphp
@endif

@if (isset($changes['customer_id']))

@php
$previousCustomer = \App\Models\Customer::find($original['customer_id']);
$newCustomer = \App\Models\Customer::find($changes['customer_id']);
@endphp
@endif
@foreach ($changes as $key => $change)
@if ($key == 'on_hold')
@endif
@php
$column = ucwords(implode(' ',explode('_',$key)));
$previousAttributes = json_decode(json_encode($original['attributes']),true);
@endphp

@if ($key == 'product_id')

<h4>Product</h4>
<p class="timeline-job-note-details">{{ $previousProduct->name }} <i class="fa fa-arrow-right mx-1"></i> {{ $newProduct->name }}</p>

@elseif ($key == 'customer_id')

<h4>Customer</h4>
<p class="timeline-job-note-details">{{ $previousCustomer->name }} <i class="fa fa-arrow-right mx-1"></i> {{ $newCustomer->name }}</p>

@elseif ($key == 'fsc')

<h4>{{ $column }}</h4>
<p class="timeline-job-note-details">{{ $original[$key] ? 'Yes' : 'No' }} <i class="fa fa-arrow-right mx-1"></i> {{ $change ? 'Yes' : 'No' }}</p>

@elseif((isset($changes['product_id']) && $key == 'attributes') || $key == 'updated_at')
@continue
@elseif((!isset($changes['product_id']) && $key == 'attributes'))

@foreach (json_decode($change,true) as $attrKey => $attr)
@if($attr['checked'] !== $previousAttributes[$attrKey]['checked'])
<h4>{{ $attr['name'] }}</h4>
<p class="timeline-job-note-details">{{ $previousAttributes[$attrKey]['checked'] ? 'Yes' : 'No' }} <i class="fa fa-arrow-right mx-1"></i> {{ $attr['checked'] ? 'Yes' : 'No' }}</p>
@endif

@endforeach

@else

<h4>{{ $column }}</h4>
<p class="timeline-job-note-details">{{ isset($original[$key]) ? $original[$key] : 'Added' }} <i class="fa fa-arrow-right mx-1"></i> {{ $change }}</p>

@endif

@endforeach

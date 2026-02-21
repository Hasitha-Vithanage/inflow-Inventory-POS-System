@component('mail::message')
# Your Order is on its way!

Hi **{{ $sale->client->name }}**,

Great news! Your order **{{ $sale->Ref }}** has just been shipped and is on its way to you.

@component('mail::panel')
**Shipping Company:** {{ $sale->shipping_company ? $sale->shipping_company->name : 'Our Courier' }}<br>
**Tracking Number:** {{ $sale->tracking_number ? $sale->tracking_number : 'Not available' }}
@endcomponent

If you have any questions or concerns regarding your shipment, please feel free to reply to this email.

Thanks,<br>
{{ config('app.name') }}
@endcomponent

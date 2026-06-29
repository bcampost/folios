<x-mail::message>
# <center style="text-transform: uppercase">{{ $folio->type === 'previo' ? 'Previo' : 'Folio' }} Rechazado</center>

<span style="font-weight: 500">Razón del Rechazo:</span> {{ $folio->reason_for_rejection }}

<span style="font-weight: 500">Asesor:</span> {{ $folio->project->owner->name }}

<span style="font-weight: 500">Monto:</span> {{  $folio->project->value }}

<span style="font-weight: 500">Modelo Base:</span> {{ $folio->product->sku}}

<span style="font-weight: 500">Descripción:</span> {{ $folio->description }}

@if($folioUrl)
<x-mail::button :url="$folioUrl">
Ver
</x-mail::button>
@endif

</x-mail::message>

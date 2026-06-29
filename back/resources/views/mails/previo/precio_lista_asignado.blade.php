<x-mail::message>
# <center style="text-transform: uppercase">Precio de Lista Asignado</center>

<span style="font-weight: 500">Precio De Lista:</span> ${{ number_format($folio->list_price, 2) }}

<span style="font-weight: 500">Detalles del Costo:</span> {{ $folio->cost_details }}

<span style="font-weight: 500">Asesor:</span> {{ $folio->project->owner->name }}

<span style="font-weight: 500">Monto:</span> {{  $folio->project->value }}

<span style="font-weight: 500">Descripción:</span> {{ $folio->description }}

<x-mail::button :url="$folioUrl">
Ver
</x-mail::button>

</x-mail::message>

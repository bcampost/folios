<x-mail::message>
# <center style="text-transform: uppercase">{{ $project->folios->count() == 1 ? 'Previo' : 'Previos' }} Aprobado</center>
@foreach ($project->folios as $folio)
<div style="border-bottom: 1px solid #eaeaea; border-top: 1px solid #eaeaea; margin-bottom: 32px; padding-top: 16px">

<span style="font-weight: 500">Asesor:</span> {{ $project->owner->name }}

<span style="font-weight: 500">Monto:</span> {{ $project->value }}

@if($folio->classification !== 'D')
<span style="font-weight: 500">Modelo Base:</span> {{ $folio->product?->sku}}
@endif

<span style="font-weight: 500">Descripción:</span> {{ $folio->description }}

<x-mail::button :url="$folioUrl . $folio->id">
Ver
</x-mail::button>
</div>
@endforeach
</x-mail::message>

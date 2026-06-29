<x-mail::message>
@if($folio instanceof App\Models\Folio)
# <center style="text-transform: uppercase">Previo Aprobado</center>

<span style="font-weight: 500">Asesor:</span> {{ $folio->project->owner->name }}

<span style="font-weight: 500">Monto:</span> {{  $folio->project->value }}

@if($folio->classification !== 'D')
<span style="font-weight: 500">Modelo Base:</span> {{ $folio->product?->sku}}
@endif

<span style="font-weight: 500">Descripción:</span> {{ $folio->description }}

<x-mail::button :url="$folioUrl . $folio->id">
Ver
</x-mail::button>
@else
# <center style="text-transform: uppercase">{{ $folio->count() == 1 ? 'Previo' : 'Previos' }} Aprobado</center>
@foreach ($folio as $xfolio)
<div style="border-bottom: 1px solid #eaeaea; border-top: 1px solid #eaeaea; margin-bottom: 32px; padding-top: 16px">

<span style="font-weight: 500">Asesor:</span> {{ $xfolio->project->owner->name }}

<span style="font-weight: 500">Monto:</span> {{  $xfolio->project->value }}

@if($xfolio->classification !== 'D')
<span style="font-weight: 500">Modelo Base:</span> {{ $xfolio->product?->sku}}
@endif

<span style="font-weight: 500">Descripción:</span> {{ $xfolio->description }}

<x-mail::button :url="$folioUrl . $xfolio->id">
Ver
</x-mail::button>
</div>
@endforeach
@endif
</x-mail::message>

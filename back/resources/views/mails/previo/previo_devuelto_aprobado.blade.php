<x-mail::message>
@if($folio instanceof App\Models\Folio)
# <center style="text-transform: uppercase">Previo Devuelto A Aprobado</center>

<span style="font-weight: 500">Asesor:</span> {{ $folio->project->owner->name }}

<span style="font-weight: 500">Monto:</span> {{  $folio->project->value }}

<span style="font-weight: 500">Comentarios:</span> {{ $folio->comments }}


<x-mail::button :url="$folioUrl">
Ver
</x-mail::button>
@else
# <center style="text-transform: uppercase">{{ $folio->count() == 1 ? 'Previo' : 'Previos' }} Devuelto A Aprobado</center>
@foreach ($folio as $xfolio)
<div style="border-bottom: 1px solid #eaeaea; border-top: 1px solid #eaeaea; margin-bottom: 32px; padding-top: 16px">

<span style="font-weight: 500">Asesor:</span> {{ $xfolio->project->owner->name }}

<span style="font-weight: 500">Comentarios:</span> {{ $xfolio->comments }}

<x-mail::button :url="$folioUrl">
Ver
</x-mail::button>
</div>
@endforeach
@endif
</x-mail::message>

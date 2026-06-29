<?php

namespace App\Models;

use App\Enums\FolioTypeEnum;
use Carbon\CarbonInterface;
use App\States\Folio\FolioState;
use Spatie\MediaLibrary\HasMedia;
use Spatie\ModelStates\HasStates;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\InteractsWithMedia;
use Awobaz\Compoships\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\States\Folio\FolioAprobadoState;
use App\States\Folio\LiberadoParaProduccionState;

class Folio extends Model implements HasMedia
{
    use HasFactory, HasStates, InteractsWithMedia, LogsActivity, SoftDeletes;

    protected $casts = [
        'state'            => FolioState::class,
        'type'             => FolioTypeEnum::class,
        'cost'             => 'float',
        'list_price'       => 'float',
        'screw_kits'       => 'array',
        'is_existing_folio' => 'boolean',
    ];

    protected static $recordEvents = ['updated'];

    public function transitions() : HasMany
    {
        return $this->hasMany(FolioTransition::class)->oldest();
    }

    public function lastTransition() : HasOne
    {
        return $this->hasOne(FolioTransition::class)->latest();
    }

    public function project() : BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function owner() : HasOneThrough
    {
        return $this->hasOneThrough(
            User::class,
            Project::class,
            'id',
            'id',
            'project_id',
            'owner_id'
        );
    }

    public function product() : BelongsTo
    {
        return $this->belongsTo(Product::class, 'reference_product');
    }

    public function category() : BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function mediaReferenceFiles(): MorphMany
    {
        return $this->media()->where('collection_name', 'previo_image_reference');
    }

    public function assemblySiblings() : HasMany
    {
        return $this->hasMany(
            Folio::class,
            ['project_id', 'assembly_number'],
            ['project_id', 'assembly_number']
        )
        ->whereNotNull('assembly_number')
        ->where('id', '!=', $this->id);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(
                collect($this->getAttributes())
                    ->except([
                        'created_at',
                        'updated_at',
                        'state',
                    ])
                    ->keys()
                    ->toArray()
            )
            ->logExcept(['created_at', 'updated_at'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function isAssembly() : bool
    {
        return $this->assembly_number !== null;
    }

    public function generatePrevioCode(): string
    {
        if ($this->code_number) {
            return sprintf('P%s-%d', $this->classification, $this->code_number);
        }

        return sprintf('P%s-%s', $this->classification, $this->id);
    }

    public function generateFolioCode(): string
    {
        if ($this->code_number) {
            return sprintf('F%s-%d', $this->classification, $this->code_number);
        }

        if ($this->previo_code) {
            return preg_replace('/^P/', 'F', $this->previo_code, 1);
        }

        return sprintf('F%s-%s', $this->classification, $this->id);
    }

    public function assignCodeByType(): void
    {
        if ($this->type === FolioTypeEnum::Previo) {
            if (!$this->previo_code) {
                $this->code_number = $this->code_number ?: FolioCodeSequence::getNextNumber('previo');
                $this->previo_code = $this->generatePrevioCode();
            }
        }

        if ($this->type === FolioTypeEnum::Folio) {
            $hadPrevioCode = (bool) $this->previo_code;

            if (!$this->previo_code) {
                $this->code_number = $this->code_number ?: FolioCodeSequence::getNextNumber('previo');
                $this->previo_code = $this->generatePrevioCode();
            }

            if (!$this->folio_code && $hadPrevioCode) {
                $this->code_number = FolioCodeSequence::getNextNumber('folio');
                $this->folio_code = $this->generateFolioCode();
            }
        }
    }

    public function getSuggestedFolioCode(): string
    {
        $nextNumber = FolioCodeSequence::peekNextNumber('folio');

        return sprintf('F%s-%d', $this->classification, $nextNumber);
    }

    public function hasNewCodeFormat(): bool
    {
        return $this->code_number !== null;
    }

    public function getDeliveryTiming(): ?array
    {
        $startedAt = $this->getTransitionedAt(FolioAprobadoState::getStateId());

        if (! $startedAt) {
            return null;
        }

        $completedAt = $this->getTransitionedAt(LiberadoParaProduccionState::getStateId());
        $endedAt = $completedAt ?? now();
        $targetDays = $this->getDeliveryTargetDays();
        $isLate = $targetDays !== null
            ? $endedAt->greaterThan($startedAt->copy()->addDays($targetDays))
            : false;
        $isCompleted = $completedAt !== null;

        return [
            'started_at' => $startedAt,
            'completed_at' => $completedAt,
            'elapsed_days' => $this->calculateElapsedDays($startedAt, $endedAt),
            'elapsed_label' => $this->formatElapsedLabel($startedAt, $endedAt),
            'target_days' => $targetDays,
            'count_mode' => config('folios.delivery_timing.count_mode', 'calendar_days'),
            'is_completed' => $isCompleted,
            'is_late' => $isLate,
            'status' => $this->resolveDeliveryTimingStatus($isCompleted, $isLate),
        ];
    }

    protected function getTransitionedAt(int $nextStateId): ?CarbonInterface
    {
        $transition = $this->relationLoaded('transitions')
            ? $this->transitions->firstWhere('next_state_id', $nextStateId)
            : $this->transitions()->where('next_state_id', $nextStateId)->oldest()->first();

        return $transition?->created_at;
    }

    protected function getDeliveryTargetDays(): ?int
    {
        return config('folios.delivery_timing.days_by_classification.' . $this->classification);
    }

    protected function calculateElapsedDays(CarbonInterface $startedAt, CarbonInterface $endedAt): int
    {
        $elapsedSeconds = max(0, $startedAt->diffInSeconds($endedAt, false));

        if ($elapsedSeconds === 0) {
            return 0;
        }

        return (int) ceil($elapsedSeconds / 86400);
    }

    protected function formatElapsedLabel(CarbonInterface $startedAt, CarbonInterface $endedAt): string
    {
        $elapsedSeconds = max(0, $startedAt->diffInSeconds($endedAt, false));

        if ($elapsedSeconds < 86400) {
            return 'Menos de 1 dia';
        }

        $elapsedDays = (int) ceil($elapsedSeconds / 86400);

        return sprintf('%d dia%s', $elapsedDays, $elapsedDays === 1 ? '' : 's');
    }

    protected function resolveDeliveryTimingStatus(bool $isCompleted, bool $isLate): string
    {
        if ($isCompleted) {
            return $isLate ? 'completed_late' : 'completed_on_time';
        }

        return $isLate ? 'late' : 'on_time';
    }
}

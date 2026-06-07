@extends('layouts.app')

@section('title', 'Mon Journal de Stage')

@section('content')
@php
    use Carbon\Carbon;

    $today        = now()->toDateString();
    $prevWeek     = $weekStart->copy()->subWeek()->toDateString();
    $nextWeek     = $weekStart->copy()->addWeek()->toDateString();
    $isCurrentWk  = $weekStart->isSameWeek(now());

    // Build the 5 weekdays (Mon–Fri) to show
    $days = [];
    for ($i = 0; $i < 5; $i++) {
        $date      = $weekStart->copy()->addDays($i);
        $dateStr   = $date->format('Y-m-d');
        $log       = $logs->get($dateStr);
        $isPast    = $date->lte(now()->endOfDay());
        $isToday   = $dateStr === $today;
        $isFuture  = !$isPast;
        $days[]    = compact('date', 'dateStr', 'log', 'isToday', 'isFuture');
    }

    $frDays = ['Lundi','Mardi','Mercredi','Jeudi','Vendredi'];
@endphp

{{-- ── Page header ───────────────────────────────────────────── --}}
<div class="d-flex align-items-center justify-content-between gap-3 mb-4 flex-wrap">
    <div>
        <h1 class="h4 mb-0 d-flex align-items-center gap-2">
            📓 Mon Journal de Stage
        </h1>
        <p class="text-muted small mb-0 mt-1">
            Documentez votre présence et vos activités quotidiennes.
            Ces données alimentent le rapport hebdomadaire IA de votre encadrant.
        </p>
    </div>

    {{-- Week navigation --}}
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('daily-log.index', ['week' => $prevWeek]) }}"
           class="btn btn-outline-secondary btn-sm px-3" title="Semaine précédente">←</a>

        <span class="fw-semibold px-2 text-nowrap">
            {{ $weekStart->isoFormat('D MMM') }} – {{ $weekStart->copy()->addDays(4)->isoFormat('D MMM YYYY') }}
            @if($isCurrentWk) <span class="badge text-bg-primary ms-1 fw-normal" style="font-size:.7rem">Cette semaine</span> @endif
        </span>

        <a href="{{ route('daily-log.index', ['week' => $nextWeek]) }}"
           class="btn btn-outline-secondary btn-sm px-3"
           @if($isCurrentWk) style="pointer-events:none;opacity:.4" @endif
           title="Semaine suivante">→</a>
    </div>
</div>

{{-- ── Daily cards grid ──────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    @foreach($days as $i => $day)
    @php
        $isPresent = $day['log'] ? $day['log']->is_present : true;
        $note      = $day['log']?->daily_note ?? '';
        $hasLog    = $day['log'] !== null;
        $cardClass = $day['isToday'] ? 'border-primary shadow-sm' : '';
    @endphp
    <div class="col-12 col-md-6 col-xl">
        <div class="card card-soft h-100 daily-card {{ $cardClass }}"
             data-date="{{ $day['dateStr'] }}">

            {{-- Card header --}}
            <div class="card-header d-flex justify-content-between align-items-center py-2"
                 style="background:transparent;border-bottom:1px solid rgba(0,0,0,.06)">
                <div>
                    <div class="fw-bold small">{{ $frDays[$i] }}</div>
                    <div class="text-muted" style="font-size:.78rem">
                        {{ $day['date']->isoFormat('D MMM') }}
                    </div>
                </div>
                @if($day['isToday'])
                    <span class="badge text-bg-primary" style="font-size:.65rem">Aujourd'hui</span>
                @elseif($hasLog)
                    <span class="badge text-bg-success" style="font-size:.65rem">✓ Journalisé</span>
                @endif
            </div>

            <div class="card-body d-flex flex-column gap-3">

                @if($day['isFuture'])
                    {{-- Future day placeholder --}}
                    <div class="text-center text-muted py-3" style="font-size:.85rem">
                        <div style="font-size:1.5rem;opacity:.3">🔒</div>
                        Pas encore disponible
                    </div>
                @else
                    {{-- Presence toggle --}}
                    <div>
                        <label class="form-label small fw-semibold mb-2">Présence</label>
                        <div class="presence-toggle d-flex gap-2"
                             data-date="{{ $day['dateStr'] }}"
                             data-present="{{ $isPresent ? '1' : '0' }}">
                            <button type="button"
                                    class="btn btn-sm w-50 presence-btn {{ $isPresent ? 'btn-success active' : 'btn-outline-success' }}"
                                    data-value="1">
                                ✓ Présent
                            </button>
                            <button type="button"
                                    class="btn btn-sm w-50 presence-btn {{ !$isPresent ? 'btn-danger active' : 'btn-outline-danger' }}"
                                    data-value="0">
                                ✗ Absent
                            </button>
                        </div>
                    </div>

                    {{-- Daily note --}}
                    <div class="flex-grow-1">
                        <label class="form-label small fw-semibold mb-1">
                            Qu'avez-vous fait aujourd'hui ?
                        </label>
                        <textarea
                            class="form-control daily-note-input"
                            rows="5"
                            data-date="{{ $day['dateStr'] }}"
                            placeholder="Décrivez vos activités, avancées, difficultés rencontrées…"
                            style="resize:none;font-size:.83rem"
                        >{{ $note }}</textarea>
                        <div class="daily-save-status text-success small mt-1"
                             data-date="{{ $day['dateStr'] }}"
                             style="display:none;min-height:1.2em">
                            ✓ Sauvegardé
                        </div>
                    </div>
                @endif

            </div>{{-- /.card-body --}}
        </div>
    </div>
    @endforeach
</div>

{{-- ── Info card ─────────────────────────────────────────────── --}}
<div class="card card-soft">
    <div class="card-body d-flex gap-3 align-items-start">
        <span style="font-size:2rem">💡</span>
        <div>
            <h6 class="fw-semibold mb-1">Comment utiliser ce journal ?</h6>
            <ul class="small text-muted mb-0 ps-3">
                <li>Cochez <strong>Présent</strong> ou <strong>Absent</strong> pour chaque jour travaillé.</li>
                <li>Rédigez une note décrivant ce que vous avez accompli, vos blocages, ou vos questions.</li>
                <li>Vos notes s'enregistrent automatiquement après chaque modification.</li>
                <li>Votre encadrant génère chaque semaine un rapport IA basé sur vos saisies et vos tâches.</li>
            </ul>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    const CSRF  = $('meta[name="csrf-token"]').attr('content');
    const URL   = '{{ route('daily-log.store') }}';
    let timers  = {};

    // ── Shared save function ───────────────────────────────────
    function saveLog(date) {
        const $card     = $(`.daily-card[data-date="${date}"]`);
        const $toggle   = $card.find(`.presence-toggle[data-date="${date}"]`);
        const $note     = $card.find(`.daily-note-input[data-date="${date}"]`);
        const $status   = $card.find(`.daily-save-status[data-date="${date}"]`);
        const isPresent = parseInt($toggle.data('present'), 10);

        $status.hide();

        $.ajax({
            url: URL,
            method: 'POST',
            data: {
                _token:     CSRF,
                log_date:   date,
                is_present: isPresent,
                daily_note: $note.val(),
            },
        })
        .done(() => {
            $status.fadeIn().delay(2500).fadeOut();
            // Update badge
            const $card2 = $(`.daily-card[data-date="${date}"]`);
            if (!$card2.find('.badge.text-bg-success').length) {
                $card2.find('.card-header .text-muted, .card-header .badge.text-bg-primary').after(
                    '<span class="badge text-bg-success ms-auto" style="font-size:.65rem">✓ Journalisé</span>'
                );
            }
        })
        .fail((xhr) => {
            const msg = xhr.responseJSON?.message || 'Erreur de sauvegarde.';
            $status.text('⚠ ' + msg).addClass('text-danger').fadeIn();
        });
    }

    // ── Presence toggle ────────────────────────────────────────
    $(document).on('click', '.presence-btn', function () {
        const $btn    = $(this);
        const $toggle = $btn.closest('.presence-toggle');
        const date    = $toggle.data('date');
        const val     = parseInt($btn.data('value'), 10);

        // Don't re-save if already selected
        if ($btn.hasClass('active')) return;

        // Update UI
        $toggle.find('.presence-btn').each(function () {
            const v = parseInt($(this).data('value'), 10);
            if (v === 1) {
                $(this).toggleClass('btn-success active', val === 1)
                       .toggleClass('btn-outline-success', val !== 1);
            } else {
                $(this).toggleClass('btn-danger active', val === 0)
                       .toggleClass('btn-outline-danger', val !== 0);
            }
        });
        $toggle.data('present', val);

        saveLog(date);
    });

    // ── Note auto-save (debounced 800ms) ──────────────────────
    $(document).on('input', '.daily-note-input', function () {
        const date = $(this).data('date');
        clearTimeout(timers[date]);
        timers[date] = setTimeout(() => saveLog(date), 800);
    });
});
</script>

<style>
    .daily-card {
        transition: box-shadow .2s, border-color .2s;
    }
    .daily-card:hover {
        box-shadow: 0 8px 24px rgba(0,0,0,.1) !important;
    }
    .daily-card.border-primary {
        border-width: 2px !important;
    }
    .presence-btn {
        transition: all .15s;
    }
</style>
@endpush

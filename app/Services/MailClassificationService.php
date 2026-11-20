<?php

namespace App\Services;

use App\Enums\MailClassificationCategory;
use App\Enums\MailClassificationMethod;
use App\Enums\MailClassificationPriority;
use App\Enums\MailClassificationStatus;
use App\Models\MailClassificationEvent;
use App\Models\MailMessage;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MailClassificationService
{
    private const MINIMUM_SCORE = 4;

    /** @var array<string, array<string, int>> */
    private const CATEGORY_RULES = [
        'oficios' => [
            'oficio' => 2,
            'oficio judicial' => 3,
            'juzgado' => 2,
            'tribunal' => 2,
            'fiscalia' => 2,
            'defensoria' => 2,
            'expediente' => 2,
            'cedula' => 2,
            'resolucion' => 2,
            'notificacion judicial' => 3,
            'informe judicial' => 3,
        ],
        'denuncias' => [
            'denuncia' => 2,
            'maltrato' => 2,
            'abuso' => 2,
            'violencia' => 2,
            'vulneracion' => 2,
            'riesgo' => 2,
            'emergencia' => 2,
        ],
        'otras_comunicaciones' => [
            'consulta' => 2,
            'solicitud' => 2,
            'pedido' => 2,
            'reunion' => 2,
            'informacion' => 2,
            'comunicacion' => 2,
            'turno' => 2,
        ],
    ];

    /** @var array<string, int> */
    private const URGENT_RULES = [
        'urgente' => 2,
        'emergencia' => 2,
        'riesgo inminente' => 3,
        'plazo perentorio' => 3,
        'medida urgente' => 3,
    ];

    /** @var array<string, int> */
    private const HIGH_PRIORITY_RULES = [
        'plazo' => 1,
        'vencimiento' => 1,
        'audiencia' => 1,
        'medida' => 1,
    ];

    /**
     * @return array{categoria: MailClassificationCategory, estado: MailClassificationStatus, confianza: ?float, metodo: MailClassificationMethod, motivos: array<int, string>, prioridad: MailClassificationPriority}
     */
    public function suggest(MailMessage $mailMessage): array
    {
        $attachments = $mailMessage->getAttribute('attachments');
        $attachmentNames = is_array($attachments)
            ? collect($attachments)
                ->map(fn (mixed $attachment): string => (string) data_get($attachment, 'name', ''))
                ->implode(' ')
            : '';
        $fields = [
            'Asunto' => $this->normalize((string) $mailMessage->subject),
            'Remitente' => $this->normalize(implode(' ', array_filter([
                $mailMessage->from_name,
                $mailMessage->from_email,
            ]))),
            'Cuerpo' => $this->normalize((string) $mailMessage->body_text),
            'Adjuntos' => $this->normalize($attachmentNames),
            'Metadatos' => $this->normalize(implode(' ', array_filter([
                $mailMessage->provider,
                $mailMessage->folder,
                $mailMessage->direction,
            ]))),
        ];
        /** @var array<string, int> $scores */
        $scores = [];
        $motivos = [];

        foreach (self::CATEGORY_RULES as $category => $rules) {
            $scores[$category] = 0;

            foreach ($rules as $term => $weight) {
                foreach ($fields as $field => $text) {
                    if ($text === '' || ! str_contains($text, $term)) {
                        continue;
                    }

                    $fieldWeight = in_array($field, ['Asunto', 'Adjuntos'], true) ? 2 : 1;
                    $scores[$category] += $weight * $fieldWeight;
                    $motivos[] = sprintf('%s contiene "%s".', $field, $term);
                }
            }
        }

        arsort($scores);
        $topCategory = (string) array_key_first($scores);
        $topScore = $scores[$topCategory];
        $sortedScores = array_values($scores);
        $secondScore = $sortedScores[1] ?? 0;
        $hasClearWinner = $topScore >= self::MINIMUM_SCORE
            && $topScore > $secondScore;
        $category = $hasClearWinner
            ? MailClassificationCategory::from((string) $topCategory)
            : MailClassificationCategory::Pendiente;
        $confidence = $hasClearWinner ? min(0.99, round(0.5 + ($topScore / 20), 3)) : null;
        $priority = $this->priority($fields);

        if (! $hasClearWinner) {
            $motivos[] = $topScore > 0
                ? 'La evidencia encontrada no alcanza el umbral de clasificación automática.'
                : 'No se detectaron señales suficientes para una clasificación automática.';
        }

        return [
            'categoria' => $category,
            'estado' => $hasClearWinner
                ? MailClassificationStatus::SugeridoAutomaticamente
                : MailClassificationStatus::Pendiente,
            'confianza' => $confidence,
            'metodo' => MailClassificationMethod::Reglas,
            'motivos' => array_values(array_unique($motivos)),
            'prioridad' => $priority,
        ];
    }

    public function classifyAutomatically(int $mailMessageId): void
    {
        DB::transaction(function () use ($mailMessageId): void {
            $mailMessage = MailMessage::query()->lockForUpdate()->find($mailMessageId);

            $currentStatus = $mailMessage
                ? MailClassificationStatus::tryFrom((string) $mailMessage->getRawOriginal('clasificacion_estado'))
                : null;

            if (! $mailMessage || in_array($currentStatus, [
                MailClassificationStatus::Confirmado,
                MailClassificationStatus::CorregidoManualmente,
            ], true)) {
                return;
            }

            $this->storeClassification($mailMessage, $this->suggest($mailMessage));
        });
    }

    /**
     * @param  array<int, string>  $motivos
     */
    public function classifyManually(
        MailMessage $mailMessage,
        MailClassificationCategory $category,
        MailClassificationPriority $priority,
        User $user,
        array $motivos = [],
    ): void {
        $reasons = $motivos !== [] ? $motivos : ['Clasificación confirmada manualmente.'];

        $this->storeClassification($mailMessage, [
            'categoria' => $category,
            'estado' => MailClassificationStatus::CorregidoManualmente,
            'confianza' => 1.0,
            'metodo' => MailClassificationMethod::Manual,
            'motivos' => $reasons,
            'prioridad' => $priority,
        ], $user);
    }

    public function confirm(MailMessage $mailMessage, User $user): void
    {
        $category = MailClassificationCategory::tryFrom((string) $mailMessage->getRawOriginal('clasificacion_categoria'));

        if (! $category || $category === MailClassificationCategory::Pendiente) {
            return;
        }

        $method = MailClassificationMethod::tryFrom((string) $mailMessage->getRawOriginal('clasificacion_metodo'))
            ?? MailClassificationMethod::Manual;
        $priority = MailClassificationPriority::tryFrom((string) $mailMessage->getRawOriginal('prioridad'))
            ?? MailClassificationPriority::Normal;
        $motivos = $mailMessage->getAttribute('clasificacion_motivos');
        $motivos = is_array($motivos) && $motivos !== []
            ? array_values(array_filter($motivos, 'is_string'))
            : ['Clasificación confirmada manualmente.'];

        $this->storeClassification($mailMessage, [
            'categoria' => $category,
            'estado' => MailClassificationStatus::Confirmado,
            'confianza' => $mailMessage->getAttribute('clasificacion_confianza') !== null
                ? (float) $mailMessage->getAttribute('clasificacion_confianza')
                : null,
            'metodo' => $method,
            'motivos' => $motivos,
            'prioridad' => $priority,
        ], $user);
    }

    /**
     * @param  array{categoria: MailClassificationCategory, estado: MailClassificationStatus, confianza: ?float, metodo: MailClassificationMethod, motivos: array<int, string>, prioridad: MailClassificationPriority}  $classification
     */
    private function storeClassification(MailMessage $mailMessage, array $classification, ?User $user = null): void
    {
        $previousCategory = MailClassificationCategory::tryFrom((string) $mailMessage->getRawOriginal('clasificacion_categoria'))?->value;
        $previousStatus = MailClassificationStatus::tryFrom((string) $mailMessage->getRawOriginal('clasificacion_estado'))?->value;

        $mailMessage->fill([
            'clasificacion_categoria' => $classification['categoria'],
            'clasificacion_estado' => $classification['estado'],
            'clasificacion_confianza' => $classification['confianza'],
            'clasificacion_metodo' => $classification['metodo'],
            'clasificacion_revisado_en' => $user ? now() : null,
            'clasificacion_revisado_por' => $user?->id,
            'clasificacion_motivos' => $classification['motivos'],
            'prioridad' => $classification['prioridad'],
        ])->save();

        MailClassificationEvent::create([
            'mail_message_id' => $mailMessage->id,
            'categoria_anterior' => $previousCategory,
            'estado_anterior' => $previousStatus,
            'categoria' => $classification['categoria'],
            'estado' => $classification['estado'],
            'confianza' => $classification['confianza'],
            'metodo' => $classification['metodo'],
            'motivos' => $classification['motivos'],
            'prioridad' => $classification['prioridad'],
            'usuario_id' => $user?->id,
        ]);
    }

    /**
     * @param  array<string, string>  $fields
     */
    private function priority(array $fields): MailClassificationPriority
    {
        $text = implode(' ', $fields);

        foreach (self::URGENT_RULES as $term => $weight) {
            if (str_contains($text, $term)) {
                return MailClassificationPriority::Urgente;
            }
        }

        foreach (self::HIGH_PRIORITY_RULES as $term => $weight) {
            if (str_contains($text, $term)) {
                return MailClassificationPriority::Alta;
            }
        }

        return MailClassificationPriority::Normal;
    }

    private function normalize(string $value): string
    {
        return (string) preg_replace('/\s+/', ' ', Str::ascii(Str::lower($value)));
    }
}

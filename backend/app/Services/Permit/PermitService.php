<?php

namespace App\Services\Permit;

use App\Models\Equipment;
use App\Models\Permit;
use App\Models\PermitEvent;
use App\Models\Worker;
use Illuminate\Support\Facades\DB;

class PermitService
{
    public function __construct(private readonly PermitNumberGenerator $numbers) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function createDraft(array $data, int $creatorUserId): Permit
    {
        return DB::transaction(function () use ($data, $creatorUserId) {
            $permit = Permit::create(array_merge($data, [
                'permit_number' => $this->numbers->next(),
                'status' => Permit::STATUS_DRAFT,
                'created_by_user_id' => $creatorUserId,
            ]));

            $this->logEvent($permit, PermitEvent::TYPE_CREATED, $creatorUserId, [
                'permit_type_id' => $permit->permit_type_id,
                'project_id' => $permit->project_id,
            ]);

            return $permit;
        });
    }

    /**
     * Attach workers by ID list and/or by helmet/coverall QR tokens. The same
     * worker may be supplied via both modes; we dedupe.
     *
     * @param  array<int, array{id: string, role_on_permit?: string}>  $byIds
     * @param  array<int, string>  $byTokens
     * @return array{attached: int, already_attached: int, unknown_tokens: array<int, string>}
     */
    public function attachWorkers(Permit $permit, array $byIds, array $byTokens, int $actorUserId): array
    {
        return DB::transaction(function () use ($permit, $byIds, $byTokens, $actorUserId) {
            $attached = 0;
            $alreadyAttached = 0;
            $unknownTokens = [];

            // ID path
            foreach ($byIds as $entry) {
                $role = $entry['role_on_permit'] ?? 'worker';
                $existing = $permit->workers()->where('workers.id', $entry['id'])->exists();
                if ($existing) {
                    $alreadyAttached++;

                    continue;
                }
                $permit->workers()->attach($entry['id'], ['role_on_permit' => $role]);
                $attached++;
            }

            // Token path: resolve helmet OR coverall token to a worker
            foreach ($byTokens as $token) {
                $worker = Worker::where('helmet_qr_token', $token)
                    ->orWhere('coverall_qr_token', $token)
                    ->first();
                if ($worker === null) {
                    $unknownTokens[] = substr($token, 0, 6).'…';

                    continue;
                }
                if ($permit->workers()->where('workers.id', $worker->id)->exists()) {
                    $alreadyAttached++;

                    continue;
                }
                $permit->workers()->attach($worker->id, ['role_on_permit' => 'worker']);
                $attached++;
            }

            if ($attached > 0) {
                $this->logEvent($permit, 'workers_attached', $actorUserId, [
                    'attached' => $attached,
                    'already_attached' => $alreadyAttached,
                    'unknown_tokens' => count($unknownTokens),
                ]);
            }

            return [
                'attached' => $attached,
                'already_attached' => $alreadyAttached,
                'unknown_tokens' => $unknownTokens,
            ];
        });
    }

    /**
     * @param  array<int, string>  $byIds
     * @param  array<int, string>  $byTokens
     * @return array{attached: int, already_attached: int, unknown_tokens: array<int, string>}
     */
    public function attachEquipment(Permit $permit, array $byIds, array $byTokens, int $actorUserId): array
    {
        return DB::transaction(function () use ($permit, $byIds, $byTokens, $actorUserId) {
            $attached = 0;
            $alreadyAttached = 0;
            $unknownTokens = [];

            foreach ($byIds as $id) {
                if ($permit->equipment()->where('equipment.id', $id)->exists()) {
                    $alreadyAttached++;

                    continue;
                }
                $permit->equipment()->attach($id);
                $attached++;
            }

            foreach ($byTokens as $token) {
                $eq = Equipment::where('qr_token', $token)->first();
                if ($eq === null) {
                    $unknownTokens[] = substr($token, 0, 6).'…';

                    continue;
                }
                if ($permit->equipment()->where('equipment.id', $eq->id)->exists()) {
                    $alreadyAttached++;

                    continue;
                }
                $permit->equipment()->attach($eq->id);
                $attached++;
            }

            if ($attached > 0) {
                $this->logEvent($permit, 'equipment_attached', $actorUserId, [
                    'attached' => $attached,
                    'already_attached' => $alreadyAttached,
                    'unknown_tokens' => count($unknownTokens),
                ]);
            }

            return [
                'attached' => $attached,
                'already_attached' => $alreadyAttached,
                'unknown_tokens' => $unknownTokens,
            ];
        });
    }

    /** @param array<string, mixed> $payload */
    public function logEvent(Permit $permit, string $type, ?int $actorUserId, array $payload = [], ?string $comment = null): PermitEvent
    {
        return $permit->events()->create([
            'event_type' => $type,
            'actor_user_id' => $actorUserId,
            'payload' => $payload,
            'comment' => $comment,
            'occurred_at' => now(),
        ]);
    }
}

<?php

namespace App\Services\Equipment;

use App\Http\Requests\V1\StoreEquipmentRequest;
use Illuminate\Support\Facades\Validator;

class BulkEquipmentService
{
    public function __construct(private readonly EquipmentService $equipment) {}

    /**
     * @param  array<int, array<string, mixed>>  $records
     * @return array{summary: array<string, int>, results: array<int, array<string, mixed>>}
     */
    public function importMany(array $records): array
    {
        $rules = (new StoreEquipmentRequest)->rules();

        $results = [];
        $succeeded = 0;
        $failed = 0;

        foreach ($records as $index => $record) {
            $validator = Validator::make($record, $rules);
            if ($validator->fails()) {
                $results[] = [
                    'index' => $index,
                    'status' => 'failed',
                    'errors' => $validator->errors()->toArray(),
                ];
                $failed++;

                continue;
            }

            try {
                $eq = $this->equipment->create($validator->validated());
                $results[] = [
                    'index' => $index,
                    'status' => 'succeeded',
                    'id' => $eq->id,
                ];
                $succeeded++;
            } catch (\Throwable $e) {
                $results[] = [
                    'index' => $index,
                    'status' => 'failed',
                    'errors' => ['_exception' => [$e->getMessage()]],
                ];
                $failed++;
            }
        }

        return [
            'summary' => [
                'total' => count($records),
                'succeeded' => $succeeded,
                'failed' => $failed,
            ],
            'results' => $results,
        ];
    }
}

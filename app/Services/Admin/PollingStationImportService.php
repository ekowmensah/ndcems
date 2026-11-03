<?php

namespace App\Services\Admin;

use App\Model\ElectoralArea;
use App\Model\ImportBatch;
use App\Model\PollingStation;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PollingStationImportService
{
    private const HEADERS = ['polling_station_id', 'name', 'total_voters', 'electoral_area_id'];

    public function preview(UploadedFile $file, ?int $adminId): ImportBatch
    {
        $handle = fopen($file->getRealPath(), 'r');
        $headers = array_map(fn ($value) => strtolower(trim((string) $value)), fgetcsv($handle) ?: []);
        if ($headers !== self::HEADERS) {
            fclose($handle);
            throw ValidationException::withMessages(['file' => 'CSV headers must be: '.implode(', ', self::HEADERS)]);
        }

        return DB::transaction(function () use ($file, $adminId, $handle) {
            $batch = ImportBatch::create(['type' => 'polling_stations', 'original_filename' => $file->getClientOriginalName(), 'uploaded_by' => $adminId]);
            $seen = [];
            $rowNumber = 1;
            $valid = 0;
            $invalid = 0;
            while (($values = fgetcsv($handle)) !== false) {
                $rowNumber++;
                if (count(array_filter($values, fn ($value) => trim((string) $value) !== '')) === 0) continue;
                $payload = array_combine(self::HEADERS, array_pad(array_slice($values, 0, 4), 4, null));
                $payload = array_map(fn ($value) => trim((string) $value), $payload);
                $errors = $this->validateRow($payload, $seen);
                $errors ? $invalid++ : $valid++;
                $batch->rows()->create(['row_number' => $rowNumber, 'payload' => $payload, 'errors' => $errors ?: null, 'status' => $errors ? 'invalid' : 'pending']);
                $seen[strtolower($payload['polling_station_id'])] = true;
            }
            fclose($handle);
            if ($rowNumber === 1) throw ValidationException::withMessages(['file' => 'The CSV contains no data rows.']);
            $batch->update(['total_rows' => $valid + $invalid, 'valid_rows' => $valid, 'invalid_rows' => $invalid]);
            return $batch;
        });
    }

    public function commit(ImportBatch $batch): void
    {
        abort_unless($batch->status === 'preview', 422, 'Only preview batches can be committed.');
        abort_if($batch->invalid_rows > 0, 422, 'Correct invalid rows and upload a new batch before committing.');
        DB::transaction(function () use ($batch) {
            foreach ($batch->rows()->where('status', 'pending')->lockForUpdate()->get() as $row) {
                $area = ElectoralArea::findOrFail((int) $row->payload['electoral_area_id']);
                abort_if(PollingStation::where('polling_station_id', $row->payload['polling_station_id'])->exists(), 422, 'A polling-station code became duplicated after preview.');
                $station = PollingStation::create([
                    'polling_station_id' => $row->payload['polling_station_id'], 'name' => $row->payload['name'],
                    'total_voters' => (int) $row->payload['total_voters'], 'electoralarea_id' => $area->id,
                    'country_id' => $area->country_id, 'region_id' => $area->region_id, 'constituency_id' => $area->constituency_id,
                ]);
                $row->update(['status' => 'imported', 'target_id' => $station->id]);
            }
            $batch->update(['status' => 'committed', 'committed_at' => now()]);
        });
    }

    public function rollback(ImportBatch $batch): void
    {
        abort_unless($batch->status === 'committed', 422, 'Only committed batches can be rolled back.');
        DB::transaction(function () use ($batch) {
            foreach ($batch->rows()->where('status', 'imported')->lockForUpdate()->get() as $row) {
                $id = $row->target_id;
                $inUse = DB::table('election_result')->where('polling_station_id', $id)->exists()
                    || DB::table('party_election_result')->where('polling_station_id', $id)->exists()
                    || DB::table('candidates')->where('polling_station_id', $id)->exists()
                    || DB::table('users')->where('polling_station_id', $id)->exists();
                abort_if($inUse, 422, 'Rollback blocked: one or more imported stations already have operational data.');
                PollingStation::whereKey($id)->delete();
                $row->update(['status' => 'rolled_back']);
            }
            $batch->update(['status' => 'rolled_back', 'rolled_back_at' => now()]);
        });
    }

    private function validateRow(array $payload, array $seen): array
    {
        $errors = [];
        $code = $payload['polling_station_id'];
        if (strlen($code) < 6 || strlen($code) > 14) $errors[] = 'Code must contain 6 to 14 characters.';
        if (isset($seen[strtolower($code)])) $errors[] = 'Duplicate code in this file.';
        if ($code && PollingStation::where('polling_station_id', $code)->exists()) $errors[] = 'Code already exists.';
        if ($payload['name'] === '') $errors[] = 'Name is required.';
        if (filter_var($payload['total_voters'], FILTER_VALIDATE_INT) === false || (int) $payload['total_voters'] < 0) $errors[] = 'Total voters must be a non-negative integer.';
        if (!ctype_digit($payload['electoral_area_id']) || !ElectoralArea::whereKey((int) $payload['electoral_area_id'])->exists()) $errors[] = 'Electoral area does not exist.';
        return $errors;
    }
}

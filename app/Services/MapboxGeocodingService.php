<?php

namespace App\Services;

use App\Models\Barrio;
use App\Models\Persona;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Throwable;

class MapboxGeocodingService
{
    private const ENDPOINT = 'https://api.mapbox.com/search/geocode/v6/batch';

    public function configured(): bool
    {
        return filled(config('services.mapbox.token'));
    }

    /**
     * @return array<int, array{latitude: float, longitude: float, precision: string|null}>
     */
    public function geocodePeople(Collection $people): array
    {
        $queries = [];
        $coordinates = [];

        foreach ($people as $persona) {
            $query = $this->personQuery($persona);

            if ($query === null) {
                continue;
            }

            $hash = $this->queryHash($query);

            if ($this->hasCurrentCoordinates($persona, $hash)) {
                $coordinates[$persona->id] = $this->coordinatesFrom($persona);

                continue;
            }

            $queries[$persona->id] = ['hash' => $hash, 'parameters' => $query];
        }

        foreach ($this->geocodeBatch($queries) as $id => $result) {
            $query = $queries[$id];
            $this->storeCoordinates('personas', (int) $id, $query['hash'], $result);
            $coordinates[$id] = $result;
        }

        foreach ($people as $persona) {
            if (isset($coordinates[$persona->id])) {
                $persona->setAttribute('latitud', $coordinates[$persona->id]['latitude']);
                $persona->setAttribute('longitud', $coordinates[$persona->id]['longitude']);
            }
        }

        return $coordinates;
    }

    /**
     * @return array<int, array{latitude: float, longitude: float, precision: string|null}>
     */
    public function geocodeBarrios(Collection $barrios): array
    {
        $queries = [];
        $coordinates = [];

        foreach ($barrios as $barrio) {
            $query = $this->barrioQuery($barrio);
            $hash = $this->queryHash($query);

            if ($this->hasCurrentCoordinates($barrio, $hash)) {
                $coordinates[$barrio->id] = $this->coordinatesFrom($barrio);

                continue;
            }

            $queries[$barrio->id] = ['hash' => $hash, 'parameters' => $query];
        }

        foreach ($this->geocodeBatch($queries) as $id => $result) {
            $query = $queries[$id];
            $this->storeCoordinates('barrios', (int) $id, $query['hash'], $result);
            $coordinates[$id] = $result;
        }

        foreach ($barrios as $barrio) {
            if (isset($coordinates[$barrio->id])) {
                $barrio->setAttribute('latitud', $coordinates[$barrio->id]['latitude']);
                $barrio->setAttribute('longitud', $coordinates[$barrio->id]['longitude']);
            }
        }

        return $coordinates;
    }

    /**
     * @param  array<int, array{hash: string, parameters: array<string, mixed>}>  $queries
     * @return array<int, array{latitude: float, longitude: float, precision: string|null}>
     */
    private function geocodeBatch(array $queries): array
    {
        if (! $this->configured() || $queries === []) {
            return [];
        }

        $coordinates = [];

        foreach (array_chunk($queries, 1000, true) as $batch) {
            try {
                $response = Http::acceptJson()
                    ->timeout(10)
                    ->withQueryParameters([
                        'access_token' => config('services.mapbox.token'),
                        'permanent' => 'true',
                    ])
                    ->post(self::ENDPOINT, array_values(array_column($batch, 'parameters')))
                    ->throw()
                    ->json('batch', []);

                foreach (array_values($batch) as $index => $query) {
                    $feature = data_get($response, "{$index}.features.0");
                    $point = data_get($feature, 'geometry.coordinates');

                    if (! is_array($point) || count($point) < 2) {
                        continue;
                    }

                    $id = array_keys($batch)[$index];
                    $coordinates[$id] = [
                        'latitude' => (float) $point[1],
                        'longitude' => (float) $point[0],
                        'precision' => data_get($feature, 'properties.coordinates.accuracy')
                            ?? data_get($feature, 'properties.feature_type'),
                    ];
                }
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        return $coordinates;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function personQuery(Persona $persona): ?array
    {
        if (blank($persona->direccion_calle) || blank($persona->direccion_numero)) {
            return null;
        }

        return [
            'address_number' => trim((string) $persona->direccion_numero),
            'street' => trim((string) $persona->direccion_calle),
            'place' => $persona->localidad?->nombre ?? 'San Nicolás de los Arroyos',
            'region' => 'Buenos Aires',
            'country' => 'AR',
            'types' => ['address'],
            'limit' => 1,
            'autocomplete' => false,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function barrioQuery(Barrio $barrio): array
    {
        return [
            'q' => "{$barrio->nombre}, {$barrio->localidad?->nombre}, Buenos Aires, Argentina",
            'types' => ['neighborhood', 'locality', 'place'],
            'limit' => 1,
            'autocomplete' => false,
        ];
    }

    private function queryHash(array $query): string
    {
        return hash('sha256', json_encode($query, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    private function hasCurrentCoordinates(object $model, string $hash): bool
    {
        return is_numeric($model->latitud)
            && is_numeric($model->longitud)
            && $model->geocodificacion_consulta_hash === $hash;
    }

    /**
     * @return array{latitude: float, longitude: float, precision: string|null}
     */
    private function coordinatesFrom(object $model): array
    {
        return [
            'latitude' => (float) $model->latitud,
            'longitude' => (float) $model->longitud,
            'precision' => $model->geocodificacion_precision,
        ];
    }

    /**
     * @param  array{latitude: float, longitude: float, precision: string|null}  $coordinates
     */
    private function storeCoordinates(string $table, int $id, string $hash, array $coordinates): void
    {
        DB::table($table)->where('id', $id)->update([
            'latitud' => $coordinates['latitude'],
            'longitud' => $coordinates['longitude'],
            'geocodificacion_precision' => $coordinates['precision'],
            'geocodificacion_consulta_hash' => $hash,
            'geocodificado_en' => now(),
        ]);
    }
}

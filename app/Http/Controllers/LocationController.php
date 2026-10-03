<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Imports\LocationsImport;
use App\Models\Location;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

final class LocationController extends Controller
{
    private const LEVEL_MAP = [
        'county'     => 1,
        'subcounty'  => 2,
        'sub county' => 2,
        'town'       => 3,
    ];

    public function index(Request $request): View|JsonResponse
    {
        Gate::authorize('viewAny', Location::class);

        if ($request->ajax()) {
            $data = Location::query()
                ->select(['id', 'code', 'name', 'level', 'parent_code', 'latitude', 'longitude'])
                ->with(['parent:code,name'])
                ->orderBy('level', 'asc')
                ->orderBy('name', 'asc');

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('parent_name', static fn (Location $row): string => $row->parent?->name ?? '—')
                ->addColumn('level_name', static fn (Location $row): string => match ((int) $row->level) {
                    1 => 'County',
                    2 => 'Subcounty',
                    3 => 'Town',
                    default => 'Unknown',
                })
                ->filterColumn('level_name', static function ($query, string $keyword): void {
                    $cleanKeyword = strtolower(trim($keyword));

                    if (isset(self::LEVEL_MAP[$cleanKeyword])) {
                        $query->where('level', self::LEVEL_MAP[$cleanKeyword]);
                    } elseif (is_numeric($cleanKeyword)) {
                        $query->where('level', (int) $cleanKeyword);
                    }
                })
                ->addColumn('action', static fn (Location $row): string => sprintf(
                    '<button class="text-blue-600 hover:underline view-location-btn" data-code="%s">View</button>',
                    e($row->code)
                ))
                ->rawColumns(['action'])
                ->make(true);
        }

        $locations = Location::query()
            ->select(['code', 'name', 'level'])
            ->orderBy('name', 'asc')
            ->get();

        return view('admin.locations', compact('locations'));
    }

    public function create(): View
    {
        Gate::authorize('create', Location::class);

        $locations = Location::query()
            ->select(['code', 'name'])
            ->orderBy('name', 'asc')
            ->get();

        return view('locations.create', compact('locations'));
    }

    public function upload(Request $request): JsonResponse
    {
        Gate::authorize('create', Location::class);

        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:2048'],
        ]);

        try {
            $import = new LocationsImport();
            Excel::import($import, $request->file('file'));

            if (method_exists($import, 'failures') && $import->failures()->isNotEmpty()) {
                return response()->json([
                    'status'        => 'warning',
                    'message'       => 'Upload completed with errors.',
                    'errors_import' => $import->failures(),
                ], 422);
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'Locations uploaded successfully!',
            ]);
        } catch (\Throwable $e) {
            Log::error('Location upload failed: ' . $e->getMessage(), [
                'exception' => $e,
                'user_id'   => auth()->id(),
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'An error occurred while processing the file upload.',
            ], 500);
        }
    }

    public function add(Request $request): JsonResponse
    {
        Gate::authorize('create', Location::class);

        $validated = $request->validate([
            'code'        => ['required', 'string', 'max:255', 'unique:locations,code'],
            'name'        => ['required', 'string', 'max:255'],
            'level'       => ['required', 'integer', 'in:1,2,3'],
            'parent_code' => ['nullable', 'string', 'exists:locations,code'],
        ]);

        Location::create([
            'code'        => strtolower(trim($validated['code'])),
            'name'        => trim($validated['name']),
            'level'       => (int) $validated['level'],
            'parent_code' => isset($validated['parent_code']) ? strtolower(trim($validated['parent_code'])) : null,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Location added successfully.',
        ]);
    }

    public function autoFillMissingCoordinates(): JsonResponse
    {
        Gate::authorize('update', Location::class);

        @set_time_limit(300);

        $results = [];

        Location::query()
            ->whereNull('latitude')
            ->orWhereNull('longitude')
            ->lazy()
            ->each(function (Location $location) use (&$results): void {
                $coords = $this->fetchCoordinatesFromExternalSource($location->name);

                if ($coords !== null) {
                    $location->update([
                        'latitude'  => $coords['lat'],
                        'longitude' => $coords['lng'],
                    ]);

                    $results[] = [
                        'level' => $location->level,
                        'name'  => $location->name,
                        'lat'   => $coords['lat'],
                        'lng'   => $coords['lng'],
                    ];
                }

                // Comply with OpenStreetMap Nominatim Rate-Limiting (Max 1 req/sec)
                usleep(1_000_000);
            });

        return response()->json([
            'status'  => 'success',
            'message' => 'Coordinates updated successfully!',
            'updated' => $results,
        ]);
    }

    private function fetchCoordinatesFromExternalSource(string $name): ?array
    {
        try {
            $userAgent = config('app.name', 'LaravelApp') . ' CoordinatesFetcher (' . config('mail.from.address', 'admin@example.com') . ')';

            $response = Http::timeout(5)
                ->withHeaders([
                    'User-Agent' => $userAgent,
                ])
                ->get('https://nominatim.openstreetmap.org/search', [
                    'format' => 'json',
                    'q'      => $name,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                if (!empty($data) && isset($data[0]['lat'], $data[0]['lon'])) {
                    return [
                        'lat' => (float) $data[0]['lat'],
                        'lng' => (float) $data[0]['lon'],
                    ];
                }
            }
        } catch (\Throwable $e) {
            Log::warning("Failed to fetch coordinates for location [{$name}]: " . $e->getMessage());
        }

        return null;
    }
}
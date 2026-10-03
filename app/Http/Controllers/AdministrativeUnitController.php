<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Imports\AdministrativeUnitsImport;
use App\Models\AdministrativeUnit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

final class AdministrativeUnitController extends Controller
{
    /**
     * Map of numeric levels to unit level labels.
     */
    private const LEVEL_MAP = [
        1 => 'School',
        2 => 'Department',
        3 => 'Course',
    ];

    /**
     * Display administrative units list or handle DataTables server-side processing.
     */
    public function index(Request $request): View|JsonResponse
    {
        Gate::authorize('manage-administrative-units');

        if ($request->ajax()) {
            $data = AdministrativeUnit::with('parent:id,code,name')
                ->orderBy('level', 'asc')
                ->orderBy('name', 'asc');

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('parent_name', fn ($row) => $row->parent?->name ?? '—')
                ->addColumn('level_name', fn ($row) => self::LEVEL_MAP[$row->level] ?? 'Unknown')
                ->filterColumn('level_name', function ($query, $keyword) {
                    $search = strtolower(trim($keyword));
                    $flippedMap = array_change_key_case(array_flip(self::LEVEL_MAP), CASE_LOWER);

                    if (isset($flippedMap[$search])) {
                        $query->where('level', $flippedMap[$search]);
                    } elseif (is_numeric($search)) {
                        $query->where('level', (int) $search);
                    }
                })
                ->addColumn('action', function ($row) {
                    return '<button class="text-blue-600 hover:underline view-unit" data-id="' . (int) $row->id . '">View</button>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        $admin_units = AdministrativeUnit::orderBy('name')->get();

        return view('admin.administrative-units', compact('admin_units'));
    }

    /**
     * Upload and import administrative units from a spreadsheet.
     */
    public function upload(Request $request): JsonResponse
    {
        Gate::authorize('manage-administrative-units');

        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:2048'],
        ]);

        try {
            $import = new AdministrativeUnitsImport();
            Excel::import($import, $request->file('file'));

            if (method_exists($import, 'failures') && $import->failures()->isNotEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'errors_import' => $import->failures(),
                ], 422);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Administrative units uploaded successfully!',
            ]);
        } catch (\Throwable $e) {
            Log::error('Administrative units upload failed: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Error uploading file. Please verify spreadsheet formatting.',
            ], 500);
        }
    }

    /**
     * Add a single administrative unit record manually.
     */
    public function add(Request $request): JsonResponse
    {
        Gate::authorize('manage-administrative-units');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:administrative_units,code'],
            'parent_code' => [
                'nullable',
                'string',
                'exists:administrative_units,code',
                'required_if:level,2,3',
            ],
            'level' => ['required', 'integer', 'min:1', 'max:3'],
        ]);

        $unit = AdministrativeUnit::create([
            'name' => trim($validated['name']),
            'code' => strtoupper(trim($validated['code'])),
            'parent_code' => !empty($validated['parent_code']) ? strtoupper(trim($validated['parent_code'])) : null,
            'level' => (int) $validated['level'],
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Administrative unit added successfully.',
            'data' => $unit,
        ], 201);
    }
}
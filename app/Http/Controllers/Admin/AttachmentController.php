<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\GenerateWeekNumber;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAttachmentRequest;
use App\Models\Attachment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;

final class AttachmentController extends Controller
{
    /**
     * Display listing or handle AJAX data for Yajra DataTables.
     */
    public function index(Request $request): View|JsonResponse
    {
        Gate::authorize('manage-attachments');

        if ($request->ajax()) {
            $data = Attachment::query()
                ->select(['id', 'name', 'slug', 'start_date', 'end_date'])
                ->latest('start_date');

            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('name', fn (Attachment $row): string => e($row->name))
                ->editColumn('start_date', fn (Attachment $row): string => $row->start_date?->format('d M Y') ?? '-')
                ->editColumn('end_date', fn (Attachment $row): string => $row->end_date?->format('d M Y') ?? '-')
                ->addColumn('action', fn (Attachment $row): string => sprintf(
                    '<a href="%s" class="px-3 py-1 bg-indigo-600 hover:bg-indigo-500 text-white rounded text-xs font-semibold transition-colors">View</a>',
                    route('admin.attachments.show', $row->slug)
                ))
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('admin.attachments');
    }

    /**
     * Store a newly created attachment session.
     */
    public function store(StoreAttachmentRequest $request, GenerateWeekNumber $weekGen): JsonResponse
    {
        $validated = $request->validated();
        $slug = Str::slug($validated['name']);

        if (Attachment::query()->where('slug', $slug)->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'An attachment session generating this URL slug already exists. Please choose a unique name.',
            ], 422);
        }

        $record = Attachment::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'start_week_id' => $weekGen->weekId($validated['start_date']),
            'end_week_id' => $weekGen->weekId($validated['end_date']),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Attachment session created successfully.',
            'data' => $record,
        ], 201);
    }
}
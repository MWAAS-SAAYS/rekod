<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\AdministrativeUnit;
use App\Models\Attachment;
use App\Models\AttachmentLecturer;
use App\Models\AttachmentStudent;
use App\Models\LecturerAssigment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

final class LecturerAssigmentController extends Controller
{
    private const CLUSTERS = [
        'nairobi_metro' => ['cbd', 'upperhill', 'nairobi'],
        'nairobi_westlands' => ['Westlands', 'Parklands', 'Kilimani', 'Kileleshwa', 'Kangemi', 'Loresho'],
        'thika_road' => ['Thika', 'Makuyu', 'Ruiru', 'Juja', 'Limuru', 'Kiambu', 'Gikuyu'],
        'nakuru_cluster' => ['Nakuru', 'Naivasha', 'Nyahururu', 'Gilgil', 'Molo', 'Eldama Ravine', 'Ol Kalou', 'Rumuruti', 'nanyuki'],
        'north_rift_cluster' => ['Eldoret', 'Kitale', 'Iten', 'Tambach', 'Kapenguria', 'Burnt Forest', 'Kapsabet', 'Nandi Hills'],
        'western_cluster' => ['Kakamega', 'Mumias', 'Kisumu', 'Homa Bay', 'Muhoroni', 'Vihiga', 'Bungoma', 'Webuye', 'Kimilili', 'Malakisi', 'Busia', 'Malaba', 'Mbale', 'Siaya', 'Bondo', 'Ugunja', 'Ukwala', 'Yala', 'Nyamira', 'Nyansiongo', 'Migori', 'Rongo', 'Kisii'],
        'nyeri_mt_kenya_cluster' => ['Nyeri', 'Othaya'],
        'meru_embu_cluster' => ['Meru', 'Maua', 'Karatina', 'Chuka', 'Embu', 'Mwea', 'Sagana', 'kenol', 'Muranga', 'Maragua', 'Kangema', 'Runyenjes', 'Kerugoya', 'Kutus', 'Kirinyaga'],
        'coast_cluster' => ['Mombasa', 'Mtwapa', 'Kilifi', 'Malindi', 'Voi', 'Mariakani', 'Ukunda', 'Likoni', 'Nyali', 'Changamwe'],
        'mombasa_road_cluster' => ['Machakos', 'Kangundo-Tala', 'Matuu', 'Kitui', 'Mwingi', 'Makueni', 'Mombasa Road', 'Donholm', 'Eastleigh', 'Embakasi', 'Umoja', 'Athi River', 'Kitengela', 'Pipeline', 'South B', 'South C', 'Buruburu'],
        'kasarani_cluster' => ['kasarani', 'Roysambu', 'Githurai', 'Kahawa', 'Zimmerman', 'Muthaiga'],
        'kericho_south_rift_cluster' => ['Kericho', 'Litein', 'Kipkelion', 'Londiani', 'Bomet', 'Narok', 'Iten', 'Tambach', 'Nandi Hills', 'Kapsabet', 'Kitale', 'Kabarnet'],
        'frontier_cluster' => ['Garissa', 'Wajir', 'Mandera', 'Isiolo', 'Moyale', 'Lodwar', 'Kakuma', 'Maralal', 'Samburu'],
    ];

    /**
     * Display a listing of assigned students or return DataTable JSON response.
     */
    public function index(Request $request): JsonResponse|View
    {
        if ($request->ajax()) {
            $query = AttachmentStudent::whereNotNull('company_id')
                ->with([
                    'attachment',
                    'student.user',
                    'student.program.parent',
                    'attachmentLecturer.lecturer.user',
                    'company.town',
                ]);

            if ($request->filled('attachment_id')) {
                $query->where('attachment_id', $request->attachment_id);
            }

            if ($request->filled('department_id')) {
                $query->whereHas('student.program.parent', static function ($q) use ($request) {
                    $q->where('id', $request->department_id);
                });
            }

            $students = $query->get();

            $lecturerGroups = [];
            foreach ($students as $student) {
                $lecturerName = $student->attachmentLecturer?->lecturer?->user?->name
                    ?? '<span class="badge badge-warning">Not Assigned</span>';

                $lecturerGroups[$lecturerName][] = $student;
            }

            uksort($lecturerGroups, static function (string $a, string $b) {
                if ($a === '<span class="badge badge-warning">Not Assigned</span>') {
                    return 1;
                }
                if ($b === '<span class="badge badge-warning">Not Assigned</span>') {
                    return -1;
                }

                return strcmp($a, $b);
            });

            $groupedData = [];
            $counter = 1;

            foreach ($lecturerGroups as $lecturerName => $groupStudents) {
                usort($groupStudents, static fn ($a, $b) => strcmp(
                    $a->student?->user?->name ?? '',
                    $b->student?->user?->name ?? ''
                ));

                foreach ($groupStudents as $student) {
                    $groupedData[] = [
                        'DT_RowIndex' => $counter++,
                        'attachment' => $student->attachment?->name ?? '-',
                        'name' => $student->student?->user?->name ?? '-',
                        'reg_no' => $student->student?->reg_no ?? '-',
                        'department' => $student->student?->program?->parent?->name ?? '-',
                        'lecturer' => $lecturerName,
                        'company' => $student->company?->name ?? '-',
                        'town' => $student->company?->town?->name ?? '-',
                        'phone_number' => $student->student?->phone_number ?? '-',
                    ];
                }
            }

            return DataTables::of(collect($groupedData))->make(true);
        }

        $attachments = Attachment::select('id', 'name')->orderByDesc('created_at')->get();
        $departments = AdministrativeUnit::where('level', 2)->get();

        return view('admin.lecturer_assignment', compact('attachments', 'departments'));
    }

    /**
     * Generate draft assignment of students to lecturers using geographical clustering.
     */
    public function generateDraft(Request $request): JsonResponse
    {
        $request->validate([
            'department_id' => ['required', 'exists:administrative_units,id'],
            'attachment_id' => ['required', 'exists:attachments,id'],
        ]);

        $students = AttachmentStudent::with(['company.town', 'student.program.parent'])
            ->where('attachment_id', $request->attachment_id)
            ->whereNotNull('company_id')
            ->whereHas('student.program.parent', static fn ($q) => $q->where('id', $request->department_id))
            ->get();

        $lecturers = AttachmentLecturer::with('lecturer.user')
            ->where([
                'attachment_id' => $request->attachment_id,
                'department_id' => $request->department_id,
            ])
            ->get();

        $studentsById = [];
        $coastStudents = [];
        $frontierStudents = [];
        $nairobiMetroStudents = [];
        $nyeriStudents = [];
        $regularClusterStudents = [];
        $uncategorized = [];

        foreach ($students as $student) {
            if (!$student->company || !$student->company->town) {
                $uncategorized[] = $student->id;
                continue;
            }

            $townName = strtolower(trim($student->company->town->name));
            $townName = str_replace([' road', ' rd', ' street', ' st', ' avenue', ' ave', ' lane', ' ln'], '', $townName);
            $townName = trim($townName);

            $studentObj = (object) [
                'id' => $student->id,
                'company_id' => $student->company_id,
                'original_model' => $student,
            ];

            $studentsById[$student->id] = $studentObj;

            $foundCluster = null;
            foreach (self::CLUSTERS as $clusterName => $towns) {
                foreach ($towns as $town) {
                    $searchTown = strtolower(trim($town));
                    if (str_contains($townName, $searchTown)) {
                        $foundCluster = $clusterName;
                        break 2;
                    }
                }
            }

            if (!$foundCluster) {
                $firstWord = explode(' ', $townName)[0];
                foreach (self::CLUSTERS as $clusterName => $towns) {
                    foreach ($towns as $town) {
                        if ($firstWord === strtolower(trim($town))) {
                            $foundCluster = $clusterName;
                            break 2;
                        }
                    }
                }
            }

            if (!$foundCluster) {
                $uncategorized[] = $student->id;
                continue;
            }

            if ($foundCluster === 'coast_cluster') {
                $coastStudents[] = $student->id;
            } elseif ($foundCluster === 'frontier_cluster') {
                $frontierStudents[] = $student->id;
            } elseif ($foundCluster === 'nyeri_mt_kenya_cluster') {
                $nyeriStudents[] = $student->id;
            } elseif ($foundCluster === 'nairobi_metro') {
                $nairobiMetroStudents[] = $student->id;
                $regularClusterStudents[$foundCluster][] = $student->id;
            } else {
                $regularClusterStudents[$foundCluster][] = $student->id;
            }
        }

        $totalStudents = count($students);
        $totalLecturers = $lecturers->count();
        $AVERAGE = $totalStudents / max(1, $totalLecturers);
        $MIN_TARGET = max(1, (int) floor($AVERAGE) - 1);
        $MAX_TARGET = (int) ceil($AVERAGE) + 1;

        Log::info('=== TARGETS ===');
        Log::info("Total Students: {$totalStudents}");
        Log::info("Total Lecturers: {$totalLecturers}");
        Log::info("MIN Target: {$MIN_TARGET}");
        Log::info("MAX Target: {$MAX_TARGET}");

        $assignments = [];
        $lecturerList = [];
        foreach ($lecturers as $lecturer) {
            $id = $lecturer->id;
            $lecturerList[] = $id;
            $assignments[$id] = [
                'id' => $id,
                'name' => $lecturer->lecturer?->user?->name ?? 'Unknown',
                'student_ids' => [],
                'count' => 0,
                'clusters' => [],
                'is_mombasa_road' => false,
                'is_westlands' => false,
                'is_kasarani' => false,
            ];
        }

        $assignedIds = [];
        $coastLecturerId = null;

        if (!empty($coastStudents)) {
            $coastLecturerId = array_shift($lecturerList);
            $assignments[$coastLecturerId]['student_ids'] = $coastStudents;
            $assignments[$coastLecturerId]['count'] = count($coastStudents);
            $assignments[$coastLecturerId]['clusters'][] = 'coast_cluster';
            $assignedIds = array_merge($assignedIds, $coastStudents);
            Log::info('✓ Coast: ' . count($coastStudents) . " students to {$assignments[$coastLecturerId]['name']}");
        }

        Log::info('=== DISTRIBUTING REGULAR CLUSTERS ===');

        $mombasaRoadLecturers = [];
        $westlandsLecturers = [];
        $kasaraniLecturers = [];

        shuffle($lecturerList);
        $queue = $lecturerList;

        foreach ($regularClusterStudents as $clusterName => $studentIds) {
            if ($clusterName === 'nairobi_metro' || empty($studentIds) || empty($queue)) {
                continue;
            }

            $clusterSize = count($studentIds);
            $neededLecturers = min(count($queue), (int) ceil($clusterSize / $MIN_TARGET));

            Log::info("{$clusterName}: {$clusterSize} students to {$neededLecturers} lecturers");

            $perLecturer = (int) floor($clusterSize / $neededLecturers);
            $remainder = $clusterSize % $neededLecturers;

            $start = 0;
            for ($i = 0; $i < $neededLecturers; $i++) {
                if (empty($queue)) {
                    break;
                }

                $lid = array_shift($queue);
                $extra = ($i < $remainder) ? 1 : 0;
                $takeCount = $perLecturer + $extra;

                $takeIds = array_slice($studentIds, $start, $takeCount);
                $start += $takeCount;

                $assignments[$lid]['student_ids'] = array_merge($assignments[$lid]['student_ids'], $takeIds);
                $assignments[$lid]['count'] += count($takeIds);
                $assignments[$lid]['clusters'][] = $clusterName;
                $assignedIds = array_merge($assignedIds, $takeIds);

                if ($clusterName === 'mombasa_road_cluster') {
                    $mombasaRoadLecturers[] = $lid;
                    $assignments[$lid]['is_mombasa_road'] = true;
                } elseif ($clusterName === 'nairobi_westlands') {
                    $westlandsLecturers[] = $lid;
                    $assignments[$lid]['is_westlands'] = true;
                } elseif ($clusterName === 'kasarani_cluster') {
                    $kasaraniLecturers[] = $lid;
                    $assignments[$lid]['is_kasarani'] = true;
                }
            }
        }

        if (!empty($frontierStudents) && !empty($mombasaRoadLecturers)) {
            $frontierIds = array_diff($frontierStudents, $assignedIds);

            if (!empty($frontierIds)) {
                Log::info('=== ASSIGNING FRONTIER TO MOMBASA ROAD ===');

                $perLecturer = (int) ceil(count($frontierIds) / count($mombasaRoadLecturers));
                $start = 0;
                $frontierList = array_values($frontierIds);

                foreach ($mombasaRoadLecturers as $lid) {
                    if ($start >= count($frontierList)) {
                        break;
                    }

                    $takeCount = min($perLecturer, count($frontierList) - $start);
                    $takeIds = array_slice($frontierList, $start, $takeCount);
                    $start += $takeCount;

                    $assignments[$lid]['student_ids'] = array_merge($assignments[$lid]['student_ids'], $takeIds);
                    $assignments[$lid]['count'] += count($takeIds);
                    $assignments[$lid]['clusters'][] = 'frontier_cluster';
                    $assignedIds = array_merge($assignedIds, $takeIds);

                    Log::info("  → {$assignments[$lid]['name']} got {$takeCount} frontier students");
                }
            }
        }

        if (!empty($nairobiMetroStudents)) {
            $metroIds = array_values(array_diff($nairobiMetroStudents, $assignedIds));

            Log::info('=== NAIROBI METRO DISTRIBUTION (' . count($metroIds) . ' students) ===');

            if (!empty($metroIds)) {
                $start = 0;
                $totalMetro = count($metroIds);

                $emptyLecturers = [];
                foreach ($assignments as $lid => $ass) {
                    if ($lid === $coastLecturerId) {
                        continue;
                    }
                    if (empty($ass['clusters'])) {
                        $emptyLecturers[] = $lid;
                    }
                }

                $pureClusters = min((int) floor($totalMetro / $MIN_TARGET), count($emptyLecturers));

                for ($i = 0; $i < $pureClusters; $i++) {
                    $lid = $emptyLecturers[$i];
                    $takeCount = $MIN_TARGET;
                    $takeIds = array_slice($metroIds, $start, $takeCount);
                    $start += $takeCount;

                    $assignments[$lid]['student_ids'] = array_merge($assignments[$lid]['student_ids'], $takeIds);
                    $assignments[$lid]['count'] += count($takeIds);
                    $assignments[$lid]['clusters'][] = 'nairobi_metro';
                    $assignedIds = array_merge($assignedIds, $takeIds);

                    Log::info("  → [PURE] {$assignments[$lid]['name']} got pure Nairobi Metro cluster");
                }

                if ($start < $totalMetro) {
                    $remaining = array_slice($metroIds, $start);
                    $specialLecturers = array_unique(array_merge($mombasaRoadLecturers, $westlandsLecturers, $kasaraniLecturers));

                    if (!empty($specialLecturers)) {
                        $perLecturer = (int) ceil(count($remaining) / count($specialLecturers));
                        $remStart = 0;

                        foreach ($specialLecturers as $lid) {
                            if ($remStart >= count($remaining)) {
                                break;
                            }

                            $takeCount = min($perLecturer, count($remaining) - $remStart);
                            $takeIds = array_slice($remaining, $remStart, $takeCount);
                            $remStart += $takeCount;

                            $assignments[$lid]['student_ids'] = array_merge($assignments[$lid]['student_ids'], $takeIds);
                            $assignments[$lid]['count'] += count($takeIds);
                            if (!in_array('nairobi_metro', $assignments[$lid]['clusters'], true)) {
                                $assignments[$lid]['clusters'][] = 'nairobi_metro';
                            }
                            $assignedIds = array_merge($assignedIds, $takeIds);
                        }

                        $start = $totalMetro - (count($remaining) - $remStart);
                    }
                }

                if ($start < $totalMetro) {
                    $remaining = array_slice($metroIds, $start);
                    $allLecturers = array_diff(array_keys($assignments), [$coastLecturerId]);

                    foreach ($remaining as $sid) {
                        $lowestLid = null;
                        $lowestCount = PHP_INT_MAX;

                        foreach ($allLecturers as $lid) {
                            if ($assignments[$lid]['count'] < $lowestCount) {
                                $lowestCount = $assignments[$lid]['count'];
                                $lowestLid = $lid;
                            }
                        }

                        if ($lowestLid) {
                            $assignments[$lowestLid]['student_ids'][] = $sid;
                            $assignments[$lowestLid]['count']++;
                            $assignedIds[] = $sid;
                        }
                    }
                }
            }
        }

        $MAX_TARGET = $MIN_TARGET + 2;
        Log::info("=== INTRA-CLUSTER OPTIMIZATION: MAXIMUM = {$MAX_TARGET} ===");

        $lecturersByCluster = [];
        foreach ($assignments as $lid => $ass) {
            if ($lid === $coastLecturerId || empty($ass['clusters'])) {
                continue;
            }

            $primaryCluster = $ass['clusters'][0];
            $lecturersByCluster[$primaryCluster][] = [
                'id' => $lid,
                'name' => $ass['name'],
                'count' => $ass['count'],
                'student_ids' => $ass['student_ids'],
            ];
        }

        foreach ($lecturersByCluster as $cluster => $lecturersInCluster) {
            if (count($lecturersInCluster) <= 1) {
                Log::info("  {$cluster}: only one lecturer, skipping");
                continue;
            }

            Log::info("  Processing {$cluster} with " . count($lecturersInCluster) . ' lecturers');

            usort($lecturersInCluster, static fn ($a, $b) => $b['count'] <=> $a['count']);

            $totalClusterStudents = array_sum(array_column($lecturersInCluster, 'count'));
            $numLecturers = count($lecturersInCluster);

            $maxPossible = (int) floor($totalClusterStudents / $MAX_TARGET);
            $lecturersToKeep = min($numLecturers, $maxPossible);

            Log::info("    Total students in cluster: {$totalClusterStudents}");
            Log::info("    Can fill {$lecturersToKeep} lecturers to MAXIMUM ({$MAX_TARGET})");

            if ($lecturersToKeep >= $numLecturers) {
                $perLecturer = (int) floor($totalClusterStudents / $numLecturers);
                $remainder = $totalClusterStudents % $numLecturers;

                Log::info("    Giving each lecturer {$perLecturer} students, with {$remainder} extra");

                $studentIds = [];
                foreach ($lecturersInCluster as $l) {
                    $studentIds = array_merge($studentIds, $assignments[$l['id']]['student_ids']);
                    $assignments[$l['id']]['student_ids'] = [];
                    $assignments[$l['id']]['count'] = 0;
                }

                $start = 0;
                foreach ($lecturersInCluster as $index => $l) {
                    $extra = ($index < $remainder) ? 1 : 0;
                    $takeCount = $perLecturer + $extra;
                    $takeIds = array_slice($studentIds, $start, $takeCount);
                    $start += $takeCount;

                    $assignments[$l['id']]['student_ids'] = $takeIds;
                    $assignments[$l['id']]['count'] = count($takeIds);

                    Log::info("      → {$l['name']} now has {$assignments[$l['id']]['count']} students");
                }
            } else {
                Log::info("    Filling {$lecturersToKeep} lecturers to MAXIMUM, rest to ONE survivor");

                $allStudentIds = [];
                foreach ($lecturersInCluster as $l) {
                    $allStudentIds = array_merge($allStudentIds, $assignments[$l['id']]['student_ids']);
                    $assignments[$l['id']]['student_ids'] = [];
                    $assignments[$l['id']]['count'] = 0;
                }

                $start = 0;
                for ($i = 0; $i < $lecturersToKeep; $i++) {
                    $lid = $lecturersInCluster[$i]['id'];
                    $takeCount = $MAX_TARGET;
                    $takeIds = array_slice($allStudentIds, $start, $takeCount);
                    $start += $takeCount;

                    $assignments[$lid]['student_ids'] = $takeIds;
                    $assignments[$lid]['count'] = count($takeIds);

                    Log::info("      → [MAX] {$lecturersInCluster[$i]['name']} now has {$assignments[$lid]['count']} students");
                }

                if ($start < count($allStudentIds)) {
                    $survivorIndex = $lecturersToKeep;
                    if ($survivorIndex < count($lecturersInCluster)) {
                        $lid = $lecturersInCluster[$survivorIndex]['id'];
                        $remainingIds = array_slice($allStudentIds, $start);

                        $assignments[$lid]['student_ids'] = $remainingIds;
                        $assignments[$lid]['count'] = count($remainingIds);

                        Log::info("      → [SURVIVOR] {$lecturersInCluster[$survivorIndex]['name']} now has {$assignments[$lid]['count']} students");

                        for ($j = $survivorIndex + 1, $jMax = count($lecturersInCluster); $j < $jMax; $j++) {
                            $assignments[$lecturersInCluster[$j]['id']]['student_ids'] = [];
                            $assignments[$lecturersInCluster[$j]['id']]['count'] = 0;
                            $assignments[$lecturersInCluster[$j]['id']]['clusters'] = [];

                            Log::info("      → [EMPTY] {$lecturersInCluster[$j]['name']} now has 0 students");
                        }
                    }
                }
            }
        }

        $needBalancing = [];
        $clustersBalanced = [];

        foreach ($assignments as $lid => $ass) {
            if ($lid === $coastLecturerId || empty($ass['clusters']) || $ass['count'] === 0 || $ass['count'] >= $MIN_TARGET) {
                continue;
            }

            $primaryCluster = $ass['clusters'][0];

            if (!in_array($primaryCluster, $clustersBalanced, true)) {
                $needBalancing[] = [
                    'lecturer_id' => $lid,
                    'name' => $ass['name'],
                    'cluster' => $primaryCluster,
                    'needed' => $MIN_TARGET - $ass['count'],
                    'current' => $ass['count'],
                ];
                $clustersBalanced[] = $primaryCluster;

                Log::info("  → [NEEDS NYERI] {$ass['name']} from {$primaryCluster} needs " . ($MIN_TARGET - $ass['count']) . ' students');
            }
        }

        if (!empty($nyeriStudents)) {
            $nyeriIds = array_values(array_diff($nyeriStudents, $assignedIds));

            Log::info('=== NYERI DISTRIBUTION - ' . count($nyeriIds) . ' students available ===');

            $start = 0;
            $totalNyeri = count($nyeriIds);

            if (!empty($needBalancing)) {
                Log::info('  Phase 1: Giving to ' . count($needBalancing) . ' lecturers who need balancing');

                foreach ($needBalancing as $data) {
                    if ($start >= $totalNyeri) {
                        break;
                    }

                    $takeCount = min($data['needed'], $totalNyeri - $start);
                    $takeIds = array_slice($nyeriIds, $start, $takeCount);
                    $start += $takeCount;

                    $assignments[$data['lecturer_id']]['student_ids'] = array_merge($assignments[$data['lecturer_id']]['student_ids'], $takeIds);
                    $assignments[$data['lecturer_id']]['count'] += count($takeIds);
                    $assignments[$data['lecturer_id']]['clusters'][] = 'nyeri_balancing';
                    $assignedIds = array_merge($assignedIds, $takeIds);

                    Log::info("    → [BALANCE] {$data['name']} got {$takeCount} Nyeri students");
                }
            }

            if ($start < $totalNyeri) {
                $remainingNyeri = array_slice($nyeriIds, $start);
                Log::info('  Phase 2: ' . count($remainingNyeri) . ' Nyeri left - creating pure clusters');

                $emptyLecturers = [];
                foreach ($assignments as $lid => $ass) {
                    if ($lid === $coastLecturerId) {
                        continue;
                    }
                    if ($ass['count'] === 0) {
                        $emptyLecturers[] = $lid;
                    }
                }

                if (!empty($emptyLecturers)) {
                    $pureStart = 0;

                    foreach ($emptyLecturers as $lid) {
                        if ($pureStart >= count($remainingNyeri)) {
                            break;
                        }

                        $takeCount = min($MIN_TARGET, count($remainingNyeri) - $pureStart);
                        $takeIds = array_slice($remainingNyeri, $pureStart, $takeCount);
                        $pureStart += $takeCount;

                        $assignments[$lid]['student_ids'] = array_merge($assignments[$lid]['student_ids'], $takeIds);
                        $assignments[$lid]['count'] += count($takeIds);
                        $assignments[$lid]['clusters'][] = 'nyeri_mt_kenya_cluster';
                        $assignedIds = array_merge($assignedIds, $takeIds);

                        Log::info("    → [PURE] {$assignments[$lid]['name']} got pure Nyeri cluster ({$takeCount} students)");
                    }
                }
            }
        }

        $allStudentIds = $students->pluck('id')->toArray();
        $finalAssignedIds = [];
        foreach ($assignments as $ass) {
            $finalAssignedIds = array_merge($finalAssignedIds, $ass['student_ids']);
        }

        $missingStudents = array_diff($allStudentIds, $finalAssignedIds);

        if (!empty($missingStudents)) {
            Log::warning('⚠️ ' . count($missingStudents) . ' students still unassigned! Final emergency assignment...');

            $allLecturers = array_diff(array_keys($assignments), [$coastLecturerId]);

            foreach ($missingStudents as $sid) {
                $lowestLid = null;
                $lowestCount = PHP_INT_MAX;

                foreach ($allLecturers as $lid) {
                    if ($assignments[$lid]['count'] < $lowestCount) {
                        $lowestCount = $assignments[$lid]['count'];
                        $lowestLid = $lid;
                    }
                }

                if ($lowestLid) {
                    $assignments[$lowestLid]['student_ids'][] = $sid;
                    $assignments[$lowestLid]['count']++;
                }
            }
        }

        $batch = (int) LecturerAssigment::where([
            'attachment_id' => $request->attachment_id,
            'department_id' => $request->department_id,
        ])->max('batch') + 1;

        DB::transaction(static function () use ($assignments, $request, $batch, $studentsById) {
            $inserts = [];

            foreach ($assignments as $lid => $ass) {
                if (empty($ass['student_ids'])) {
                    continue;
                }

                foreach ($ass['student_ids'] as $sid) {
                    $inserts[] = [
                        'attachment_id' => $request->attachment_id,
                        'department_id' => $request->department_id,
                        'attachment_student_id' => $sid,
                        'attachment_lecturer_id' => $lid,
                        'company_id' => $studentsById[$sid]->company_id ?? null,
                        'batch' => $batch,
                        'created_by' => Auth::id(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                AttachmentStudent::whereIn('id', $ass['student_ids'])->update([
                    'attachment_lecturer_id' => $lid,
                ]);
            }

            if (!empty($inserts)) {
                LecturerAssigment::insert($inserts);
            }
        });

        $totalAssigned = 0;
        $lecturersWithStudents = 0;
        $meetMinimum = 0;

        foreach ($assignments as $ass) {
            $totalAssigned += $ass['count'];
            if ($ass['count'] > 0) {
                $lecturersWithStudents++;
            }
            if ($ass['count'] >= $MIN_TARGET || $ass['id'] === $coastLecturerId) {
                $meetMinimum++;
            }
        }

        Log::info('=== FINAL STATS ===');
        Log::info("Total assigned: {$totalAssigned}/" . count($students));
        Log::info("Lecturers with students: {$lecturersWithStudents}/" . count($lecturers));

        return response()->json([
            'status' => 'success',
            'message' => "✅ All {$totalAssigned} students assigned to {$lecturersWithStudents} lecturers",
            'summary' => [
                'total_students' => $totalAssigned,
                'total_lecturers' => count($lecturers),
                'lecturers_with_students' => $lecturersWithStudents,
                'min_target' => $MIN_TARGET,
                'max_target' => $MAX_TARGET,
            ],
        ]);
    }
}
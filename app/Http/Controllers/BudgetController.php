<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class BudgetController extends Controller
{
    private const DEFAULT_TRANSPORT_COST = 800;

    /**
     * Regional clusters with default transport rates and town matches.
     */
    private const CLUSTERS = [
        'nairobi_metro' => [
            'cost' => 9000,
            'towns' => ['cbd', 'upperhill', 'nairobi'],
        ],
        'nairobi_westlands' => [
            'cost' => 9000,
            'towns' => ['westlands', 'parklands', 'kilimani', 'kileleshwa', 'kangemi', 'loresho'],
        ],
        'thika_road' => [
            'cost' => 9000,
            'towns' => ['thika', 'makuyu', 'ruiru', 'juja', 'limuru', 'kiambu', 'gikuyu'],
        ],
        'kasarani_cluster' => [
            'cost' => 9000,
            'towns' => ['kasarani', 'roysambu', 'githurai', 'kahawa', 'zimmerman', 'muthaiga'],
        ],
        'mombasa_road_cluster' => [
            'cost' => 9000,
            'towns' => [
                'machakos', 'kangundo-tala', 'matuu', 'kitui', 'mwingi', 'makueni', 'mombasa road',
                'donholm', 'eastleigh', 'embakasi', 'umoja', 'athi river', 'kitengela', 'pipeline',
                'south b', 'south c', 'buruburu',
            ],
        ],
        'nakuru_cluster' => [
            'cost' => 13000,
            'towns' => ['nakuru', 'naivasha', 'nyahururu', 'gilgil', 'molo', 'eldama ravine', 'ol kalou', 'rumuruti', 'nanyuki'],
        ],
        'western_cluster' => [
            'cost' => 17000,
            'towns' => [
                'kakamega', 'mumias', 'kisumu', 'homa bay', 'muhoroni', 'vihiga', 'bungoma', 'webuye',
                'kimilili', 'malakisi', 'busia', 'malaba', 'mbale', 'eldoret', 'siaya', 'bondo',
                'ugunja', 'ukwala', 'yala', 'nyamira', 'nyansiongo', 'migori', 'rongo', 'kisii',
            ],
        ],
        'nyeri_mt_kenya_cluster' => [
            'cost' => 9000,
            'towns' => ['nyeri', 'othaya', 'mweiga', 'maralal'],
        ],
        'meru_embu_cluster' => [
            'cost' => 9000,
            'towns' => [
                'meru', 'maua', 'karatina', 'chuka', 'embu', 'ol kalou', 'kenol', 'mwea',
                'sagana', 'muranga', 'maragua', 'kangema', 'runyenjes', 'kerugoya', 'kutus', 'kirinyaga',
            ],
        ],
        'coast_cluster' => [
            'cost' => 17000,
            'towns' => ['mombasa', 'mtwapa', 'kilifi', 'malindi', 'voi', 'mariakani', 'ukunda', 'likoni', 'nyali', 'changamwe'],
        ],
        'frontier_cluster' => [
            'cost' => 14000,
            'towns' => ['garissa', 'wajir', 'mandera', 'isiolo', 'moyale', 'lodwar', 'kakuma', 'samburu'],
        ],
        'kericho_south_rift_cluster' => [
            'cost' => 15000,
            'towns' => [
                'kericho', 'litein', 'kipkelion', 'londiani', 'bomet', 'narok', 'iten', 'tambach',
                'nandi hills', 'kapsabet', 'kitale', 'kabarnet',
            ],
        ],
    ];

    /**
     * Calculate and display attachment lecturer travel and subsistence budgets.
     */
    public function budgets(): View
    {
        $attachmentLecturers = DB::table('attachment_lecturers')
            ->join('lecturers', 'lecturers.id', '=', 'attachment_lecturers.lecturer_id')
            ->join('users', 'users.id', '=', 'lecturers.user_id')
            ->select('attachment_lecturers.*', 'users.name as real_name')
            ->get();

        $jobGrades = DB::table('job_grades')
            ->pluck('daily_allowance', 'dekut_grade')
            ->all();

        // Batch fetch all lecturer visits in a single query to eliminate the N+1 problem
        $allVisits = DB::table('lecturer_assignments')
            ->join('companies', 'companies.id', '=', 'lecturer_assignments.company_id')
            ->whereIn('lecturer_assignments.attachment_lecturer_id', $attachmentLecturers->pluck('id'))
            ->select(
                'lecturer_assignments.attachment_lecturer_id',
                'companies.address as town',
                DB::raw('COUNT(*) as students_count')
            )
            ->groupBy('lecturer_assignments.attachment_lecturer_id', 'companies.address')
            ->get()
            ->groupBy('attachment_lecturer_id');

        foreach ($attachmentLecturers as $al) {
            $rate = $jobGrades[$al->job_grade] ?? 0;
            $visits = $allVisits->get($al->id, collect());

            foreach ($visits as $visit) {
                $cluster = $this->getClusterName((string) $visit->town);
                $visit->cluster = $cluster;
                $visit->transport_cost = self::CLUSTERS[$cluster]['cost'] ?? self::DEFAULT_TRANSPORT_COST;
            }

            $clusteredVisits = $visits->groupBy('cluster')->map(static function ($clusterVisits, $cluster) {
                return [
                    'cluster' => $cluster,
                    'total_students' => $clusterVisits->sum('students_count'),
                    'towns' => $clusterVisits->pluck('town')->map(static function ($town) {
                        return str_replace([' Road', ' Rd', ' Street', ' St'], '', (string) $town);
                    })->unique()->values()->all(),
                    'transport_cost' => $clusterVisits->first()->transport_cost ?? 0,
                ];
            })->values()->all();

            $uniqueClusters = $visits->pluck('cluster')->unique()->values()->all();

            $primaryCluster = $visits->groupBy('cluster')
                ->map(static fn ($group) => $group->sum('students_count'))
                ->sortDesc()
                ->keys()
                ->first();

            $totalTransport = 0;
            if ($primaryCluster !== null) {
                $visitInPrimary = $visits->firstWhere('cluster', $primaryCluster);
                if ($visitInPrimary) {
                    $totalTransport = $visitInPrimary->transport_cost;

                    if ($primaryCluster === 'mombasa_road_cluster' && in_array('frontier_cluster', $uniqueClusters, true)) {
                        $totalTransport = 14000;
                    }
                }
            }

            $townCount = $visits->count();

            $al->lecturer_name = $al->real_name;
            $al->dekut_grade = $al->job_grade ?? 'N/A';
            $al->assessmentVisits = $visits;
            $al->clusteredVisits = $clusteredVisits;
            $al->unique_cluster_count = count($uniqueClusters);
            $al->primary_cluster = $primaryCluster;
            $al->town_count = $townCount;
            $al->daily_rate_used = $rate;
            $al->total_subsistence = $townCount * $rate;
            $al->total_transport = $totalTransport;
        }

        return view('admin.budgets', ['attachment_lecturers' => $attachmentLecturers]);
    }

    /**
     * Determine region cluster name based on location address.
     */
    private function getClusterName(string $address): string
    {
        $location = strtolower($address);
        $location = str_replace([' road', ' rd', 'street', ' st', 'avenue', ' ave', 'lane', ' ln'], '', $location);
        $location = trim($location);

        foreach (self::CLUSTERS as $clusterName => $data) {
            foreach ($data['towns'] as $town) {
                if (str_contains($location, $town)) {
                    return $clusterName;
                }
            }
        }

        $firstWord = explode(' ', $location)[0] ?? '';
        if ($firstWord !== '') {
            foreach (self::CLUSTERS as $clusterName => $data) {
                foreach ($data['towns'] as $town) {
                    if ($firstWord === $town || str_contains($town, $firstWord)) {
                        return $clusterName;
                    }
                }
            }
        }

        return 'uncategorized';
    }
}
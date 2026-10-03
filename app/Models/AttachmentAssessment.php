<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttachmentAssessment extends Model
{
    use HasFactory;

    /**
     * Assessment mark field definitions for lecturer evaluation.
     *
     * @var array<int, string>
     */
    public const LECTURER_MARK_FIELDS = [
        'practical_orientation_marks',
        'intellectual_activity_marks',
        'independence_marks',
        'communication_marks',
        'technology_and_skills_marks',
        'innovativeness_marks',
    ];

    /**
     * Assessment mark field definitions for industrial supervisor evaluation.
     *
     * @var array<int, string>
     */
    public const INDUSTRIAL_SUPERVISOR_MARK_FIELDS = [
        'punctuality_marks',
        'attendance_marks',
        'basic_skills_marks',
        'general_office_applications_marks',
        'technical_applications_marks',
        'area_of_specialization_marks',
        'scientific_and_technical_knowledge_marks',
        'intelligence_marks',
        'learning_ability_marks',
        'responsibility_acceptance_marks',
        'acceptability_to_colleagues_marks',
        'improvisation_marks',
        'environment_adjustment_marks',
        'dependability_and_reliability_marks',
        'organization_and_planning_marks',
        'effective_time_use_marks',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'attachment_student_id',
        'student_id',
        'lecturer_id',
        'industrial_supervisor_id',

        // Academic Lecturer Criteria
        'practical_orientation_marks',
        'practical_orientation_remarks',
        'intellectual_activity_marks',
        'intellectual_activity_remarks',
        'independence_marks',
        'independence_remarks',
        'communication_marks',
        'communication_remarks',
        'technology_and_skills_marks',
        'technology_and_skills_remarks',
        'innovativeness_marks',
        'innovativeness_remarks',

        // Industrial Supervisor Criteria
        'punctuality_marks',
        'punctuality_remarks',
        'attendance_marks',
        'attendance_remarks',
        'basic_skills_marks',
        'basic_skills_remarks',
        'general_office_applications_marks',
        'general_office_applications_remarks',
        'technical_applications_marks',
        'technical_applications_remarks',
        'area_of_specialization_marks',
        'area_of_specialization_remarks',
        'scientific_and_technical_knowledge_marks',
        'scientific_and_technical_knowledge_remarks',
        'intelligence_marks',
        'intelligence_remarks',
        'learning_ability_marks',
        'learning_ability_remarks',
        'responsibility_acceptance_marks',
        'responsibility_acceptance_remarks',
        'acceptability_to_colleagues_marks',
        'acceptability_to_colleagues_remarks',
        'improvisation_marks',
        'improvisation_remarks',
        'environment_adjustment_marks',
        'environment_adjustment_remarks',
        'dependability_and_reliability_marks',
        'dependability_and_reliability_remarks',
        'organization_and_planning_marks',
        'organization_and_planning_remarks',
        'effective_time_use_marks',
        'effective_time_use_remarks',

        // Summary Aggregates
        'total_marks',
        'overall_remarks',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'attachment_student_id'                     => 'integer',
        'student_id'                                => 'integer',
        'lecturer_id'                               => 'integer',
        'industrial_supervisor_id'                  => 'integer',
        'practical_orientation_marks'               => 'float',
        'intellectual_activity_marks'               => 'float',
        'independence_marks'                        => 'float',
        'communication_marks'                       => 'float',
        'technology_and_skills_marks'               => 'float',
        'innovativeness_marks'                      => 'float',
        'punctuality_marks'                         => 'float',
        'attendance_marks'                          => 'float',
        'basic_skills_marks'                        => 'float',
        'general_office_applications_marks'         => 'float',
        'technical_applications_marks'              => 'float',
        'area_of_specialization_marks'              => 'float',
        'scientific_and_technical_knowledge_marks' => 'float',
        'intelligence_marks'                        => 'float',
        'learning_ability_marks'                    => 'float',
        'responsibility_acceptance_marks'           => 'float',
        'acceptability_to_colleagues_marks'         => 'float',
        'improvisation_marks'                       => 'float',
        'environment_adjustment_marks'              => 'float',
        'dependability_and_reliability_marks'       => 'float',
        'organization_and_planning_marks'           => 'float',
        'effective_time_use_marks'                  => 'float',
        'total_marks'                               => 'float',
    ];

    /**
     * Get the student profile associated with this assessment.
     *
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    /**
     * Get the student attachment session associated with this assessment.
     *
     * @return BelongsTo<AttachmentStudent, $this>
     */
    public function attachmentStudent(): BelongsTo
    {
        return $this->belongsTo(AttachmentStudent::class, 'attachment_student_id');
    }

    /**
     * Get the visiting academic lecturer who submitted the assessment.
     *
     * @return BelongsTo<Lecturer, $this>
     */
    public function lecturer(): BelongsTo
    {
        return $this->belongsTo(Lecturer::class, 'lecturer_id');
    }

    /**
     * Get the industrial supervisor who submitted the assessment.
     *
     * @return BelongsTo<IndustrialSupervisor, $this>
     */
    public function industrialSupervisor(): BelongsTo
    {
        return $this->belongsTo(IndustrialSupervisor::class, 'industrial_supervisor_id');
    }

    /**
     * Accessor for calculating the cumulative lecturer score.
     */
    public function getLecturerTotalMarksAttribute(): float
    {
        return (float) array_reduce(
            self::LECTURER_MARK_FIELDS,
            fn (float $carry, string $field) => $carry + (float) ($this->attributes[$field] ?? 0),
            0.0
        );
    }

    /**
     * Accessor for calculating the cumulative industrial supervisor score.
     */
    public function getIndustrialSupervisorTotalMarksAttribute(): float
    {
        return (float) array_reduce(
            self::INDUSTRIAL_SUPERVISOR_MARK_FIELDS,
            fn (float $carry, string $field) => $carry + (float) ($this->attributes[$field] ?? 0),
            0.0
        );
    }

    /**
     * Accessor for calculating overall combined assessment marks.
     */
    public function getCalculatedTotalMarksAttribute(): float
    {
        return $this->lecturer_total_marks + $this->industrial_supervisor_total_marks;
    }
}
<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\LetterTemplate;
use App\Models\User;

/**
 * Seed letter templates:
 * 1. introductory_letter - university-wide template
 * 2. completion_letter - university-wide template
 */
class LetterTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create an admin user as creator
        $admin = User::where('role', 'admin')->first();
        if (!$admin) {
            $admin = User::where('email', 'admin@dams.com')->first();
        }
        
        $creatorId = $admin?->id ?? 1;

        // Introductory Letter Template
        LetterTemplate::updateOrCreate(
            ['name' => 'introductory_letter'],
            [
                'department_id'   => null, // University-wide
                'template_body'   => $this->getIntroductoryLetterTemplate(),
                'variables_schema' => $this->getIntroductoryLetterVariables(),
                'version'         => 1,
                'is_active'       => true,
                'created_by'      => $creatorId,
            ]
        );

        // Completion Letter Template
        LetterTemplate::updateOrCreate(
            ['name' => 'completion_letter'],
            [
                'department_id'   => null, // University-wide
                'template_body'   => $this->getCompletionLetterTemplate(),
                'variables_schema' => $this->getCompletionLetterVariables(),
                'version'         => 1,
                'is_active'       => true,
                'created_by'      => $creatorId,
            ]
        );

        $this->command?->info('Letter templates seeded successfully.');
    }

    /**
     * Get introductory letter template body (Jinja2/Handlebars syntax).
     */
    private function getIntroductoryLetterTemplate(): string
    {
        return <<<'TEMPLATE'
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'DejaVu Sans', Arial, sans-serif; margin: 40px; line-height: 1.6; }
        .header { text-align: center; margin-bottom: 30px; }
        .logo { max-width: 120px; margin-bottom: 10px; }
        .university-name { font-size: 18px; font-weight: bold; color: #1a3c5e; margin: 5px 0; }
        .dept-name { font-size: 14px; color: #444; margin: 2px 0; }
        .ref-line { font-size: 12px; color: #666; margin-top: 10px; }
        .date-line { text-align: right; margin-bottom: 20px; }
        .subject { text-align: center; font-weight: bold; text-decoration: underline; margin: 20px 0; }
        .salutation { margin-bottom: 20px; }
        .body-text { text-align: justify; margin-bottom: 15px; }
        .student-details { background: #f5f5f5; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .detail-row { display: flex; margin: 8px 0; }
        .detail-label { font-weight: bold; width: 200px; flex-shrink: 0; }
        .closing { margin-top: 40px; }
        .signature-block { margin-top: 60px; }
        .signature-line { border-bottom: 1px solid #000; width: 250px; margin-bottom: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ university_logo_url }}" alt="University Logo" class="logo">
        <div class="university-name">{{ university_name }}</div>
        <div class="dept-name">{{ department_name }}</div>
        <div class="dept-name">{{ department_address }}</div>
        <div class="dept-name">{{ department_phone }} | {{ department_email }}</div>
        <div class="ref-line">Ref: {{ reference_number }}</div>
    </div>

    <div class="date-line">
        <strong>Date:</strong> {{ letter_date }}
    </div>

    <div class="subject">
        INTRODUCTORY LETTER FOR INDUSTRIAL ATTACHMENT
    </div>

    <div class="salutation">
        Dear Sir/Madam,<br>
        <strong>{{ company_name }}</strong>
    </div>

    <div class="body-text">
        <p>
            I write to introduce <strong>{{ student_name }}</strong> (Registration No: <strong>{{ reg_no }}</strong>),
            a student of <strong>{{ program_name }}</strong> in the <strong>{{ department_name }}</strong>,
            who is required to undertake industrial attachment as part of the curriculum requirements
            for the award of {{ degree_name }}.
        </p>

        <p>
            The attachment period is scheduled from <strong>{{ attachment_start_date }}</strong> to
            <strong>{{ attachment_end_date }}</strong> ({{ duration_weeks }} weeks).
            During this period, the student is expected to gain practical experience in
            <strong>{{ field_of_study }}</strong> and related areas.
        </p>

        <p>
            The university would be grateful if you could provide the student with an opportunity
            to acquire hands-on experience under the supervision of your qualified personnel.
            The student will be expected to conform to your organization's rules and regulations
            and will be under the general supervision of:
        </p>

        <div class="student-details">
            <div class="detail-row">
                <span class="detail-label">Student Name:</span>
                <span>{{ student_name }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Registration Number:</span>
                <span>{{ reg_no }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Program:</span>
                <span>{{ program_name }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Year of Study:</span>
                <span>{{ year_of_study }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Attachment Period:</span>
                <span>{{ attachment_start_date }} to {{ attachment_end_date }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Duration:</span>
                <span>{{ duration_weeks }} weeks</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Field of Study:</span>
                <span>{{ field_of_study }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">University Supervisor:</span>
                <span>{{ supervisor_name }} ({{ supervisor_email }})</span>
            </div>
        </div>

        <p>
            The university supervisor will visit the student periodically to assess progress.
            We would appreciate it if you could assign an industrial supervisor to oversee
            the student's daily activities and provide guidance.
        </p>

        <p>
            Please confirm acceptance of this student by signing and returning the attached
            acceptance form, or by email to {{ department_email }}.
        </p>

        <p>
            For any queries, please contact the undersigned or the department office.
        </p>

        <div class="closing">
            Yours faithfully,<br><br>
        </div>

        <div class="signature-block">
            <div class="signature-line"></div>
            <strong>{{ hod_name }}</strong><br>
            Head of Department - {{ department_name }}<br>
            {{ university_name }}<br>
            {{ hod_phone }} | {{ hod_email }}
        </div>
    </body>
</html>
TEMPLATE;
    }

    /**
     * Get introductory letter variables schema.
     */
    private function getIntroductoryLetterVariables(): array
    {
        return [
            'required' => [
                'university_name',
                'university_logo_url',
                'department_name',
                'department_address',
                'department_phone',
                'department_email',
                'reference_number',
                'letter_date',
                'company_name',
                'student_name',
                'reg_no',
                'program_name',
                'degree_name',
                'year_of_study',
                'field_of_study',
                'attachment_start_date',
                'attachment_end_date',
                'duration_weeks',
                'supervisor_name',
                'supervisor_email',
                'hod_name',
                'hod_phone',
                'hod_email',
            ],
            'optional' => [],
            'types' => [
                'university_name'         => 'string',
                'university_logo_url'     => 'url',
                'department_name'         => 'string',
                'department_address'      => 'string',
                'department_phone'        => 'string',
                'department_email'        => 'email',
                'reference_number'        => 'string',
                'letter_date'             => 'date',
                'company_name'            => 'string',
                'student_name'            => 'string',
                'reg_no'                  => 'string',
                'program_name'            => 'string',
                'degree_name'             => 'string',
                'year_of_study'           => 'string',
                'field_of_study'          => 'string',
                'attachment_start_date'   => 'date',
                'attachment_end_date'     => 'date',
                'duration_weeks'          => 'integer',
                'supervisor_name'         => 'string',
                'supervisor_email'        => 'email',
                'hod_name'                => 'string',
                'hod_phone'               => 'string',
                'hod_email'               => 'email',
            ],
            'descriptions' => [
                'university_name'         => 'Full name of the university',
                'university_logo_url'     => 'URL to university logo image',
                'department_name'         => 'Name of the academic department',
                'department_address'      => 'Physical address of the department',
                'department_phone'        => 'Department contact phone number',
                'department_email'        => 'Department contact email',
                'reference_number'        => 'Letter reference number',
                'letter_date'             => 'Date the letter is issued',
                'company_name'            => 'Name of the host company',
                'student_name'            => 'Full name of the student',
                'reg_no'                  => 'Student registration number',
                'program_name'            => 'Academic program name',
                'degree_name'             => 'Degree being pursued',
                'year_of_study'           => 'Current year of study',
                'field_of_study'          => 'Field/specialization of study',
                'attachment_start_date'   => 'Start date of attachment',
                'attachment_end_date'     => 'End date of attachment',
                'duration_weeks'          => 'Duration in weeks',
                'supervisor_name'         => 'University supervisor name',
                'supervisor_email'        => 'University supervisor email',
                'hod_name'                => 'Head of Department name',
                'hod_phone'               => 'HOD contact phone',
                'hod_email'               => 'HOD contact email',
            ],
        ];
    }

    /**
     * Get completion letter template body.
     */
    private function getCompletionLetterTemplate(): string
    {
        return <<<'TEMPLATE'
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'DejaVu Sans', Arial, sans-serif; margin: 40px; line-height: 1.6; }
        .header { text-align: center; margin-bottom: 30px; }
        .logo { max-width: 120px; margin-bottom: 10px; }
        .university-name { font-size: 18px; font-weight: bold; color: #1a3c5e; margin: 5px 0; }
        .dept-name { font-size: 14px; color: #444; margin: 2px 0; }
        .ref-line { font-size: 12px; color: #666; margin-top: 10px; }
        .date-line { text-align: right; margin-bottom: 20px; }
        .subject { text-align: center; font-weight: bold; text-decoration: underline; margin: 20px 0; }
        .salutation { margin-bottom: 20px; }
        .body-text { text-align: justify; margin-bottom: 15px; }
        .student-details { background: #f5f5f5; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .detail-row { display: flex; margin: 8px 0; }
        .detail-label { font-weight: bold; width: 200px; flex-shrink: 0; }
        .performance-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .performance-table th, .performance-table td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        .performance-table th { background-color: #1a3c5e; color: white; }
        .closing { margin-top: 40px; }
        .signature-block { margin-top: 60px; }
        .signature-line { border-bottom: 1px solid #000; width: 250px; margin-bottom: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ university_logo_url }}" alt="University Logo" class="logo">
        <div class="university-name">{{ university_name }}</div>
        <div class="dept-name">{{ department_name }}</div>
        <div class="dept-name">{{ department_address }}</div>
        <div class="dept-name">{{ department_phone }} | {{ department_email }}</div>
        <div class="ref-line">Ref: {{ reference_number }}</div>
    </div>

    <div class="date-line">
        <strong>Date:</strong> {{ letter_date }}
    </div>

    <div class="subject">
        CERTIFICATE OF COMPLETION OF INDUSTRIAL ATTACHMENT
    </div>

    <div class="salutation">
        To Whom It May Concern,<br>
        <strong>{{ company_name }}</strong>
    </div>

    <div class="body-text">
        <p>
            This is to certify that <strong>{{ student_name }}</strong> (Registration No: <strong>{{ reg_no }}</strong>),
            a student of <strong>{{ program_name }}</strong> in the <strong>{{ department_name }}</strong>,
            has successfully completed the mandatory industrial attachment at your organization
            from <strong>{{ attachment_start_date }}</strong> to <strong>{{ attachment_end_date }}</strong>.
        </p>

        <p>
            During the attachment period, the student was attached to the <strong>{{ department_assigned }}</strong>
            department/section under the supervision of <strong>{{ industrial_supervisor_name }}</strong>.
            The student demonstrated satisfactory performance and acquired practical skills in
            the following areas:
        </p>

        <table class="performance-table">
            <thead>
                <tr>
                    <th>Competency Area</th>
                    <th>Rating (1-5)</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                <tr><td>Technical Knowledge</td><td>{{ rating_technical }}</td><td>{{ remarks_technical }}</td></tr>
                <tr><td>Practical Skills</td><td>{{ rating_practical }}</td><td>{{ remarks_practical }}</td></tr>
                <tr><td>Problem Solving</td><td>{{ rating_problem_solving }}</td><td>{{ remarks_problem_solving }}</td></tr>
                <tr><td>Communication</td><td>{{ rating_communication }}</td><td>{{ remarks_communication }}</td></tr>
                <tr><td>Teamwork</td><td>{{ rating_teamwork }}</td><td>{{ remarks_teamwork }}</td></tr>
                <tr><td>Initiative</td><td>{{ rating_initiative }}</td><td>{{ remarks_initiative }}</td></tr>
                <tr><td>Punctuality & Attendance</td><td>{{ rating_attendance }}</td><td>{{ remarks_attendance }}</td></tr>
                <tr><td>Professional Conduct</td><td>{{ rating_conduct }}</td><td>{{ remarks_conduct }}</td></tr>
            </tbody>
        </table>

        <p>
            Overall Performance Rating: <strong>{{ overall_rating }}/5</strong><br>
            Supervisor's Comments: {{ overall_remarks }}
        </p>

        <div class="student-details">
            <div class="detail-row">
                <span class="detail-label">Student Name:</span>
                <span>{{ student_name }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Registration Number:</span>
                <span>{{ reg_no }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Program:</span>
                <span>{{ program_name }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Host Company:</span>
                <span>{{ company_name }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Department Assigned:</span>
                <span>{{ department_assigned }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Industrial Supervisor:</span>
                <span>{{ industrial_supervisor_name }} ({{ industrial_supervisor_email }})</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Attachment Period:</span>
                <span>{{ attachment_start_date }} to {{ attachment_end_date }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Total Weeks Completed:</span>
                <span>{{ weeks_completed }}</span>
            </div>
        </div>

        <p>
            The university acknowledges the valuable contribution of your organization
            towards the practical training of our students. We look forward to continued
            collaboration.
        </p>

        <div class="closing">
            Yours sincerely,<br><br>
        </div>

        <div class="signature-block">
            <div class="signature-line"></div>
            <strong>{{ dean_name }}</strong><br>
            Dean, School of {{ school_name }}<br>
            {{ university_name }}<br>
            {{ dean_phone }} | {{ dean_email }}
        </div>
    </body>
</html>
TEMPLATE;
    }

    /**
     * Get completion letter variables schema.
     */
    private function getCompletionLetterVariables(): array
    {
        return [
            'required' => [
                'university_name',
                'university_logo_url',
                'department_name',
                'department_address',
                'department_phone',
                'department_email',
                'reference_number',
                'letter_date',
                'company_name',
                'student_name',
                'reg_no',
                'program_name',
                'department_assigned',
                'industrial_supervisor_name',
                'industrial_supervisor_email',
                'attachment_start_date',
                'attachment_end_date',
                'weeks_completed',
                'overall_rating',
                'overall_remarks',
                'dean_name',
                'school_name',
                'dean_phone',
                'dean_email',
            ],
            'optional' => [
                'rating_technical',
                'remarks_technical',
                'rating_practical',
                'remarks_practical',
                'rating_problem_solving',
                'remarks_problem_solving',
                'rating_communication',
                'remarks_communication',
                'rating_teamwork',
                'remarks_teamwork',
                'rating_initiative',
                'remarks_initiative',
                'rating_attendance',
                'remarks_attendance',
                'rating_conduct',
                'remarks_conduct',
            ],
            'types' => [
                'university_name'             => 'string',
                'university_logo_url'         => 'url',
                'department_name'             => 'string',
                'department_address'          => 'string',
                'department_phone'            => 'string',
                'department_email'            => 'email',
                'reference_number'            => 'string',
                'letter_date'                 => 'date',
                'company_name'                => 'string',
                'student_name'                => 'string',
                'reg_no'                      => 'string',
                'program_name'                => 'string',
                'department_assigned'         => 'string',
                'industrial_supervisor_name'  => 'string',
                'industrial_supervisor_email' => 'email',
                'attachment_start_date'       => 'date',
                'attachment_end_date'         => 'date',
                'weeks_completed'             => 'integer',
                'overall_rating'              => 'decimal:1',
                'overall_remarks'             => 'string',
                'dean_name'                   => 'string',
                'school_name'                 => 'string',
                'dean_phone'                  => 'string',
                'dean_email'                  => 'email',
                'rating_technical'            => 'integer',
                'remarks_technical'           => 'string',
                'rating_practical'            => 'integer',
                'remarks_practical'           => 'string',
                'rating_problem_solving'      => 'integer',
                'remarks_problem_solving'     => 'string',
                'rating_communication'        => 'integer',
                'remarks_communication'       => 'string',
                'rating_teamwork'             => 'integer',
                'remarks_teamwork'            => 'string',
                'rating_initiative'           => 'integer',
                'remarks_initiative'          => 'string',
                'rating_attendance'           => 'integer',
                'remarks_attendance'          => 'string',
                'rating_conduct'              => 'integer',
                'remarks_conduct'             => 'string',
            ],
            'descriptions' => [
                'university_name'             => 'Full name of the university',
                'university_logo_url'         => 'URL to university logo image',
                'department_name'             => 'Name of the academic department',
                'department_address'          => 'Physical address of the department',
                'department_phone'            => 'Department contact phone number',
                'department_email'            => 'Department contact email',
                'reference_number'            => 'Letter reference number',
                'letter_date'                 => 'Date the letter is issued',
                'company_name'                => 'Name of the host company',
                'student_name'                => 'Full name of the student',
                'reg_no'                      => 'Student registration number',
                'program_name'                => 'Academic program name',
                'department_assigned'         => 'Company department/section assigned',
                'industrial_supervisor_name'  => 'Industrial supervisor full name',
                'industrial_supervisor_email' => 'Industrial supervisor email',
                'attachment_start_date'       => 'Start date of attachment',
                'attachment_end_date'         => 'End date of attachment',
                'weeks_completed'             => 'Number of weeks completed',
                'overall_rating'              => 'Overall performance rating (1-5)',
                'overall_remarks'             => 'Overall supervisor comments',
                'dean_name'                   => 'Dean of School name',
                'school_name'                 => 'School name',
                'dean_phone'                  => 'Dean contact phone',
                'dean_email'                  => 'Dean contact email',
                'rating_technical'            => 'Technical knowledge rating (1-5)',
                'remarks_technical'           => 'Technical knowledge remarks',
                'rating_practical'            => 'Practical skills rating (1-5)',
                'remarks_practical'           => 'Practical skills remarks',
                'rating_problem_solving'      => 'Problem solving rating (1-5)',
                'remarks_problem_solving'     => 'Problem solving remarks',
                'rating_communication'        => 'Communication rating (1-5)',
                'remarks_communication'       => 'Communication remarks',
                'rating_teamwork'             => 'Teamwork rating (1-5)',
                'remarks_teamwork'            => 'Teamwork remarks',
                'rating_initiative'           => 'Initiative rating (1-5)',
                'remarks_initiative'          => 'Initiative remarks',
                'rating_attendance'           => 'Attendance rating (1-5)',
                'remarks_attendance'          => 'Attendance remarks',
                'rating_conduct'              => 'Professional conduct rating (1-5)',
                'remarks_conduct'             => 'Professional conduct remarks',
            ],
        ];
    }
}

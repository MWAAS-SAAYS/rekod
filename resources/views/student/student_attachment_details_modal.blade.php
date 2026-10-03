<div id="student_attachment_details-modal" 
     tabindex="-1" 
     aria-hidden="true" 
     data-modal-backdrop="static" 
     class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full bg-slate-900/60 backdrop-blur-xs">
    
    <div class="relative p-4 w-full max-w-4xl max-h-full">
        
        <!-- Modal content card -->
        <div class="relative bg-white rounded-2xl shadow-2xl border border-slate-200/80 overflow-hidden">
            
            <!-- Modal header banner -->
            <div class="bg-[#0b1329] px-6 py-4 border-b border-amber-500/20 flex items-center justify-between relative overflow-hidden">
                <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-amber-500/5 rounded-full blur-2xl pointer-events-none"></div>
                
                <div class="relative z-10 flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center text-xs font-bold border border-amber-500/20">
                        <i class="fas fa-id-card"></i>
                    </div>
                    <h3 class="text-sm font-bold text-white tracking-wide">
                        Student Attachment Details
                    </h3>
                </div>

                <button type="button" class="relative z-10 text-slate-400 hover:text-white hover:bg-slate-800/80 rounded-xl text-xs w-8 h-8 inline-flex justify-center items-center transition-all close-student_attachment_details_modal-btn">
                    <i class="fas fa-xmark text-sm"></i>
                    <span class="sr-only">Close modal</span>
                </button>
            </div>

            <!-- Modal Body (Details Table Container) -->
            <div class="p-6 overflow-x-auto">
                <table class="w-full text-left border-collapse rounded-xl overflow-hidden border border-slate-200/80" id="student_attachment_detailsTable">
                    <tbody class="divide-y divide-slate-100 text-xs">
                        <!-- jQuery populates data here -->
                    </tbody>
                </table>
            </div>

            <!-- Modal Footer -->
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end">
                <button type="button" class="inline-flex items-center px-4 py-2 rounded-xl bg-slate-900 text-amber-400 hover:bg-slate-800 text-xs font-bold transition-all shadow-xs close-student_attachment_details_modal-btn">
                    <i class="fas fa-xmark mr-2 text-[10px]"></i> Close Details
                </button>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        const student_attachment_details_modal = new Modal($('#student_attachment_details-modal')[0], {
            backdrop: 'static',
            closable: false
        });

        $(document).on('click', '.close-student_attachment_details_modal-btn', function () {
            student_attachment_details_modal.hide();
        });

        $(document).on('click', '.open-student_attachment_details_modal-btn', function () {
            const student_attachment_details_id = $(this).data('id');
            openAttachmentModal(student_attachment_details_id);
        });

        function openAttachmentModal(id) {
            student_attachment_details_modal.show();
            
            // Clear & show styled loading state
            $("#student_attachment_detailsTable tbody").html(`
                <tr>
                    <td colspan="4" class="p-8 text-center bg-slate-50/50">
                        <div class="flex flex-col items-center justify-center space-y-2">
                            <i class="fas fa-circle-notch animate-spin text-2xl text-amber-500"></i>
                            <span class="text-xs font-bold text-slate-600">Retrieving Attachment Record...</span>
                        </div>
                    </td>
                </tr>
            `);

            $.ajax({
                url: "/attachment-details/" + id,
                method: 'GET',
                success: function (data) {
                    console.log('Data received:', data);
                    fillAttachmentTable(data);
                },
                error: function (xhr, status, error) {
                    console.error('AJAX Error:', error);
                    
                    $("#student_attachment_detailsTable tbody").html(`
                        <tr>
                            <td colspan="4" class="p-6 text-center bg-rose-50/50 text-rose-600">
                                <div class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-rose-100 text-rose-600 mb-2">
                                    <i class="fas fa-triangle-exclamation"></i>
                                </div>
                                <p class="text-xs font-bold">Failed to load attachment details (${xhr.status})</p>
                                <p class="text-[11px] text-rose-500 mt-1">${xhr.responseText || 'An error occurred while fetching details.'}</p>
                            </td>
                        </tr>
                    `);
                }
            });
        }

        function fillAttachmentTable(data) {
            console.log('Data received:', data);
            
            let html = '';

            // Helper function for sleek section header banners
            const sectionHeader = (title, icon) => `
                <tr class="bg-[#0b1329] text-amber-400 font-bold uppercase tracking-wider text-[10px]">
                    <td colspan="4" class="px-4 py-2 border-y border-amber-500/20">
                        <span class="inline-flex items-center gap-1.5"><i class="${icon}"></i> ${title}</span>
                    </td>
                </tr>
            `;

            const thClass = "p-3 bg-slate-50/80 text-slate-500 text-[10px] font-bold uppercase tracking-wider border-b border-slate-100 w-1/4 align-middle";
            const tdClass = "p-3 text-slate-800 font-semibold text-xs border-b border-slate-100 w-1/4 align-middle";

            // SECTION 1: STUDENT INFO
            html += sectionHeader('Student Information', 'fas fa-user-graduate');
            html += `<tr>
                <th class="${thClass}">Student Name</th>
                <td class="${tdClass}">${data?.student?.user?.name || 'N/A'}</td>
                <th class="${thClass}">Phone Number</th>
                <td class="${tdClass}">${data?.student?.user?.phone_number || 'N/A'}</td>
            </tr>`;
            
            html += `<tr>
                <th class="${thClass}">Registration No</th>
                <td class="${tdClass}">${data?.student?.reg_no || 'N/A'}</td>
                <th class="${thClass}">Email Address</th>
                <td class="${tdClass}">${data?.student?.user?.email || 'N/A'}</td>
            </tr>`;
            
            html += `<tr>
                <th class="${thClass}">Academic Program</th>
                <td class="${tdClass}" colspan="3">${data?.student?.program?.name || 'N/A'}</td>
            </tr>`;

            // SECTION 2: COMPANY INFO
            const townName = data?.company?.town?.name || data?.town?.name || 'Not Assigned';
            html += sectionHeader('Host Organization & Location', 'fas fa-building');
            html += `<tr>
                <th class="${thClass}">Organization</th>
                <td class="${tdClass}">${data?.company?.name || 'N/A'}</td>
                <th class="${thClass}">Street</th>
                <td class="${tdClass}">${data?.company?.street || 'N/A'}</td>
            </tr>`;
            
            html += `<tr>
                <th class="${thClass}">Town / Sub-County</th>
                <td class="${tdClass}">${townName}</td>
                <th class="${thClass}">Building / Office</th>
                <td class="${tdClass}">${data?.company?.building || 'N/A'}</td>
            </tr>`;

            // SECTION 3: ATTACHMENT DETAILS
            html += sectionHeader('Placement Details', 'fas fa-calendar-check');
            html += `<tr>
                <th class="${thClass}">Attachment Name</th>
                <td class="${tdClass}" colspan="3">${data?.attachment?.name || 'N/A'}</td>
            </tr>`;
            
            html += `<tr>
                <th class="${thClass}">Start Date</th>
                <td class="${tdClass}">${data?.start_date || 'N/A'}</td>
                <th class="${thClass}">End Date</th>
                <td class="${tdClass}">${data?.end_date || 'N/A'}</td>
            </tr>`;

            // SECTION 4: SUPERVISORS & LECTURER INFO
            const supervisor = data?.industrial_supervisor || data?.industrialSupervisor || {};
            const supervisorUser = supervisor?.user || {};
            
            const attachmentLecturer = data?.attachment_lecturer || data?.attachmentLecturer || {};
            const lecturer = attachmentLecturer?.lecturer || {};
            const lecturerUser = lecturer?.user || {};

            const lecturerPhone = lecturerUser?.phone_number || lecturer?.office_phone || 'N/A';
            const lecturerName = lecturerUser?.name || 'Not Assigned';
            const lecturerEmail = lecturerUser?.email || 'N/A';

            html += sectionHeader('Supervision & Assessment Team', 'fas fa-user-tie');
            html += `<tr>
                <th class="${thClass}">Industry Supervisor</th>
                <td class="${tdClass}">${supervisorUser?.name || 'N/A'}</td>
                <th class="${thClass}">Supervisor Email</th>
                <td class="${tdClass}">${supervisorUser?.email || 'N/A'}</td>
            </tr>`;
            
            html += `<tr>
                <th class="${thClass}">Supervisor Phone</th>
                <td class="${tdClass}" colspan="3">${supervisorUser?.phone_number || 'N/A'}</td>
            </tr>`;

            html += `<tr>
                <th class="${thClass}">Assigned Lecturer</th>
                <td class="${tdClass}">${lecturerName}</td>
                <th class="${thClass}">Lecturer Email</th>
                <td class="${tdClass}">${lecturerEmail}</td>
            </tr>`;
            
            html += `<tr>
                <th class="${thClass}">Lecturer Phone</th>
                <td class="${tdClass}" colspan="3">${lecturerPhone}</td>
            </tr>`;

            $("#student_attachment_detailsTable tbody").html(html);
        }
    });
</script>
@endpush
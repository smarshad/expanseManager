@extends('layoutsnew.app')
@push('styles')
<link href="{{asset('backend/libs/select2/select2.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('backend/libs/bootstrap-select/bootstrap-select.min.css')}}" rel="stylesheet" type="text/css" />
@endpush
@push('scripts')
<script src="{{asset('backend/libs/select2/select2.min.js')}}"></script>
@endpush
@section('content')
<div class="content">

    <!-- Start Content-->
    <div class="container-fluid">

        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Adminox</a></li>
                            <li class="breadcrumb-item"><a href="javascript: void(0);">{{__('expense.title')}}</a></li>
                            <li class="breadcrumb-item active">New</li>
                        </ol>
                    </div>
                    <h4 class="page-title">{{__('expense.title')}}</h4>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card-box">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="header-title mb-3">{{ __('expense.create_title')}}</h4>
                        <a href="{{ route('expense.index') }}" class="btn btn-primary">
                            {{__('global.back')}}
                        </a>
                    </div>
                    <x-alert />

                    <div class="container my-5">
                        <div class="card shadow-lg">
                            <div class="card-header bg-danger text-white text-center">
                                <h2 class="mb-0">SURVEY FORM</h2>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('expense.index') }}" method="POST">
                                    @csrf

                                    {{-- Section 1: Basic Information --}}
                                    <h4 class="text-primary mb-3">1. Personal and Contact Information</h4>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="form_no" class="form-label">Form No.</label>
                                            <input type="text" class="form-control" id="form_no" name="form_no" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="eligibility" class="form-label">Eligibility</label>
                                            <input type="text" class="form-control" id="eligibility" name="eligibility">
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="name" class="form-label">Name</label>
                                            <input type="text" class="form-control" id="name" name="name" placeholder="«Name»" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="contact_no_1" class="form-label">Contact No. 1</label>
                                            <input type="text" class="form-control" id="contact_no_1" name="contact_no_1" placeholder="«Contact1»" required>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="address" class="form-label">Address</label>
                                            <textarea class="form-control" id="address" name="address" rows="2" placeholder="«Address»" required></textarea>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="contact_no_2" class="form-label">Contact No. 2</label>
                                            <input type="text" class="form-control" id="contact_no_2" name="contact_no_2" placeholder="«Contact2»">
                                        </div>
                                    </div>
                                    <div class="mb-4">
                                        <label for="landmark" class="form-label">Landmark</label>
                                        <input type="text" class="form-control" id="landmark" name="landmark" placeholder="«Area»">
                                    </div>

                                    {{-- Section 2: Dynamic Members Table (CRUD Feature) --}}
                                    <h4 class="text-primary mb-3">2. Family Members Details</h4>
                                    <div class="table-responsive mb-4">
                                        <table class="table table-bordered" id="members_table">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Sr#</th>
                                                    <th>Members</th>
                                                    <th>Relation</th>
                                                    <th>Age</th>
                                                    <th>Qualification</th>
                                                    <th>Profession</th>
                                                    <th>Income</th>
                                                    <th>Marital Status</th>
                                                    <th>Remarks/Religious activities</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {{-- Dynamic rows will be added here --}}
                                            </tbody>
                                        </table>
                                        <button type="button" class="btn btn-success btn-sm" id="add_member_row">Add Member</button>
                                    </div>

                                    {{-- Section 3: Eligibility and Asset Checkboxes --}}
                                    <h4 class="text-primary mb-3">3. Eligibility and Assets</h4>
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Eligibility Criteria:</label>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="eligibility_criteria[]" value="Light Bill: Above 1200" id="light_bill_above">
                                                <label class="form-check-label" for="light_bill_above">Light Bill: Above 1200</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="eligibility_criteria[]" value="Costly Smart Phone: Above 10000" id="smart_phone_above">
                                                <label class="form-check-label" for="smart_phone_above">Costly Smart Phone: Above 10000</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="eligibility_criteria[]" value="Important Furniture and AC" id="furniture_ac">
                                                <label class="form-check-label" for="furniture_ac">Important Furniture and AC</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="eligibility_criteria[]" value="Fish Tank or Any Pet like Cat-Dog" id="pet_fish_tank">
                                                <label class="form-check-label" for="pet_fish_tank">Fish Tank or Any Pet like Cat-Dog</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="eligibility_criteria[]" value="Receiving Aid from any other Source/Trust" id="receiving_aid">
                                                <label class="form-check-label" for="receiving_aid">Receiving Aid from any other Source/Trust.</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="eligibility_criteria[]" value="Two/Four-Wheeler for Personal Use" id="personal_vehicle">
                                                <label class="form-check-label" for="personal_vehicle">Two/Four-Wheeler for Personal Use.</label>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="row mb-4">
                                                <div class="col-md-12">
                                                    <label class="form-label fw-bold">Housing Status:</label>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="housing_status" id="housing_rent" value="Rent">
                                                        <label class="form-check-label" for="housing_rent">Rent</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="housing_status" id="housing_own" value="Own">
                                                        <label class="form-check-label" for="housing_own">Own</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="housing_status" id="housing_family" value="Family">
                                                        <label class="form-check-label" for="housing_family">Family</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mb-4">
                                                <div class="col-md-12">
                                                    <label class="form-label fw-bold mt-3">Vehicle Type:</label>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="vehicle_type" id="vehicle_2_wheeler" value="2-wheeler">
                                                        <label class="form-check-label" for="vehicle_2_wheeler">2-wheeler</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="vehicle_type" id="vehicle_auto" value="Auto">
                                                        <label class="form-check-label" for="vehicle_auto">Auto</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="vehicle_type" id="vehicle_4_wheeler" value="4-wheeler">
                                                        <label class="form-check-label" for="vehicle_4_wheeler">4-wheeler</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label fw-bold">Assets Owned:</label>
                                        <div class="row">
                                            @php
                                            $assets = ['Furniture', 'TV', 'DTH', 'Fridge', 'Washing Machine', 'Computer', 'Gold', 'iPhone', 'Fixed Deposit'];
                                            @endphp
                                            @foreach ($assets as $asset)
                                            <div class="col-md-3 col-sm-6">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="assets_owned[]" value="{{ $asset }}" id="asset_{{ Str::slug($asset) }}">
                                                    <label class="form-check-label" for="asset_{{ Str::slug($asset) }}">{{ $asset }}</label>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    {{-- Section 4: Other Details --}}
                                    <h4 class="text-primary mb-3">4. Other Details</h4>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="support_from_trust" class="form-label">Support from any other Trust?</label>
                                            <select class="form-select" id="support_from_trust" name="support_from_trust">
                                                <option value="No">No</option>
                                                <option value="Yes">Yes</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="name_of_trust" class="form-label">If Yes Name of Trust</label>
                                            <input type="text" class="form-control" id="name_of_trust" name="name_of_trust" disabled>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="serious_disease" class="form-label">Serious Disease if Any</label>
                                            <input type="text" class="form-control" id="serious_disease" name="serious_disease">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="job_requirement" class="form-label">Job Requirement?</label>
                                            <select class="form-select" id="job_requirement" name="job_requirement">
                                                <option value="No">No</option>
                                                <option value="Yes">Yes</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label for="aadhar_numbers" class="form-label">All Family Members Aadhar Number (Comma Separated)</label>
                                        <textarea class="form-control" id="aadhar_numbers" name="aadhar_numbers" rows="2"></textarea>
                                    </div>

                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-danger btn-lg">Submit Survey</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- JavaScript for Dynamic Row Addition/Deletion --}}
                    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
                    <script>
                        /* $(document).ready(function() {
                            let member_row_count = 0;

                            // Function to generate a new member row
                            function generateMemberRow() {
                                member_row_count++;
                                const newRow = `
                <tr id="member_row_${member_row_count}">
                    <td>${member_row_count}</td>
                    <td><input type="text" name="members[${member_row_count}][name]" class="form-control form-control-sm" required></td>
                    <td><input type="text" name="members[${member_row_count}][relation]" class="form-control form-control-sm"></td>
                    <td><input type="number" name="members[${member_row_count}][age]" class="form-control form-control-sm"></td>
                    <td><input type="text" name="members[${member_row_count}][qualification]" class="form-control form-control-sm"></td>
                    <td><input type="text" name="members[${member_row_count}][profession]" class="form-control form-control-sm"></td>
                    <td><input type="number" name="members[${member_row_count}][income]" class="form-control form-control-sm"></td>
                    <td><input type="text" name="members[${member_row_count}][marital_status]" class="form-control form-control-sm"></td>
                    <td><input type="text" name="members[${member_row_count}][remarks]" class="form-control form-control-sm"></td>
                    <td><button type="button" class="btn btn-danger btn-sm remove-member-row" data-row-id="${member_row_count}">Remove</button></td>
                </tr>
            `;
                                $('#members_table tbody').append(newRow);
                            }

                            // Add initial row
                            generateMemberRow();

                            // Event listener for adding a new row
                            $('#add_member_row').on('click', function() {
                                generateMemberRow();
                            });

                            // Event listener for removing a row (using event delegation)
                            $('#members_table').on('click', '.remove-member-row', function() {
                                const rowId = $(this).data('row-id');
                                $(`#member_row_${rowId}`).remove();
                                // Re-index the rows for display (optional, but good for user experience)
                                $('#members_table tbody tr').each(function(index) {
                                    $(this).find('td:first').text(index + 1);
                                });
                                // Note: The actual input names (members[X][Y]) are not re-indexed here,
                                // which is fine for Laravel as it will receive an associative array.
                            });

                            // Logic for "If Yes Name of Trust" field
                            $('#support_from_trust').on('change', function() {
                                if ($(this).val() === 'Yes') {
                                    $('#name_of_trust').prop('disabled', false).prop('required', true);
                                } else {
                                    $('#name_of_trust').prop('disabled', true).prop('required', false).val('');
                                }
                            }).trigger('change'); // Trigger on load to set initial state
                        }); */

                        /**
                         * Dynamic CRUD Form - JavaScript/jQuery Code
                         * File: resources/views/survey/create.blade.php
                         * 
                         * This script handles the dynamic addition and removal of family member rows
                         * in the survey form. It uses jQuery for DOM manipulation and event handling.
                         * 
                         * Features:
                         * - Add new member rows dynamically
                         * - Remove member rows with a single click
                         * - Maintain proper array naming for Laravel form submission
                         * - Re-index row numbers for visual consistency
                         * - Conditional field enabling based on user selection
                         */

                        // ============================================================================
                        // 1. DOCUMENT READY - Initialize the form on page load
                        // ============================================================================
                        $(document).ready(function() {
                            // Initialize counter for unique row IDs
                            let member_row_count = 0;

                            // ========================================================================
                            // 2. FUNCTION: Generate a new member row with input fields
                            // ========================================================================
                            /**
                             * Generates a new table row with input fields for a family member.
                             * Uses Laravel's array notation (members[X][Y]) for form submission.
                             * 
                             * @returns {void} - Appends the new row to the members table
                             */
                            function generateMemberRow() {
                                // Increment the counter to ensure unique row IDs
                                member_row_count++;

                                // Create the HTML for a new row with all required input fields
                                const newRow = `
            <tr id="member_row_${member_row_count}">
                <!-- Serial Number Column -->
                <td>${member_row_count}</td>
                
                <!-- Member Name Input -->
                <td>
                    <input 
                        type="text" 
                        name="members[${member_row_count}][name]" 
                        class="form-control form-control-sm" 
                        required
                    >
                </td>
                
                <!-- Relation Input (e.g., Son, Daughter, Father, Mother) -->
                <td>
                    <input 
                        type="text" 
                        name="members[${member_row_count}][relation]" 
                        class="form-control form-control-sm"
                    >
                </td>
                
                <!-- Age Input (numeric) -->
                <td>
                    <input 
                        type="number" 
                        name="members[${member_row_count}][age]" 
                        class="form-control form-control-sm"
                    >
                </td>
                
                <!-- Qualification Input (e.g., 10th, 12th, Bachelor's) -->
                <td>
                    <input 
                        type="text" 
                        name="members[${member_row_count}][qualification]" 
                        class="form-control form-control-sm"
                    >
                </td>
                
                <!-- Profession Input (e.g., Engineer, Doctor, Teacher) -->
                <td>
                    <input 
                        type="text" 
                        name="members[${member_row_count}][profession]" 
                        class="form-control form-control-sm"
                    >
                </td>
                
                <!-- Income Input (numeric, in rupees) -->
                <td>
                    <input 
                        type="number" 
                        name="members[${member_row_count}][income]" 
                        class="form-control form-control-sm"
                    >
                </td>
                
                <!-- Marital Status Input (e.g., Single, Married, Divorced) -->
                <td>
                    <input 
                        type="text" 
                        name="members[${member_row_count}][marital_status]" 
                        class="form-control form-control-sm"
                    >
                </td>
                
                <!-- Remarks/Religious Activities Input -->
                <td>
                    <input 
                        type="text" 
                        name="members[${member_row_count}][remarks]" 
                        class="form-control form-control-sm"
                    >
                </td>
                
                <!-- Remove Button Column -->
                <td>
                    <button 
                        type="button" 
                        class="btn btn-danger btn-sm remove-member-row" 
                        data-row-id="${member_row_count}"
                    >
                        Remove
                    </button>
                </td>
            </tr>
        `;

                                // Append the new row to the table body
                                $('#members_table tbody').append(newRow);
                            }

                            // ========================================================================
                            // 3. INITIALIZATION: Add the first member row on page load
                            // ========================================================================
                            /**
                             * When the page loads, automatically add one empty row so users
                             * can immediately start entering data without clicking "Add Member" first.
                             */
                            generateMemberRow();

                            // ========================================================================
                            // 4. EVENT LISTENER: "Add Member" button click
                            // ========================================================================
                            /**
                             * When the user clicks the "Add Member" button, generate a new row.
                             * This allows users to dynamically add as many family members as needed.
                             */
                            $('#add_member_row').on('click', function() {
                                generateMemberRow();
                            });

                            // ========================================================================
                            // 5. EVENT LISTENER: "Remove" button click (Event Delegation)
                            // ========================================================================
                            /**
                             * When the user clicks a "Remove" button, delete the corresponding row.
                             * Uses event delegation so it works for dynamically added rows.
                             * 
                             * After deletion, re-indexes the serial numbers (Sr#) for visual consistency.
                             */
                            $('#members_table').on('click', '.remove-member-row', function() {
                                // Get the row ID from the button's data attribute
                                const rowId = $(this).data('row-id');

                                // Remove the row from the DOM
                                $(`#member_row_${rowId}`).remove();

                                // Re-index the serial numbers (Sr#) in the first column of each row
                                // This ensures the numbering remains sequential after deletions
                                $('#members_table tbody tr').each(function(index) {
                                    // Update the serial number in the first column (index + 1 because of 1-based numbering)
                                    $(this).find('td:first').text(index + 1);
                                });

                                /**
                                 * IMPORTANT NOTE:
                                 * The actual input field names (members[X][Y]) are NOT re-indexed here.
                                 * This is intentional because Laravel can handle sparse arrays.
                                 * 
                                 * For example, if you have members[1][name], members[2][name], and members[4][name],
                                 * Laravel's form helper will still correctly process all three records.
                                 * 
                                 * If you prefer to have sequential indices (members[0], members[1], members[2]),
                                 * you would need to add additional logic to rebuild the form data before submission.
                                 */
                            });

                            // ========================================================================
                            // 6. EVENT LISTENER: Conditional field enabling
                            // ========================================================================
                            /**
                             * When the user selects "Yes" for "Support from any other Trust?",
                             * enable the "Name of Trust" field. Otherwise, disable it and clear its value.
                             * 
                             * This demonstrates form validation and conditional logic.
                             */
                            $('#support_from_trust').on('change', function() {
                                if ($(this).val() === 'Yes') {
                                    // Enable the field and mark it as required
                                    $('#name_of_trust').prop('disabled', false).prop('required', true);
                                } else {
                                    // Disable the field, clear its value, and remove the required attribute
                                    $('#name_of_trust').prop('disabled', true).prop('required', false).val('');
                                }
                            }).trigger('change'); // Trigger on load to set the initial state

                            // ========================================================================
                            // 7. OPTIONAL: Form Validation Before Submission
                            // ========================================================================
                            /**
                             * You can add additional validation logic here if needed.
                             * For example, checking that at least one member row has data,
                             * or validating that all required fields are filled.
                             * 
                             * Example:
                             * 
                             * $('form').on('submit', function(e) {
                             *     let hasValidMember = false;
                             *     
                             *     $('#members_table tbody tr').each(function() {
                             *         const memberName = $(this).find('input[name*="[name]"]').val();
                             *         if (memberName && memberName.trim() !== '') {
                             *             hasValidMember = true;
                             *         }
                             *     });
                             *     
                             *     if (!hasValidMember) {
                             *         e.preventDefault();
                             *         alert('Please add at least one family member.');
                             *         return false;
                             *     }
                             * });
                             */

                        });

                        // ============================================================================
                        // 8. FORM SUBMISSION AND SERVER-SIDE PROCESSING
                        // ============================================================================
                        /**
                         * When the form is submitted, the data is sent to the server in this format:
                         * 
                         * POST /survey
                         * 
                         * Form Data:
                         * {
                         *     form_no: "123456",
                         *     name: "John Doe",
                         *     address: "123 Main Street",
                         *     contact_no_1: "9876543210",
                         *     members: {
                         *         1: { name: "Jane Doe", relation: "Wife", age: 35, ... },
                         *         2: { name: "Tom Doe", relation: "Son", age: 10, ... },
                         *         3: { name: "Mary Doe", relation: "Daughter", age: 8, ... }
                         *     },
                         *     eligibility_criteria: ["Light Bill: Above 1200", "Costly Smart Phone: Above 10000"],
                         *     assets_owned: ["TV", "Fridge", "Computer"],
                         *     ...
                         * }
                         * 
                         * The Laravel controller receives this data and processes it as follows:
                         * 
                         * In SurveyController@store():
                         * 
                         * 1. Validate the incoming data using Laravel's validation rules
                         * 2. Create a new Survey record with the main form data
                         * 3. Iterate over the members array and create related SurveyMember records
                         * 4. Return a success response or redirect to the survey list
                         * 
                         * Example Controller Logic:
                         * 
                         * $validatedData = $request->validate([...]);
                         * $survey = Survey::create($validatedData);
                         * 
                         * foreach ($validatedData['members'] as $memberData) {
                         *     $survey->members()->create($memberData);
                         * }
                         * 
                         * return redirect()->route('survey.index')->with('success', 'Survey submitted!');
                         */

                        // ============================================================================
                        // 9. KEY CONCEPTS AND BEST PRACTICES
                        // ============================================================================
                        /**
                         * 1. ARRAY NOTATION IN BLADE FORMS:
                         *    - members[1][name] creates a nested array structure
                         *    - Laravel automatically converts this to: $request->input('members.1.name')
                         *    - Or access it as: $request->input('members')[1]['name']
                         * 
                         * 2. EVENT DELEGATION:
                         *    - Using $('#members_table').on('click', '.remove-member-row', ...)
                         *    - Ensures that dynamically added rows also have the remove functionality
                         *    - Without delegation, only pre-existing rows would have the handler
                         * 
                         * 3. JQUERY SELECTORS:
                         *    - $(this) refers to the element that triggered the event
                         *    - $(this).data('row-id') retrieves the data attribute value
                         *    - $(`#member_row_${rowId}`) uses template literals for dynamic IDs
                         * 
                         * 4. FORM CONTROL CLASSES:
                         *    - form-control-sm: Bootstrap class for smaller input fields
                         *    - form-control: Standard Bootstrap input styling
                         *    - These can be replaced with custom CSS if needed
                         * 
                         * 5. ACCESSIBILITY:
                         *    - Always include labels for form fields (in the Blade template)
                         *    - Use semantic HTML (table for tabular data, form for forms)
                         *    - Ensure keyboard navigation works (tab through fields)
                         *    - Test with screen readers for accessibility compliance
                         */
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script src="{{ asset('backend/js/ajax-form-submit.js') }}"></script>
<script src="{{ asset('backend/js/expense.js') }}"></script>
@endpush
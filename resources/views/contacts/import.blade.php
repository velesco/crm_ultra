@extends('layouts.app')

@section('title', 'Import Contacts')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-upload me-2"></i>Import Contacts
                    </h5>
                    <a href="{{ route('contacts.index') }}" class="btn btn-light btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> Back to Contacts
                    </a>
                </div>
                <div class="card-body">
                    <div id="importWizard">
                        <!-- Step 1: File Upload -->
                        <div class="step active" id="step1">
                            <h6 class="mb-3">Step 1: Upload Your File</h6>
                            <form id="uploadForm" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="mb-3">
                                            <label for="file" class="form-label">Choose CSV or Excel File</label>
                                            <input type="file" class="form-control" id="file" name="file" 
                                                   accept=".csv,.xlsx,.xls" required>
                                            <div class="form-text">
                                                Supported formats: CSV, Excel (.xlsx, .xls). Maximum file size: 10MB
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="segment_id" class="form-label">Add to Segment (Optional)</label>
                                            <select class="form-select" id="segment_id" name="segment_id">
                                                <option value="">None</option>
                                                @foreach($segments as $segment)
                                                    <option value="{{ $segment->id }}">{{ $segment->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="skip_duplicates" 
                                                   name="skip_duplicates" checked>
                                            <label class="form-check-label" for="skip_duplicates">
                                                Skip Duplicates (based on email)
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="update_existing" 
                                                   name="update_existing">
                                            <label class="form-check-label" for="update_existing">
                                                Update Existing Contacts
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-3">
                                    <button type="button" class="btn btn-primary" onclick="analyzeFile()">
                                        <i class="fas fa-analyze me-1"></i> Analyze File
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Step 2: Column Mapping -->
                        <div class="step" id="step2" style="display: none;">
                            <h6 class="mb-3">Step 2: Map Columns</h6>
                            <p class="text-muted">Map the columns from your file to contact fields:</p>
                            
                            <form id="mappingForm" method="POST" action="{{ route('contacts.import.process') }}">
                                @csrf
                                <input type="hidden" id="fileInput" name="file_data">
                                <input type="hidden" id="segmentInput" name="segment_id">
                                <input type="hidden" id="skipDuplicatesInput" name="skip_duplicates">
                                <input type="hidden" id="updateExistingInput" name="update_existing">
                                
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>File Column</th>
                                                <th>Sample Data</th>
                                                <th>Map to Contact Field</th>
                                            </tr>
                                        </thead>
                                        <tbody id="columnMapping">
                                            <!-- Dynamic content -->
                                        </tbody>
                                    </table>
                                </div>

                                <div class="mt-4">
                                    <button type="button" class="btn btn-light me-2" onclick="previousStep()">
                                        <i class="fas fa-arrow-left me-1"></i> Previous
                                    </button>
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-upload me-1"></i> Start Import
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sample Format Modal -->
    <div class="modal fade" id="sampleModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Sample CSV Format</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Your CSV file should have headers in the first row. Here's an example:</p>
                    <pre>first_name,last_name,email,phone,company,position
John,Doe,john@example.com,+1234567890,Acme Corp,Manager
Jane,Smith,jane@example.com,+0987654321,Tech Inc,Developer</pre>
                    
                    <h6 class="mt-3">Supported Fields:</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="list-unstyled small">
                                <li><strong>first_name</strong> - First Name</li>
                                <li><strong>last_name</strong> - Last Name</li>
                                <li><strong>email</strong> - Email Address</li>
                                <li><strong>phone</strong> - Phone Number</li>
                                <li><strong>company</strong> - Company Name</li>
                                <li><strong>position</strong> - Job Position</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="list-unstyled small">
                                <li><strong>address</strong> - Street Address</li>
                                <li><strong>city</strong> - City</li>
                                <li><strong>country</strong> - Country</li>
                                <li><strong>source</strong> - Lead Source</li>
                                <li><strong>tags</strong> - Tags (comma separated)</li>
                                <li><strong>notes</strong> - Notes</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let currentStep = 1;
let fileData = null;

function analyzeFile() {
    const form = document.getElementById('uploadForm');
    const formData = new FormData(form);
    
    if (!formData.get('file')) {
        alert('Please select a file first.');
        return;
    }
    
    // Here you would send the file to be analyzed
    // For now, we'll simulate the analysis
    setTimeout(() => {
        // Simulate file analysis response
        const mockData = {
            headers: ['first_name', 'last_name', 'email', 'phone', 'company'],
            sample: ['John', 'Doe', 'john@example.com', '+1234567890', 'Acme Corp'],
            total_rows: 150
        };
        
        showMappingStep(mockData);
    }, 1000);
}

function showMappingStep(data) {
    document.getElementById('step1').style.display = 'none';
    document.getElementById('step2').style.display = 'block';
    currentStep = 2;
    
    // Populate mapping table
    const tbody = document.getElementById('columnMapping');
    tbody.innerHTML = '';
    
    const contactFields = [
        { value: '', text: '-- Skip this column --' },
        { value: 'first_name', text: 'First Name' },
        { value: 'last_name', text: 'Last Name' },
        { value: 'email', text: 'Email Address' },
        { value: 'phone', text: 'Phone Number' },
        { value: 'company', text: 'Company Name' },
        { value: 'position', text: 'Position/Title' },
        { value: 'address', text: 'Address' },
        { value: 'city', text: 'City' },
        { value: 'country', text: 'Country' },
        { value: 'source', text: 'Source' },
        { value: 'tags', text: 'Tags' },
        { value: 'notes', text: 'Notes' }
    ];
    
    data.headers.forEach((header, index) => {
        const row = document.createElement('tr');
        
        // Guess the field mapping
        const guessedField = guessFieldMapping(header);
        
        row.innerHTML = `
            <td><strong>${header}</strong></td>
            <td class="text-muted">${data.sample[index] || 'N/A'}</td>
            <td>
                <select class="form-select" name="mapping[${header}]">
                    ${contactFields.map(field => 
                        `<option value="${field.value}" ${field.value === guessedField ? 'selected' : ''}>${field.text}</option>`
                    ).join('')}
                </select>
            </td>
        `;
        
        tbody.appendChild(row);
    });
    
    // Store form data
    const form1 = document.getElementById('uploadForm');
    const formData = new FormData(form1);
    
    document.getElementById('segmentInput').value = formData.get('segment_id') || '';
    document.getElementById('skipDuplicatesInput').value = formData.get('skip_duplicates') ? '1' : '0';
    document.getElementById('updateExistingInput').value = formData.get('update_existing') ? '1' : '0';
}

function guessFieldMapping(header) {
    const headerLower = header.toLowerCase().replace(/[^a-z]/g, '');
    
    const mappings = {
        'firstname': 'first_name',
        'fname': 'first_name',
        'givenname': 'first_name',
        'lastname': 'last_name',
        'lname': 'last_name',
        'surname': 'last_name',
        'familyname': 'last_name',
        'email': 'email',
        'emailaddress': 'email',
        'mail': 'email',
        'phone': 'phone',
        'phonenumber': 'phone',
        'mobile': 'phone',
        'tel': 'phone',
        'company': 'company',
        'companyname': 'company',
        'organization': 'company',
        'position': 'position',
        'title': 'position',
        'jobtitle': 'position',
        'address': 'address',
        'streetaddress': 'address',
        'city': 'city',
        'country': 'country',
        'source': 'source',
        'leadsource': 'source',
        'tags': 'tags',
        'notes': 'notes',
        'comments': 'notes'
    };
    
    return mappings[headerLower] || '';
}

function previousStep() {
    if (currentStep > 1) {
        document.getElementById(`step${currentStep}`).style.display = 'none';
        currentStep--;
        document.getElementById(`step${currentStep}`).style.display = 'block';
    }
}

// Handle form submission
document.getElementById('mappingForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Create a new form with file
    const originalForm = document.getElementById('uploadForm');
    const newForm = new FormData();
    
    // Add file
    const fileInput = originalForm.querySelector('#file');
    if (fileInput.files[0]) {
        newForm.append('file', fileInput.files[0]);
    }
    
    // Add other form data
    const formData = new FormData(this);
    for (let pair of formData.entries()) {
        newForm.append(pair[0], pair[1]);
    }
    
    // Submit
    fetch(this.action, {
        method: 'POST',
        body: newForm,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => {
        if (response.redirected) {
            window.location.href = response.url;
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            window.location.href = data.redirect;
        } else {
            alert(data.error || 'Import failed');
        }
    })
    .catch(error => {
        console.error('Import error:', error);
        alert('Import failed. Please try again.');
    });
});
</script>
@endpush

@endsection
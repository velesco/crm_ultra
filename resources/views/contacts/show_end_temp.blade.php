                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function duplicateContact() {
    if (confirm('Do you want to create a duplicate of this contact?')) {
        window.location.href = '{{ route("contacts.create") }}?duplicate={{ $contact->id }}';
    }
}

function exportContact() {
    // Create a temporary form to export this contact
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("data.export") }}';
    form.innerHTML = `
        @csrf
        <input type="hidden" name="type" value="contacts">
        <input type="hidden" name="contact_ids[]" value="{{ $contact->id }}">
        <input type="hidden" name="format" value="csv">
    `;
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

function unsubscribeContact() {
    if (confirm('Are you sure you want to unsubscribe this contact from all communications?')) {
        // Create a form to update contact status
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("contacts.update", $contact) }}';
        form.innerHTML = `
            @csrf
            @method('PUT')
            <input type="hidden" name="status" value="unsubscribed">
        `;
        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    }
}
</script>
@endpush
@endsection
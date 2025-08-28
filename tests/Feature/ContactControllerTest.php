<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ContactControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles and permissions
        $this->createRolesAndPermissions();
    }

    public function test_admin_can_view_contacts_index()
    {
        $admin = $this->createAdminUser();
        Contact::factory(5)->create();

        $response = $this->actingAs($admin)->get(route('contacts.index'));

        $response->assertStatus(200);
        $response->assertViewIs('contacts.index');
        $response->assertViewHas('contacts');
    }

    public function test_admin_can_create_contact()
    {
        $admin = $this->createAdminUser();

        $contactData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'phone' => '+1-555-0123',
            'company' => 'Test Company',
            'position' => 'Manager',
            'status' => 'active',
            'source' => 'website',
        ];

        $response = $this->actingAs($admin)->post(route('contacts.store'), $contactData);

        $response->assertRedirect(route('contacts.index'));
        $this->assertDatabaseHas('contacts', [
            'email' => 'john.doe@example.com',
            'created_by' => $admin->id,
        ]);
    }

    public function test_admin_can_update_contact()
    {
        $admin = $this->createAdminUser();
        $contact = Contact::factory()->create(['created_by' => $admin->id]);

        $updateData = [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => $contact->email,
            'phone' => $contact->phone,
            'company' => 'Updated Company',
            'position' => 'Senior Manager',
            'status' => 'active',
            'source' => 'referral',
        ];

        $response = $this->actingAs($admin)
            ->put(route('contacts.update', $contact), $updateData);

        $response->assertRedirect(route('contacts.show', $contact));
        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'first_name' => 'Jane',
            'company' => 'Updated Company',
        ]);
    }

    public function test_admin_can_delete_contact()
    {
        $admin = $this->createAdminUser();
        $contact = Contact::factory()->create(['created_by' => $admin->id]);

        $response = $this->actingAs($admin)
            ->delete(route('contacts.destroy', $contact));

        $response->assertRedirect(route('contacts.index'));
        $this->assertSoftDeleted('contacts', ['id' => $contact->id]);
    }

    public function test_agent_can_only_view_own_contacts()
    {
        $agent = $this->createAgentUser();
        $otherUser = $this->createAgentUser();

        // Create contacts - some owned by agent, some by other user
        $ownContact = Contact::factory()->create(['created_by' => $agent->id]);
        $otherContact = Contact::factory()->create(['created_by' => $otherUser->id]);

        $response = $this->actingAs($agent)->get(route('contacts.show', $ownContact));
        $response->assertStatus(200);

        $response = $this->actingAs($agent)->get(route('contacts.show', $otherContact));
        $response->assertStatus(403);
    }

    public function test_agent_cannot_delete_contacts_they_did_not_create()
    {
        $agent = $this->createAgentUser();
        $otherUser = $this->createAgentUser();

        $otherContact = Contact::factory()->create(['created_by' => $otherUser->id]);

        $response = $this->actingAs($agent)
            ->delete(route('contacts.destroy', $otherContact));

        $response->assertStatus(403);
        $this->assertDatabaseHas('contacts', ['id' => $otherContact->id]);
    }

    public function test_contact_validation_requires_email()
    {
        $admin = $this->createAdminUser();

        $contactData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            // email is missing
            'company' => 'Test Company',
        ];

        $response = $this->actingAs($admin)->post(route('contacts.store'), $contactData);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_contact_validation_requires_unique_email()
    {
        $admin = $this->createAdminUser();
        $existingContact = Contact::factory()->create();

        $contactData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => $existingContact->email, // duplicate email
            'company' => 'Test Company',
        ];

        $response = $this->actingAs($admin)->post(route('contacts.store'), $contactData);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_bulk_actions_delete_contacts()
    {
        $admin = $this->createAdminUser();
        $contacts = Contact::factory(3)->create(['created_by' => $admin->id]);

        $contactIds = $contacts->pluck('id')->toArray();

        $response = $this->actingAs($admin)->post(route('contacts.bulk-actions'), [
            'action' => 'delete',
            'contact_ids' => $contactIds,
        ]);

        $response->assertRedirect(route('contacts.index'));

        foreach ($contactIds as $id) {
            $this->assertSoftDeleted('contacts', ['id' => $id]);
        }
    }

    public function test_bulk_actions_update_status()
    {
        $admin = $this->createAdminUser();
        $contacts = Contact::factory(3)->create([
            'created_by' => $admin->id,
            'status' => 'prospect',
        ]);

        $contactIds = $contacts->pluck('id')->toArray();

        $response = $this->actingAs($admin)->post(route('contacts.bulk-actions'), [
            'action' => 'update_status',
            'contact_ids' => $contactIds,
            'status' => 'active',
        ]);

        $response->assertRedirect(route('contacts.index'));

        foreach ($contactIds as $id) {
            $this->assertDatabaseHas('contacts', [
                'id' => $id,
                'status' => 'active',
            ]);
        }
    }

    public function test_contact_export_returns_csv()
    {
        $admin = $this->createAdminUser();
        Contact::factory(5)->create(['created_by' => $admin->id]);

        $response = $this->actingAs($admin)->get(route('contacts.export'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_contact_search_filters_results()
    {
        $admin = $this->createAdminUser();

        Contact::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'company' => 'TechCorp',
        ]);

        Contact::factory()->create([
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane@example.com',
            'company' => 'MarketingPro',
        ]);

        $response = $this->actingAs($admin)->get(route('contacts.index', ['search' => 'John']));

        $response->assertStatus(200);
        $response->assertSee('john@example.com');
        $response->assertDontSee('jane@example.com');
    }

    public function test_unauthorized_user_cannot_access_contacts()
    {
        $contact = Contact::factory()->create();

        $response = $this->get(route('contacts.index'));
        $response->assertRedirect(route('login'));

        $response = $this->get(route('contacts.show', $contact));
        $response->assertRedirect(route('login'));
    }

    public function test_quick_email_can_be_sent_to_contact()
    {
        $admin = $this->createAdminUser();
        $contact = Contact::factory()->create(['created_by' => $admin->id]);

        $emailData = [
            'subject' => 'Test Subject',
            'message' => 'Test message content',
        ];

        $response = $this->actingAs($admin)
            ->post(route('contacts.quick-email', $contact), $emailData);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_note_can_be_added_to_contact()
    {
        $admin = $this->createAdminUser();
        $contact = Contact::factory()->create(['created_by' => $admin->id]);

        $noteData = [
            'note' => 'This is a test note about the contact.',
        ];

        $response = $this->actingAs($admin)
            ->post(route('contacts.add-note', $contact), $noteData);

        $response->assertRedirect();
        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
        ]);
    }

    // Helper methods
    protected function createRolesAndPermissions()
    {
        // Create permissions
        $permissions = [
            'view contacts', 'create contacts', 'edit contacts', 'delete contacts',
            'import contacts', 'export contacts', 'bulk-update contacts',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        $agentRole = Role::create(['name' => 'agent']);
        $agentRole->givePermissionTo([
            'view contacts', 'create contacts', 'edit contacts',
        ]);

        $viewerRole = Role::create(['name' => 'viewer']);
        $viewerRole->givePermissionTo(['view contacts']);
    }

    protected function createAdminUser()
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        return $user;
    }

    protected function createAgentUser()
    {
        $user = User::factory()->create();
        $user->assignRole('agent');

        return $user;
    }

    protected function createViewerUser()
    {
        $user = User::factory()->create();
        $user->assignRole('viewer');

        return $user;
    }
}

<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AttachmentValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_attachment_mime_and_size_validation_on_create()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // invalid mime
        $badFile = UploadedFile::fake()->create('bad.exe', 100, 'application/x-msdownload');
        $resp = $this->postJson('/api/repair-requests', [
            'title' => 'Test',
            'priority' => 'low',
            'files' => [$badFile],
        ]);
        $resp->assertStatus(422);
        $this->assertArrayHasKey('files.0', $resp->json('errors'));

        // too large
        $bigFile = UploadedFile::fake()->create('big.pdf', config('uploads.max_kb', 10240) + 1024, 'application/pdf');
        $resp2 = $this->postJson('/api/repair-requests', [
            'title' => 'Test',
            'priority' => 'low',
            'files' => [$bigFile],
        ]);
        $resp2->assertStatus(422);
        $this->assertArrayHasKey('files.0', $resp2->json('errors'));
    }
}

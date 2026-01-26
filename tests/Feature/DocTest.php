<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

class DocTest extends TestCase
{

    use RefreshDatabase;

    /** @test */
    public function usuario_autenticado_pode_adicionar_documento()
    {
        Storage::fake('public');

        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user, 'api')
            ->postJson('/api/docs', [
                'title' => 'Documento de Teste',
                'file' => UploadedFile::fake()->create(
                    'teste.pdf',
                    100,
                    'application/pdf'
                )
            ]);

        $response->assertStatus(200)
                ->assertJson([
                    'error' => ''
                ]);

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('public');

        $disk->assertExists('docs');
    }

    /** @test */
    public function nao_permite_documento_sem_arquivo()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user, 'api')
            ->postJson('/api/docs', [
                'title' => 'Sem arquivo'
            ]);

        $response->assertJson([
            'error' => 'Campos obrigatórios não enviados'
        ]);
    }

    /** @test */
    public function lista_documentos()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $this->actingAs($user, 'api')
            ->getJson('/api/docs')
            ->assertStatus(200)
            ->assertJsonStructure([
                'error',
                'list'
            ]);
    }
}

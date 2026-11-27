<?php

use App\Models\Acta;
use App\Models\User;
use Illuminate\Process\PendingProcess;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    DB::table('roles')->insert([
        ['id' => 1, 'nombre' => 'admin', 'descripcion' => 'Acceso total', 'creado_en' => now()],
    ]);
    Storage::fake('local');
    Storage::disk('local')->put('actas/acta-inicial.docx', 'contenido word');

    $this->admin = User::factory()->create(['rol_id' => 1]);
    $this->acta = Acta::factory()->create(['creado_por' => $this->admin->id]);
});

/**
 * Simula LibreOffice: escribe el PDF en la carpeta de salida que recibe.
 */
function libreOfficeQueConvierte(): void
{
    Process::fake(function (PendingProcess $proceso) {
        $comando = (array) $proceso->command;
        $carpeta = $comando[array_search('--outdir', $comando, true) + 1];
        File::put("{$carpeta}/documento.pdf", '%PDF-1.4 acta');

        return Process::result();
    });
}

test('an acta can be downloaded as a pdf converted from its word', function () {
    libreOfficeQueConvierte();

    $this->actingAs($this->admin)
        ->get(route('actas.descargar', ['acta' => $this->acta, 'formato' => 'pdf']))
        ->assertOk()
        ->assertDownload('acta-inicial.pdf')
        ->assertHeader('Content-Type', 'application/pdf');

    Storage::disk('local')->assertExists('actas/acta-inicial.pdf');
    Process::assertRan(fn (PendingProcess $proceso) => in_array('--convert-to', (array) $proceso->command, true));
});

test('the converted pdf is reused instead of converting again', function () {
    libreOfficeQueConvierte();

    $this->actingAs($this->admin)->get(route('actas.descargar', ['acta' => $this->acta, 'formato' => 'pdf']))->assertOk();
    $this->actingAs($this->admin)->get(route('actas.descargar', ['acta' => $this->acta, 'formato' => 'pdf']))->assertOk();

    Process::assertRanTimes(fn (PendingProcess $proceso) => true, 1);
});

test('the word stays the default download', function () {
    Process::fake();

    $this->actingAs($this->admin)
        ->get(route('actas.descargar', $this->acta))
        ->assertOk()
        ->assertDownload('acta-inicial.docx');

    Process::assertNothingRan();
});

test('a failed conversion explains the problem instead of returning a broken file', function () {
    Process::fake(fn () => Process::result(errorOutput: 'soffice: command not found', exitCode: 127));

    $this->actingAs($this->admin)
        ->getJson(route('actas.descargar', ['acta' => $this->acta, 'formato' => 'pdf']))
        ->assertStatus(503)
        ->assertJsonPath('message', fn (string $mensaje) => str_contains($mensaje, 'No se pudo generar el PDF'));

    Storage::disk('local')->assertMissing('actas/acta-inicial.pdf');
});

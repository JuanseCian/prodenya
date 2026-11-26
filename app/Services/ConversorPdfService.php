<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Convierte documentos Word guardados a PDF con LibreOffice (modo headless).
 *
 * El PDF se guarda junto al Word (misma ruta, extensión .pdf) y se reutiliza mientras
 * el Word no cambie: cuando un acta se corrige, su Word nuevo tiene otra ruta.
 */
class ConversorPdfService
{
    private const DISCO = 'local';

    /**
     * Ruta (en el disco local) del PDF del Word indicado; lo genera si todavía no existe.
     *
     * @throws RuntimeException si LibreOffice no está disponible o la conversión falla.
     */
    public function pdfDe(string $rutaWord): string
    {
        $disco = Storage::disk(self::DISCO);
        $rutaPdf = self::rutaPdf($rutaWord);

        if ($disco->exists($rutaPdf)) {
            return $rutaPdf;
        }

        if (! $disco->exists($rutaWord)) {
            throw new RuntimeException('No se encontró el documento a convertir.');
        }

        $directorio = storage_path('app/conversiones-pdf/'.Str::uuid());
        File::ensureDirectoryExists($directorio);

        try {
            $entrada = "{$directorio}/documento.docx";
            File::put($entrada, (string) $disco->get($rutaWord));

            $resultado = Process::timeout((int) config('services.libreoffice.timeout', 60))
                ->env(['HOME' => $directorio])
                ->run([
                    (string) config('services.libreoffice.binary', 'soffice'),
                    // Perfil propio por conversión: evita bloqueos si hay varias a la vez.
                    '-env:UserInstallation=file://'.$directorio.'/perfil',
                    '--headless',
                    '--norestore',
                    '--convert-to',
                    'pdf',
                    '--outdir',
                    $directorio,
                    $entrada,
                ]);

            $salida = "{$directorio}/documento.pdf";

            if ($resultado->failed() || ! File::exists($salida)) {
                throw new RuntimeException(trim('LibreOffice no pudo convertir el documento. '.$resultado->errorOutput()));
            }

            $disco->put($rutaPdf, File::get($salida));
        } finally {
            File::deleteDirectory($directorio);
        }

        return $rutaPdf;
    }

    /**
     * Ruta donde queda (o quedaría) el PDF de un Word.
     */
    public static function rutaPdf(string $rutaWord): string
    {
        return preg_replace('/\.docx$/i', '', $rutaWord).'.pdf';
    }
}

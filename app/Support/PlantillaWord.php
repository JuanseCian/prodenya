<?php

namespace App\Support;

use PhpOffice\PhpWord\TemplateProcessor;

/**
 * TemplateProcessor con manejo de bloques acotado a párrafos.
 *
 * Los bloques de PHPWord (cloneBlock/deleteBlock) usan una expresión glotona que,
 * cuando el bloque está dentro de una tabla, puede abarcar párrafos anteriores y
 * romper el documento.
 */
class PlantillaWord extends TemplateProcessor
{
    /**
     * Conserva el contenido del bloque (quitando sus marcas) o lo elimina entero.
     */
    public function resolverBloque(string $nombre, bool $conservar): void
    {
        $xml = $this->tempDocumentMainPart;
        $apertura = $this->parrafoConMacro($xml, $nombre);
        $cierre = $apertura ? $this->parrafoConMacro($xml, '/'.$nombre, $apertura[1]) : null;

        if (! $apertura || ! $cierre) {
            return;
        }

        [$inicioApertura, $finApertura] = $apertura;
        [$inicioCierre, $finCierre] = $cierre;

        $this->tempDocumentMainPart = $conservar
            ? substr($xml, 0, $inicioApertura).substr($xml, $finApertura, $inicioCierre - $finApertura).substr($xml, $finCierre)
            : substr($xml, 0, $inicioApertura).substr($xml, $finCierre);
    }

    /**
     * @return array{0: int, 1: int}|null Inicio y fin del párrafo que contiene la macro.
     */
    private function parrafoConMacro(string $xml, string $macro, int $desde = 0): ?array
    {
        $posicion = strpos($xml, '${'.$macro.'}', $desde);

        if ($posicion === false) {
            return null;
        }

        $inicio = max(
            (int) strrpos(substr($xml, 0, $posicion), '<w:p '),
            (int) strrpos(substr($xml, 0, $posicion), '<w:p>'),
        );
        $fin = strpos($xml, '</w:p>', $posicion);

        return $fin === false ? null : [$inicio, $fin + strlen('</w:p>')];
    }
}

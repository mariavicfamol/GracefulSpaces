<?php

if (!function_exists('pdf_pf_escapar')) {
    function pdf_pf_escapar(string $texto): string {
        // Prepara el texto para que se pueda escribir dentro de un PDF sin romper el formato.
        $texto = (string)$texto;
        $texto = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $texto);
        $convertido = @iconv('UTF-8', 'Windows-1252//TRANSLIT', $texto);

        if ($convertido !== false) {
            return $convertido;
        }

        if (function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($texto, 'Windows-1252', 'UTF-8');
        }

        return $texto;
    }
}

if (!function_exists('pdf_pf_limitar')) {
    function pdf_pf_limitar(string $texto, int $limite): string {
        // Reduce el texto para que no se salga de la columna en el reporte.
        $texto = trim(preg_replace('/\s+/', ' ', $texto));

        if ($limite <= 0) {
            return '';
        }

        if (function_exists('mb_strlen') && function_exists('mb_substr')) {
            if (mb_strlen($texto, 'UTF-8') <= $limite) {
                return $texto;
            }

            return mb_substr($texto, 0, max(0, $limite - 1), 'UTF-8') . '…';
        }

        if (strlen($texto) <= $limite) {
            return $texto;
        }

        return substr($texto, 0, max(0, $limite - 3)) . '...';
    }
}

if (!function_exists('pdf_pf_linea')) {
    function pdf_pf_linea(int $x, int $y, string $texto, int $tamano = 8, string $fuente = 'F1'): string {
        // Dibuja una línea de texto en una posición específica de la página.
        return "BT\n/{$fuente} {$tamano} Tf\n1 0 0 1 {$x} {$y} Tm\n(" . pdf_pf_escapar($texto) . ") Tj\nET\n";
    }
}

if (!function_exists('pdf_pf_rect')) {
    function pdf_pf_rect(int $x, int $y, int $ancho, int $alto, string $modo = 'f'): string {
        // Dibuja un rectángulo para fondos, tarjetas o encabezados.
        return sprintf('%d %d %d %d re %s\n', $x, $y, $ancho, $alto, $modo);
    }
}

if (!function_exists('generarPdfProductosFaltantes')) {
    function generarPdfProductosFaltantes(array $productos): string {
        // Construye el PDF completo del reporte de productos faltantes.
        $anchoPagina = 842;
        $altoPagina = 595;
        $margenIzquierdo = 28;
        $margenDerecho = 28;
        $margenSuperior = 24;
        $margenInferior = 28;
        $anchoUtil = $anchoPagina - $margenIzquierdo - $margenDerecho;
        $altoCabecera = 20;
        $altoFila = 16;
        $altoResumen = 18;
        $altoBarra = 34;
        $altoTarjeta = 34;

        // Calcula totales para mostrar un resumen rápido en la parte superior.
        $total = count($productos);
        $pendientes = 0;
        $comprados = 0;

        foreach ($productos as $producto) {
            if (($producto['estado'] ?? '') === 'Comprado') {
                $comprados++;
            } else {
                $pendientes++;
            }
        }

        // Define las columnas que tendrá la tabla del reporte.
        $columnas = [
            ['titulo' => 'Producto', 'ancho' => 110, 'campo' => 'nombre'],
            ['titulo' => 'Descripcion', 'ancho' => 180, 'campo' => 'descripcion'],
            ['titulo' => 'Cant.', 'ancho' => 42, 'campo' => 'cantidad_solicitada'],
            ['titulo' => 'Solicitante', 'ancho' => 120, 'campo' => 'solicitante'],
            ['titulo' => 'Fecha solicitud', 'ancho' => 90, 'campo' => 'fecha_solicitud'],
            ['titulo' => 'Estado', 'ancho' => 70, 'campo' => 'estado'],
            ['titulo' => 'Comprado por', 'ancho' => 110, 'campo' => 'comprador'],
        ];

        $sumaAnchos = array_sum(array_column($columnas, 'ancho'));
        if ($sumaAnchos > $anchoUtil) {
            $ajuste = $anchoUtil / $sumaAnchos;
            foreach ($columnas as &$columna) {
                $columna['ancho'] = (int)floor($columna['ancho'] * $ajuste);
            }
            unset($columna);
        }

        $paginas = [];
        $contenidoPagina = '';
        $numeroPagina = 1;

        // Esta función dibuja el encabezado de cada página.
        $dibujarEncabezado = function () use (
            &$contenidoPagina,
            $anchoPagina,
            $altoPagina,
            $margenIzquierdo,
            $margenDerecho,
            $anchoUtil,
            $columnas,
            $altoResumen,
            $altoCabecera,
            $altoBarra,
            $altoTarjeta,
            $total,
            $pendientes,
            $comprados,
            &$numeroPagina
        ): int {
            $y = $altoPagina - 28;

            $contenidoPagina .= "0.31 0.42 0.17 rg\n";
            $contenidoPagina .= pdf_pf_rect($margenIzquierdo, $y - 6, $anchoUtil, $altoBarra);
            $contenidoPagina .= "0.94 0.96 0.90 rg\n";
            $contenidoPagina .= pdf_pf_linea($margenIzquierdo + 14, $y + 2, 'Productos Faltantes', 18, 'F2');
            $contenidoPagina .= pdf_pf_linea($margenIzquierdo + 14, $y - 9, 'Reporte administrativo de solicitudes pendientes y compradas', 9, 'F1');
            $contenidoPagina .= pdf_pf_linea($anchoPagina - 190, $y + 1, date('d/m/Y H:i'), 9, 'F1');

            $y -= 52;

            // Resumen visual con totales del reporte.
            $tarjetas = [
                ['label' => 'Total', 'valor' => (string)$total, 'fondo' => '0.96 0.97 0.93 rg', 'texto' => '0.21 0.28 0.12 rg'],
                ['label' => 'Pendientes', 'valor' => (string)$pendientes, 'fondo' => '0.99 0.95 0.84 rg', 'texto' => '0.53 0.35 0.04 rg'],
                ['label' => 'Comprados', 'valor' => (string)$comprados, 'fondo' => '0.91 0.97 0.94 rg', 'texto' => '0.07 0.34 0.24 rg'],
            ];

            $anchoTarjeta = (int)floor(($anchoUtil - 24) / 3);
            $inicioTarjetas = $margenIzquierdo;

            foreach ($tarjetas as $indice => $tarjeta) {
                $x = $inicioTarjetas + ($indice * ($anchoTarjeta + 12));
                $contenidoPagina .= $tarjeta['fondo'] . "\n";
                $contenidoPagina .= pdf_pf_rect($x, $y - 4, $anchoTarjeta, $altoTarjeta);
                $contenidoPagina .= $tarjeta['texto'] . "\n";
                $contenidoPagina .= pdf_pf_linea($x + 12, $y + 8, $tarjeta['label'], 9, 'F1');
                $contenidoPagina .= pdf_pf_linea($x + 12, $y - 6, $tarjeta['valor'], 18, 'F2');
            }

            $y -= 40;

            $contenidoPagina .= "0.25 0.33 0.14 rg\n";
            $contenidoPagina .= pdf_pf_rect($margenIzquierdo, $y - 2, $anchoUtil, $altoCabecera);
            $contenidoPagina .= "1 1 1 rg\n";

            $anchoAcumulado = 0;
            foreach ($columnas as $columna) {
                $contenidoPagina .= pdf_pf_linea($margenIzquierdo + $anchoAcumulado + 4, $y + 7, $columna['titulo'], 8, 'F2');
                $anchoAcumulado += $columna['ancho'];
            }

            $y -= $altoCabecera;

            $anchoAcumulado = 0;
            $contenidoPagina .= "0 0 0 rg\n";
            $contenidoPagina .= pdf_pf_linea($margenIzquierdo, 18, 'Generado por GracefulSpaces', 8, 'F1');
            $contenidoPagina .= pdf_pf_linea($anchoPagina - $margenDerecho - 42, 18, 'Página ' . $numeroPagina, 8, 'F1');

            return $y - 10;
        };

        $y = $dibujarEncabezado();
        $filaAlterna = false;

        // Recorre los productos y agrega una fila por cada uno.
        foreach ($productos as $producto) {
            if ($y < $margenInferior + $altoFila) {
            // Si ya no hay espacio, guarda la página actual y comienza otra.
                $paginas[] = $contenidoPagina;
                $contenidoPagina = '';
                $numeroPagina++;
                $filaAlterna = false;
                $y = $dibujarEncabezado();
            }

            // Limita y formatea cada valor antes de pintarlo en la tabla.
            $valores = [
                pdf_pf_limitar((string)($producto['nombre'] ?? ''), 22),
                pdf_pf_limitar((string)($producto['descripcion'] ?? '--'), 34),
                (string)max(1, (int)($producto['cantidad_solicitada'] ?? 1)),
                pdf_pf_limitar((string)($producto['solicitante'] ?? '--'), 20),
                !empty($producto['fecha_solicitud']) ? date('d/m/Y', strtotime($producto['fecha_solicitud'])) : '--',
                pdf_pf_limitar((string)($producto['estado'] ?? '--'), 12),
                pdf_pf_limitar((string)($producto['comprador'] ?? '--'), 18),
            ];

            if ($filaAlterna) {
                $contenidoPagina .= "0.98 0.99 0.97 rg\n";
                $contenidoPagina .= pdf_pf_rect($margenIzquierdo, $y - 3, $anchoUtil, $altoFila);
                $contenidoPagina .= "0 0 0 rg\n";
            }

            $anchoAcumulado = 0;
            foreach ($columnas as $indice => $columna) {
                $texto = $valores[$indice] ?? '';
                $x = $margenIzquierdo + $anchoAcumulado + 4;

                if ($columna['campo'] === 'cantidad_solicitada') {
                    $x = $margenIzquierdo + $anchoAcumulado + $columna['ancho'] - 16;
                } elseif ($columna['campo'] === 'fecha_solicitud') {
                    $x = $margenIzquierdo + $anchoAcumulado + 6;
                } elseif ($columna['campo'] === 'estado') {
                    $estado = (string)($producto['estado'] ?? '--');
                    $contenidoPagina .= ($estado === 'Comprado' ? '0.20 0.64 0.42 rg\n' : '0.95 0.74 0.16 rg\n');
                    $contenidoPagina .= pdf_pf_rect($margenIzquierdo + $anchoAcumulado + 4, $y - 1, $columna['ancho'] - 8, 11);
                    $contenidoPagina .= "1 1 1 rg\n";
                    $contenidoPagina .= pdf_pf_linea($margenIzquierdo + $anchoAcumulado + 10, $y + 7, $texto, 8, 'F2');
                    $anchoAcumulado += $columna['ancho'];
                    continue;
                }

                $contenidoPagina .= pdf_pf_linea($x, $y, $texto, 8);
                $anchoAcumulado += $columna['ancho'];
            }

            $contenidoPagina .= "0.90 0.92 0.86 RG\n";
            $contenidoPagina .= sprintf('%d %d m %d %d l S\n', $margenIzquierdo, $y - 2, $margenIzquierdo + $anchoUtil, $y - 2);
            $y -= $altoFila;
            $filaAlterna = !$filaAlterna;
        }

        $paginas[] = $contenidoPagina;

        // Arma la estructura interna del archivo PDF.
        $objetos = [];
        $objetos[1] = '<< /Type /Catalog /Pages 2 0 R >>';
        $objetos[3] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';
        $objetos[4] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>';
        $objetos[5] = '<< /Producer (' . pdf_pf_escapar('GracefulSpaces') . ') /Title (' . pdf_pf_escapar('Productos Faltantes') . ') /Author (' . pdf_pf_escapar('GracefulSpaces') . ') >>';

        $refsPaginas = [];
        $totalPaginas = count($paginas);
        $inicioPaginas = 6;
        $inicioContenido = $inicioPaginas + $totalPaginas;

        for ($i = 0; $i < $totalPaginas; $i++) {
            $numPagina = $inicioPaginas + $i;
            $numContenido = $inicioContenido + $i;
            $refsPaginas[] = $numPagina . ' 0 R';

            $contenido = $paginas[$i];
            $objetos[$numContenido] = '<< /Length ' . strlen($contenido) . ' >>' . "\nstream\n" . $contenido . "\nendstream\n";
            $objetos[$numPagina] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 ' . $anchoPagina . ' ' . $altoPagina . '] /Resources << /Font << /F1 3 0 R /F2 4 0 R >> >> /Contents ' . $numContenido . ' 0 R >>';
        }

        $objetos[2] = '<< /Type /Pages /Kids [' . implode(' ', $refsPaginas) . '] /Count ' . $totalPaginas . ' >>';

        ksort($objetos);

        $pdf = "%PDF-1.4\n";
        $offsets = [0 => 0];
        $numeroObjeto = 1;

        foreach ($objetos as $objeto) {
            $offsets[$numeroObjeto] = strlen($pdf);
            $pdf .= $numeroObjeto . " 0 obj\n" . $objeto . "\nendobj\n";
            $numeroObjeto++;
        }

        // Crea el índice final para que el lector PDF pueda ubicar cada objeto.
        $inicioXref = strlen($pdf);
        $pdf .= "xref\n0 " . $numeroObjeto . "\n";
        $pdf .= "0000000000 65535 f \n";

        for ($i = 1; $i < $numeroObjeto; $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }

        $pdf .= "trailer\n<< /Size " . $numeroObjeto . " /Root 1 0 R /Info 5 0 R >>\n";
        $pdf .= "startxref\n" . $inicioXref . "\n%%EOF";

        return $pdf;
    }
}

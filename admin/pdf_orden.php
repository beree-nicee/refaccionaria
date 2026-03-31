<?php

function generarPDFOrden($id_orden, Orden $app) {

    // ── Cargar TCPDF ────────────────────────────────────────────────
    $autoload = __DIR__ . '/vendor/autoload.php';
    if (!file_exists($autoload)) {
        die('<div style="font-family:sans-serif;padding:2rem;color:#c00">
            <h2>TCPDF no instalado</h2>
            <p>Ejecuta en terminal dentro de la carpeta <code>admin/</code>:</p>
            <pre style="background:#f4f4f4;padding:1rem;border-radius:6px">composer require tecnickcom/tcpdf</pre>
            <p>Luego recarga esta página.</p>
        </div>');
    }
    require_once $autoload;

    // ── Datos ────────────────────────────────────────────────────────
    $orden    = $app->leerUno($id_orden);
    $detalles = $app->obtenerDetalles($id_orden);

    if (!$orden) {
        die('Orden no encontrada');
    }

    $numeroOrden  = str_pad($orden['id_orden'], 4, '0', STR_PAD_LEFT);
    $fechaOrden   = date('d/m/Y H:i', strtotime($orden['fecha_orden']));
    $nombreCliente = trim(($orden['nombre'] ?? '') . ' ' . ($orden['apellidos'] ?? ''));
    $email        = $orden['email'] ?? '';

    // ── Configurar TCPDF ─────────────────────────────────────────────
    $pdf = new TCPDF('P', 'mm', 'LETTER', true, 'UTF-8', false);

    $pdf->SetCreator('Sistema Taller');
    $pdf->SetAuthor('Taller Refaccionaria');
    $pdf->SetTitle("Orden de Compra #$numeroOrden");
    $pdf->SetSubject('Orden de Compra');

    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins(15, 15, 15);
    $pdf->SetAutoPageBreak(true, 20);
    $pdf->AddPage();

    // ── Colores del tema ─────────────────────────────────────────────
    $colorOscuro = [30,  30,  30 ];
    $colorPrimario = [13, 110, 253];   // Bootstrap primary
    $colorGris   = [108, 117, 125];
    $colorClaro  = [248, 249, 250];
    $colorBorde  = [222, 226, 230];

    // ── ENCABEZADO: Logo + Nombre negocio ────────────────────────────
    $logoPath = __DIR__ . '/../images/logo.png';
    if (file_exists($logoPath)) {
        $pdf->Image($logoPath, 15, 12, 35);
        $pdf->SetXY(55, 12);
    } else {
        $pdf->SetXY(15, 12);
    }

    $pdf->SetFont('helvetica', 'B', 18);
    $pdf->SetTextColor(...$colorOscuro);
    $pdf->Cell(0, 9, 'TALLER REFACCIONARIA', 0, 1, 'L');

    $pdf->SetFont('helvetica', '', 9);
    $pdf->SetTextColor(...$colorGris);
    $pdf->SetX(file_exists($logoPath) ? 55 : 15);
    $pdf->Cell(0, 5, 'Sistema de Gestión de Taller', 0, 1, 'L');

    // Número de orden (esquina derecha)
    $pdf->SetFont('helvetica', 'B', 22);
    $pdf->SetTextColor(...$colorPrimario);
    $pdf->SetXY(130, 12);
    $pdf->Cell(65, 10, "#$numeroOrden", 0, 1, 'R');

    $pdf->SetFont('helvetica', '', 9);
    $pdf->SetTextColor(...$colorGris);
    $pdf->SetX(130);
    $pdf->Cell(65, 5, 'ORDEN DE COMPRA', 0, 1, 'R');
    $pdf->SetX(130);
    $pdf->Cell(65, 5, $fechaOrden, 0, 1, 'R');

    // Línea separadora
    $pdf->SetY(35);
    $pdf->SetDrawColor(...$colorPrimario);
    $pdf->SetLineWidth(0.8);
    $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
    $pdf->Ln(5);

    // ── SECCIÓN: Datos del cliente + Estado ──────────────────────────
    $yDatos = $pdf->GetY();

    // Caja cliente
    $pdf->SetFillColor(...$colorClaro);
    $pdf->SetDrawColor(...$colorBorde);
    $pdf->SetLineWidth(0.3);
    $pdf->Rect(15, $yDatos, 90, 28, 'DF');

    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->SetTextColor(...$colorGris);
    $pdf->SetXY(18, $yDatos + 3);
    $pdf->Cell(0, 4, 'CLIENTE', 0, 1);

    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetTextColor(...$colorOscuro);
    $pdf->SetX(18);
    $pdf->Cell(0, 5, $nombreCliente ?: 'Sin nombre', 0, 1);

    $pdf->SetFont('helvetica', '', 9);
    $pdf->SetTextColor(...$colorGris);
    $pdf->SetX(18);
    $pdf->Cell(0, 4, $email, 0, 1);

    // Caja estado + pago
    $pdf->SetFillColor(...$colorClaro);
    $pdf->Rect(110, $yDatos, 85, 28, 'DF');

    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->SetTextColor(...$colorGris);
    $pdf->SetXY(113, $yDatos + 3);
    $pdf->Cell(0, 4, 'ESTADO DE LA ORDEN', 0, 1);

    $estadoColores = [
        'pendiente'  => [255, 193, 7],
        'procesando' => [13,  202, 240],
        'completada' => [25,  135, 84],
        'cancelada'  => [220, 53,  69],
    ];
    $estColor = $estadoColores[$orden['estado_orden']] ?? [108, 117, 125];

    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->SetTextColor(...$estColor);
    $pdf->SetX(113);
    $pdf->Cell(0, 6, strtoupper($orden['estado_orden']), 0, 1);

    $pdf->SetFont('helvetica', '', 9);
    $pdf->SetTextColor(...$colorGris);
    $pdf->SetX(113);
    $pdf->Cell(40, 4, 'Método de pago:', 0, 0);
    $pdf->SetTextColor(...$colorOscuro);
    $pdf->Cell(0, 4, $orden['metodo_pago'] ?? 'No especificado', 0, 1);

    $pdf->SetY($yDatos + 32);

    // ── TABLA: Refacciones ────────────────────────────────────────────
    if (!empty($detalles['refacciones'])) {
        $pdf->Ln(4);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetTextColor(...$colorOscuro);
        $pdf->Cell(0, 6, 'REFACCIONES', 0, 1);

        // Encabezado tabla
        $pdf->SetFillColor(...$colorOscuro);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->SetLineWidth(0);

        $pdf->Cell(25,  7, 'Código',     1, 0, 'C', true);
        $pdf->Cell(75,  7, 'Producto',   1, 0, 'L', true);
        $pdf->Cell(20,  7, 'Cantidad',   1, 0, 'C', true);
        $pdf->Cell(32,  7, 'Precio unit.',1, 0, 'R', true);
        $pdf->Cell(28,  7, 'Subtotal',   1, 1, 'R', true);

        // Filas
        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetTextColor(...$colorOscuro);
        $pdf->SetDrawColor(...$colorBorde);
        $pdf->SetLineWidth(0.2);

        $fill = false;
        foreach ($detalles['refacciones'] as $r) {
            $colors = $fill ? [249, 250] : [255, 255];
            $pdf->SetFillColor($fill ? 248 : 255, ...$colors);
            $pdf->Cell(25,  6, $r['codigo_producto'], 'B', 0, 'C', $fill);
            $pdf->Cell(75,  6, $r['nombre'],          'B', 0, 'L', $fill);
            $pdf->Cell(20,  6, $r['cantidad'],         'B', 0, 'C', $fill);
            $pdf->Cell(32,  6, '$' . number_format($r['precio_unitario'], 2), 'B', 0, 'R', $fill);
            $pdf->Cell(28,  6, '$' . number_format($r['subtotal'], 2),        'B', 1, 'R', $fill);
            $fill = !$fill;
        }
        $pdf->Ln(2);
    }

    // ── TABLA: Servicios ──────────────────────────────────────────────
    if (!empty($detalles['servicios'])) {
        $pdf->Ln(2);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetTextColor(...$colorOscuro);
        $pdf->Cell(0, 6, 'SERVICIOS', 0, 1);

        $pdf->SetFillColor(...$colorOscuro);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('helvetica', 'B', 8);

        $pdf->Cell(152, 7, 'Servicio',     1, 0, 'L', true);
        $pdf->Cell(28,  7, 'Precio',       1, 1, 'R', true);

        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetTextColor(...$colorOscuro);
        $pdf->SetDrawColor(...$colorBorde);

        $fill = false;
        foreach ($detalles['servicios'] as $s) {
            $pdf->SetFillColor($fill ? 248 : 255, $fill ? 249 : 255, $fill ? 250 : 255);
            $pdf->Cell(152, 6, $s['nombre_servicio'],                       'B', 0, 'L', $fill);
            $pdf->Cell(28,  6, '$' . number_format($s['precio_servicio'], 2),'B', 1, 'R', $fill);
            $fill = !$fill;
        }
        $pdf->Ln(2);
    }

    // ── TOTALES ───────────────────────────────────────────────────────
    $pdf->Ln(4);
    $pdf->SetDrawColor(...$colorBorde);
    $pdf->SetLineWidth(0.3);

    $xTot = 120;
    $wLbl = 45;
    $wVal = 30;

    $pdf->SetFont('helvetica', '', 9);
    $pdf->SetTextColor(...$colorGris);
    $pdf->SetX($xTot);
    $pdf->Cell($wLbl, 6, 'Subtotal refacciones:', 0, 0, 'R');
    $pdf->SetTextColor(...$colorOscuro);
    $pdf->Cell($wVal, 6, '$' . number_format($orden['total_refacciones'], 2), 0, 1, 'R');

    $pdf->SetTextColor(...$colorGris);
    $pdf->SetX($xTot);
    $pdf->Cell($wLbl, 6, 'Subtotal servicios:', 0, 0, 'R');
    $pdf->SetTextColor(...$colorOscuro);
    $pdf->Cell($wVal, 6, '$' . number_format($orden['total_servicios'], 2), 0, 1, 'R');

    // Línea antes del total
    $pdf->SetDrawColor(...$colorPrimario);
    $pdf->SetLineWidth(0.5);
    $pdf->Line($xTot, $pdf->GetY(), 195, $pdf->GetY());
    $pdf->Ln(2);

    $pdf->SetFillColor(...$colorPrimario);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetX($xTot);
    $pdf->Cell($wLbl + $wVal, 8, 'TOTAL   $' . number_format($orden['total_general'], 2), 0, 1, 'R', true);

    // ── NOTAS ─────────────────────────────────────────────────────────
    if (!empty($orden['notas_especiales'])) {
        $pdf->Ln(6);
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetTextColor(...$colorOscuro);
        $pdf->Cell(0, 5, 'Notas especiales:', 0, 1);
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(...$colorGris);
        $pdf->MultiCell(0, 5, $orden['notas_especiales'], 0, 'L');
    }

    // ── PIE DE PÁGINA ─────────────────────────────────────────────────
    $pdf->SetY(-25);
    $pdf->SetDrawColor(...$colorBorde);
    $pdf->SetLineWidth(0.3);
    $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
    $pdf->Ln(3);
    $pdf->SetFont('helvetica', 'I', 7);
    $pdf->SetTextColor(...$colorGris);
    $pdf->Cell(0, 4, 'Documento generado el ' . date('d/m/Y H:i') . ' — Sistema de Gestión de Taller', 0, 1, 'C');
    $pdf->Cell(0, 4, 'Este documento es un comprobante de su orden de compra.', 0, 1, 'C');

    // ── SALIDA ────────────────────────────────────────────────────────
    $pdf->Output("orden_{$numeroOrden}.pdf", 'D'); // D = descarga directa
}
?>

<?php
/* Lab 9 - Estructuras de control en PHP
   Carlos Coba · semilla 741
   Todo sale de la semilla, nada va quemado. */

const SEMILLA = 741;
const NOMBRE = "Carlos";
const APELLIDO = "Coba";

// precios del cine
const PRECIO_NINO     = 3.00;
const PRECIO_JOVEN    = 4.50;
const PRECIO_ADULTO   = 7.00;
const PRECIO_JUBILADO = 3.50;

const ITBMS = 0.07;

$nombreCompleto = NOMBRE . " " . APELLIDO;
$edad  = 8 + (SEMILLA % 70);
$dia   = (SEMILLA % 7) + 1;      // 1=lunes ... 7=domingo
$mes   = (SEMILLA % 12) + 1;     // 1=enero ... 12=diciembre
$nota1 = 40 + (SEMILLA % 61);
$nota2 = 40 + ((SEMILLA * 3) % 61);
$nota3 = 40 + ((SEMILLA * 7) % 61);
$monto = 20 + (SEMILLA % 181);
$esMiembro = (SEMILLA % 2) == 0;
$hora  = SEMILLA % 24;
$huella = (SEMILLA * 13 + 91) % 1000;
$descuento = SEMILLA * 0.001;
$vacio = null;

// ---------- Ejercicio 1: boleto de cine ----------
if ($edad < 12) {
    $categoria = "Niño";
    $precio = PRECIO_NINO;
} elseif ($edad <= 17) {
    $categoria = "Joven";
    $precio = PRECIO_JOVEN;
} elseif ($edad <= 59) {
    $categoria = "Adulto";
    $precio = PRECIO_ADULTO;
} else {
    $categoria = "Jubilado";
    $precio = PRECIO_JUBILADO;
}

// los miércoles el cine cobra la mitad
$esMiercoles = ($dia === 3);
if ($esMiercoles) {
    $precio = $precio * 0.5;
}

// ---------- Ejercicio 2: notas ----------
$promedio = ($nota1 + $nota2 + $nota3) / 3;

if ($promedio >= 90) {
    $letra = "A";
} elseif ($promedio >= 80) {
    $letra = "B";
} elseif ($promedio >= 70) {
    $letra = "C";
} elseif ($promedio >= 60) {
    $letra = "D";
} else {
    $letra = "F";
}

$estado = ($promedio >= 70) ? "Aprobado" : "Reprobado";

// cuadro de honor: promedio alto y ninguna nota floja
$honor = ($promedio >= 90 && $nota1 >= 80 && $nota2 >= 80 && $nota3 >= 80) ? "Sí" : "No";

// clase del párrafo, verde si pasó y rojo si no
$clase = ($promedio >= 70) ? "ok" : "error";

// ---------- Ejercicio 3: día de la semana ----------
switch ($dia) {
    case 1:
        $nombreDia = "Lunes";
        break;
    case 2:
        $nombreDia = "Martes";
        break;
    case 3:
        $nombreDia = "Miércoles";
        break;
    case 4:
        $nombreDia = "Jueves";
        break;
    case 5:
        $nombreDia = "Viernes";
        break;
    case 6:
        $nombreDia = "Sábado";
        break;
    case 7:
        $nombreDia = "Domingo";
        break;
    default:
        $nombreDia = "Desconocido";
}

switch ($dia) {
    case 1:
    case 2:
    case 3:
    case 4:
    case 5:
        $tipoDia = "Laboral";
        break;
    case 6:
    case 7:
        $tipoDia = "Fin de semana";
        break;
    default:
        $tipoDia = "Desconocido";
}

// ---------- Ejercicio 4: mes y estación ----------
$nombreMes = match ($mes) {
    1 => "Enero",
    2 => "Febrero",
    3 => "Marzo",
    4 => "Abril",
    5 => "Mayo",
    6 => "Junio",
    7 => "Julio",
    8 => "Agosto",
    9 => "Septiembre",
    10 => "Octubre",
    11 => "Noviembre",
    12 => "Diciembre",
    default => "Desconocido",
};

// en Panamá: seca de diciembre a abril, lluviosa el resto
$estacion = match ($mes) {
    12, 1, 2, 3, 4 => "Seca",
    5, 6, 7, 8, 9, 10, 11 => "Lluviosa",
    default => "Desconocida",
};

// ---------- Ejercicio 5: caja de la tienda ----------
if ($monto >= 150) {
    $pct = 15;
} elseif ($monto >= 100) {
    $pct = 10;
} elseif ($monto >= 50) {
    $pct = 5;
} else {
    $pct = 0;
}

if ($esMiembro) {
    $pct = $pct + 5;
}

$base = $monto - ($monto * $pct / 100);

if ($hora >= 22 || $hora < 6) {
    $base = $base * 1.03;          // recargo de 3 %
    $recargo = "Sí";
} else {
    $recargo = "No";
}

$total = $base + ($base * ITBMS);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="lab9-huella" content="<?= $huella ?>">
    <title>Lab 9 · Estructuras de control</title>
    <link rel="stylesheet" href="css/estilos.css">
    <style>
        header {
            background: #fdf3e7;
            border-left: 6px solid #b5651d;
            padding: 14px 18px;
            margin-bottom: 22px;
        }
        header p {
            margin: 4px 0 0;
            color: #6b5d50;
            font-size: 14px;
        }
    </style>
</head>
<body>

<header>
    <h1 style="margin: 0; color: #7a2e0e; font-size: 26px;">Laboratorio 9 · Estructuras de control en PHP</h1>
    <p>Desarrollo Web · Módulo III · Semilla <?= SEMILLA ?></p>
</header>

<main id="lab9-coba">

    <section id="ej0">
        <h2>Ejercicio 0 · Datos y tipos</h2>
        <p id="autor">Autor: <?= $nombreCompleto ?></p>

        <table id="ej0-tipos">
            <thead>
                <tr>
                    <th>Variable</th>
                    <th>Tipo (gettype)</th>
                </tr>
            </thead>
            <tbody>
                <tr><td>$nombreCompleto</td><td><?= gettype($nombreCompleto) ?></td></tr>
                <tr><td>$edad</td><td><?= gettype($edad) ?></td></tr>
                <tr><td>$esMiembro</td><td><?= gettype($esMiembro) ?></td></tr>
                <tr><td>$descuento</td><td><?= gettype($descuento) ?></td></tr>
                <tr><td>$vacio</td><td><?= gettype($vacio) ?></td></tr>
            </tbody>
        </table>

        <p class="nota">
            Semilla <?= SEMILLA ?>: edad <?= $edad ?>, día <?= $dia ?>, mes <?= $mes ?>,
            monto B/. <?= number_format($monto, 2) ?>, hora <?= $hora ?>.
        </p>
    </section>

    <section id="ej1">
        <h2>Ejercicio 1 · Boleto de cine</h2>
        <p id="ej1-res">Categoría: <?= $categoria ?> | Precio final: B/. <?= number_format($precio, 2) ?> | Miércoles 50%: <?= $esMiercoles ? "Sí" : "No" ?></p>
    </section>

    <section id="ej2">
        <h2>Ejercicio 2 · Notas del estudiante</h2>
        <p id="ej2-res" class="resultado <?= $clase ?>">Promedio: <?= number_format($promedio, 2) ?> | Letra: <?= $letra ?> | Estado: <?= $estado ?> | Cuadro de honor: <?= $honor ?></p>
    </section>

    <section id="ej3">
        <h2>Ejercicio 3 · Día de la semana</h2>
        <p id="ej3-res">Día: <?= $nombreDia ?> | Tipo: <?= $tipoDia ?></p>
    </section>

    <section id="ej4">
        <h2>Ejercicio 4 · Mes y estación</h2>
        <p id="ej4-res">Mes: <?= $nombreMes ?> | Estación: <?= $estacion ?></p>
    </section>

    <section id="ej5">
        <h2>Ejercicio 5 · Caja de la tienda</h2>
        <p id="ej5-monto">Monto de compra: B/. <?= number_format($monto, 2) ?></p>
        <p id="ej5-descuento">Descuento aplicado: <?= $pct ?>%</p>
        <p id="ej5-recargo">Recargo nocturno: <?= $recargo ?></p>
        <p id="ej5-total">Total a pagar: B/. <?= number_format($total, 2) ?></p>
    </section>

</main>

<footer id="firma">Lab 9 · <?= $nombreCompleto ?> · Semilla <?= SEMILLA ?></footer>

</body>
</html>

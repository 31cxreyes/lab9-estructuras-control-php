<?php
/*
 * LABORATORIO #9 · Estructuras de control en PHP
 * Sigue el documento del estudiante. Escribe tu código SOLO en las zonas marcadas con TODO.
 * Recuerda: un commit por ejercicio (ej0, ej1, ... css, final).
 */

// ---------- PARTE 0 · TUS DATOS ----------
// TODO 0.1: define las constantes SEMILLA (últimos 3 dígitos de tu cédula, como número),
//           NOMBRE y APELLIDO.
// TODO 0.2: crea $nombreCompleto, $edad, $dia, $mes, $nota1, $nota2, $nota3, $monto,
//           $esMiembro, $hora, $huella, $descuento y $vacio (fórmulas en el documento).

// ---------- EJERCICIO 1 · Boleto de cine (if / elseif / else) ----------
// TODO 1

// ---------- EJERCICIO 2 · Notas (elseif, ternario, &&) ----------
// TODO 2

// ---------- EJERCICIO 3 · Día de la semana (switch) ----------
// TODO 3

// ---------- EJERCICIO 4 · Mes y estación (match) ----------
// TODO 4

// ---------- EJERCICIO 5 · Caja de la tienda (reto integrador) ----------
// TODO 5
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- TODO 0.3: <meta name="lab9-huella" content="..."> con el valor de $huella -->
  <title>Lab 9 · Estructuras de control</title>
  <!-- TODO CSS-1: enlaza css/estilos.css (CSS externo) -->
  <!-- TODO CSS-2: agrega un <style> interno con al menos 2 reglas -->
</head>
<body>
  <header>
    <!-- TODO CSS-3: agrega un atributo style (CSS en línea) a este h1 -->
    <h1>Laboratorio #9 · Estructuras de control</h1>
    <p id="autor"><!-- TODO 0.4: "Autor: Nombre Apellido" --></p>
  </header>

  <!-- TODO 0.5: el id de <main> es "lab9-" + tu apellido en minúsculas, sin tildes (ver documento) -->
  <main id="">

    <section id="ej0">
      <h2>Ejercicio 0 · Tipos de datos</h2>
      <table id="ej0-tipos">
        <thead>
          <tr><th>Variable</th><th>Tipo</th></tr>
        </thead>
        <tbody>
          <!-- TODO 0.6: 5 filas <tr> con el nombre de la variable y su gettype() -->
        </tbody>
      </table>
    </section>

    <section id="ej1">
      <h2>Ejercicio 1 · Boleto de cine</h2>
      <p id="ej1-res"><!-- TODO 1 --></p>
    </section>

    <section id="ej2">
      <h2>Ejercicio 2 · Notas</h2>
      <!-- TODO CSS-4: agrega la clase "ok" o "error" con PHP según el estado -->
      <p id="ej2-res" class="resultado"><!-- TODO 2 --></p>
    </section>

    <section id="ej3">
      <h2>Ejercicio 3 · Día de la semana</h2>
      <p id="ej3-res"><!-- TODO 3 --></p>
    </section>

    <section id="ej4">
      <h2>Ejercicio 4 · Mes y estación</h2>
      <p id="ej4-res"><!-- TODO 4 --></p>
    </section>

    <section id="ej5">
      <h2>Ejercicio 5 · Caja de la tienda</h2>
      <p id="ej5-monto"><!-- TODO 5 --></p>
      <p id="ej5-descuento"><!-- TODO 5 --></p>
      <p id="ej5-recargo"><!-- TODO 5 --></p>
      <p id="ej5-total"><!-- TODO 5 --></p>
    </section>

  </main>

  <footer id="firma"><!-- TODO 0.7: "Lab 9 · Nombre Apellido · Semilla N" --></footer>
</body>
</html>

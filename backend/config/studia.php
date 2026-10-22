<?php

return [
    // Zona en la que se le muestran las fechas a las personas (asistente, reportes).
    // La base de datos y la API siguen en UTC (APP_TIMEZONE).
    'timezone' => env('STUDIA_TIMEZONE', 'America/Mexico_City'),

    'promedios' => [
        // true: una tarea vencida y sin entrega cuenta como 0. false: no cuenta hasta que se entregue.
        'vencidas_como_cero' => (bool) env('STUDIA_VENCIDAS_COMO_CERO', true),
    ],
];
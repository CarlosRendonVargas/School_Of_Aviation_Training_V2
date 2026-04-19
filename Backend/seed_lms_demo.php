<?php
use App\Models\Materia;

$materiasDemo = [
    'AER-01' => [
        'link_meet' => 'https://meet.google.com/abc-defg-hij',
        'documento_url' => 'https://aerocivil.gov.co/normatividad/RAC%20141.pdf',
        'temario' => "1. Introducción a la Aerodinámica\n2. Fuerzas que actúan en el vuelo\n3. Estabilidad y Control\n4. Dispositivos Hipersustentadores"
    ],
    'MET-02' => [
        'link_meet' => 'https://meet.google.com/xyz-uvwx-yza',
        'documento_url' => 'http://www.ideam.gov.co/documents/24155/125494/Meteorologia+Aeronautica.pdf',
        'temario' => "1. La Atmósfera\n2. Presión, Densidad y Altura\n3. Masas de Aire y Frentes\n4. Reportes METAR y TAF"
    ],
    'NAV-03' => [
        'link_meet' => 'https://meet.google.com/qwe-rtyu-iop',
        'documento_url' => 'https://skybrary.aero/sites/default/files/bookshelf/123.pdf',
        'temario' => "1. La Tierra y sus Coordenadas\n2. Cartas Aeronáuticas\n3. Planificación de Vuelo\n4. Uso del Computador E6B"
    ]
];

foreach ($materiasDemo as $codigo => $data) {
    $materia = Materia::where('codigo', 'like', "%$codigo%")->first();
    if ($materia) {
        $materia->update($data);
        echo "Materia {$materia->nombre} actualizada con datos DEMO.\n";
    }
}
echo "Carga de datos demo completada.\n";

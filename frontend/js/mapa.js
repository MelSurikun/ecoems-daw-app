// ── ECOEMS — mapa.js ────────────────────────────────────────
// Leaflet map shared by mapa.php

let mapaLeaflet;
let marcadoresLayer;

function inicializarMapa(elementId) {
    mapaLeaflet = L.map(elementId, {
        center: [19.4, -99.1],
        zoom: 10,
        zoomControl: true
    });
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://osm.org/copyright">OpenStreetMap</a>',
        maxZoom: 18
    }).addTo(mapaLeaflet);
    marcadoresLayer = L.layerGroup().addTo(mapaLeaflet);
}

function colorPorInst(clave) {
    const pref = (clave || '').substring(0, 2);
    const colores = {
        'B0': '#023047',
        'I5': '#ffb703',
        'U6': '#fb8500',
        'D4': '#1E64C8',
        'C1': '#2E7D32',
        'G2': '#6B3FA0',
        'E0': '#e63946',
        'S0': '#457b9d',
        'S1': '#457b9d',
        'S2': '#457b9d',
        'S4': '#457b9d',
        'S5': '#457b9d',
        'S7': '#457b9d',
        'S8': '#457b9d',
        'M9': '#a67c52'
    };
    return colores[pref] || '#6B3FA0';
}

function crearIcono(color, puntaje) {
    const size = 40;
    const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="${size}" height="${size}" viewBox="0 0 ${size} ${size}">
        <circle cx="${size/2}" cy="${size/2}" r="${size/2-1}" fill="${color}" stroke="#fff" stroke-width="2"/>
        <text x="${size/2}" y="${size/2+4}" text-anchor="middle" fill="#fff"
              font-family="Sora,sans-serif" font-size="11" font-weight="700">${puntaje}</text>
    </svg>`;
    return L.divIcon({
        html: svg,
        className: '',
        iconSize: [size, size],
        iconAnchor: [size/2, size],
        popupAnchor: [0, -size]
    });
}

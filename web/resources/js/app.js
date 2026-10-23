import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

// Ensure any opened Bootstrap modal is attached directly to <body> so it always sits above the backdrop (z-index: 1055 > 1050)
document.addEventListener('show.bs.modal', (e) => {
    if (e.target && e.target.parentElement !== document.body) {
        document.body.appendChild(e.target);
    }
});

import './sidebar.js';
import './skeleton.js';
import '../scss/style.scss';
import ApexCharts from 'apexcharts';

window.ApexCharts = ApexCharts;



/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to quickly build robust real-time web applications.
 */

import './echo';

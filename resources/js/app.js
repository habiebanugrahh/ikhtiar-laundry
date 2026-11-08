import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

window.loadChart = () => import('chart.js/auto').then((module) => module.default);
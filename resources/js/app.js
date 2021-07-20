import { Chart, registerables } from 'chart.js';
Chart.register(...registerables);

window.memoryTrackerCharts = {};

for (const trackerChart of memoryTrackerCharts) {
    const chartInstance = document.getElementById('memory-tracker-chart-' + trackerChart['key']);
    const ctx = chartInstance.getContext('2d');

    window.memoryTrackerCharts[trackerChart['key']] = new Chart(ctx, {
        type: 'line',
        data: {
            labels: trackerChart['timeseries']['labels'],
            datasets: [{
                borderColor: 'rgb(101, 116, 205)',
                borderWidth: 2,
                radius: 0,
                data: trackerChart['timeseries']['data'],
                label: 'Memory',
            }]
        },
        options: {
            animation: false,
            interaction: {
                intersect: false,
            },
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false,
                },
                tooltip: {
                    displayColors: false,
                    callbacks: {
                        label: (ctx) => ' ' + ctx.parsed.y + ' MB',
                    },
                },
            },
            elements: {
                line: {
                    tension: 0,
                },
            },
            scales: {
                x: {
                    grid: {
                        display: false,
                    },
                    ticks: {
                        maxTicksLimit: 15,
                    },
                },
                y: {
                    min: 0,
                    max: trackerChart['timeseries']['max'],
                    ticks: {
                        stepSize: .1,
                    }
                },
            },
        },
    });
}

// Listen for updates from Livewire
Livewire.on('memoryTrackerUpdated', charts => {
    for (const trackerChart of charts) {
        window.memoryTrackerCharts[trackerChart['key']].data.labels = trackerChart['timeseries']['labels'];
        window.memoryTrackerCharts[trackerChart['key']].data.datasets[0].data = trackerChart['timeseries']['data'];
        window.memoryTrackerCharts[trackerChart['key']].update();
    }
});

function toggle(event) {
    api_post('change', { id: event.getAttribute('device_id') });
}
const vw = Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0);
const vh = Math.max(document.documentElement.clientHeight || 0, window.innerHeight || 0);
var config = {

    type: 'line',
    data: {
        labels: [],
        datasets: [{
            label: 'Wewnętrzna',
            backgroundColor: "powderblue",
            borderColor: "powderblue",
            fill: false,
            hidden: true
        },
        {
            label: 'Zewnętrzna',
            backgroundColor: "rgb(255, 99, 255)",
            borderColor: "rgb(255, 99, 255)",
            fill: false,
        }]
    },
    options: {
        maintainAspectRatio: false,
        responsive: true,
        title: {
            display: false
        },
        tooltips: {
            mode: 'index',
            intersect: false,
        },
        hover: {
            mode: 'nearest',
            intersect: true
        },
        scales: {
            xAxes: [{
                display: true,
                scaleLabel: {
                    display: false,
                    fontColor: 'white'
                }
            }],
            yAxes: [{
                display: true,
                scaleLabel: {
                    display: true,
                    labelString: '°C'
                }
            }]
        }
    }
};


document.addEventListener("DOMContentLoaded", () => {

    Chart.defaults.global.defaultFontColor = "#fff";
    myChart = new Chart(document.getElementById('chart').getContext('2d'), config);
    temperatureData.forEach(n => {
        var d = new Date(n['date']);
        var datestring = ("0" + d.getDate()).slice(-2) + "." + ("0" + (d.getMonth() + 1)).slice(-2) + "." +
            d.getFullYear() + " " + ("0" + d.getHours()).slice(-2) + ":" + ("0" + d.getMinutes()).slice(-2);
        myChart.data.labels.push(datestring);
        myChart.data.datasets[0].data.push(n['indoor']);
        myChart.data.datasets[1].data.push(n['outdoor']);
    });
    myChart.update();
});
const vw = Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0);
const vh = Math.max(document.documentElement.clientHeight || 0, window.innerHeight || 0);

function GitHome_fetch(url, data = null, method = "GET")
{
    const formData = new FormData();
    
    if (data != null)
        for(const name in data)
            formData.append(name, obj[name]);
    
    if (method == "GET")
    {
        fetch(url, {
            method: "GET"
        }).then(() => {location.reload()});
    }
    else
    {
        fetch(url, {
            method: method,
            body: formData
        }).then(() => {location.reload()});
    }

}

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


document.addEventListener("DOMContentLoaded", async () => {

    Chart.defaults.global.defaultFontColor = "#fff";
    myChart = new Chart(document.getElementById('chart').getContext('2d'), config);

    const outdoorTemperature = await (await fetch('/temperature/get/OUTDOOR/month')).json();
    const indoorTemperature = await (await fetch('/temperature/get/INDOOR/month')).json();

    outdoorTemperature.forEach(n => {
        var d = new Date(n['date']);
        var datestring = ("0" + d.getDate()).slice(-2) + "." + ("0" + (d.getMonth() + 1)).slice(-2) + "." +
            d.getFullYear() + " " + ("0" + d.getHours()).slice(-2) + ":" + ("0" + d.getMinutes()).slice(-2);
        myChart.data.labels.push(datestring);
        myChart.data.datasets[1].data.push(n['value']);
    });

    indoorTemperature.forEach(n => {
        myChart.data.datasets[0].data.push(n['value']);
    });
    
    myChart.update();
});
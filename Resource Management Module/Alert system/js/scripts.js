setInterval(() => {
    fetch('fetch_alerts.php')
        .then(res => res.text())
        .then(data => {
            document.getElementById('alerts').innerHTML = data;
        });
}, 3000);

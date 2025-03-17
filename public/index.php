<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Smart PHP Log Analyzer Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f2f2;
            margin: 0;
            padding: 20px;
        }
        h1 {
            color: #333;
        }
        #log {
            border: 1px solid #ccc;
            background: #fff;
            height: 400px;
            overflow-y: auto;
            padding: 10px;
            margin-bottom: 20px;
        }
        .log-entry {
            padding: 5px;
            border-bottom: 1px solid #eee;
            opacity: 0;
            animation: fadeIn 0.8s forwards;
        }
        @keyframes fadeIn {
            to { opacity: 1; }
        }
        /* Responsive chart container */
        #chartContainer {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <h1>Smart PHP Log Analyzer Dashboard</h1>
    <div id="log">Loading logs...</div>

    <div id="chartContainer">
        <canvas id="logChart"></canvas>
    </div>

    <!-- Include Chart.js from CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Create WebSocket connection to your server (ensure run-server.php is running)
        var ws = new WebSocket("ws://localhost:8080");

        // Chart.js setup
        var ctx = document.getElementById('logChart').getContext('2d');
        var chartData = {
            labels: ['INFO', 'WARNING', 'ERROR'],
            datasets: [{
                label: 'Log Count',
                data: [0, 0, 0],
                backgroundColor: ['#4caf50', '#ff9800', '#f44336']
            }]
        };
        var logChart = new Chart(ctx, {
            type: 'bar',
            data: chartData,
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        // Update the chart data based on incoming log entries
        function updateChart(logData) {
            if (logData.includes('INFO')) { chartData.datasets[0].data[0]++; }
            if (logData.includes('WARNING')) { chartData.datasets[0].data[1]++; }
            if (logData.includes('ERROR')) { chartData.datasets[0].data[2]++; }
            logChart.update();
        }

        // When a message is received from the WebSocket, display it and update the chart
        ws.onmessage = function(event) {
            var logDiv = document.getElementById("log");
            var entry = document.createElement("div");
            entry.className = "log-entry";
            entry.textContent = event.data;
            logDiv.appendChild(entry);
            logDiv.scrollTop = logDiv.scrollHeight;
            updateChart(event.data);
        };

        ws.onerror = function(error) {
            console.error("WebSocket error:", error);
        };
    </script>
</body>
</html>
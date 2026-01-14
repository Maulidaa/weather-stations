// Dark Mode Toggle
function toggleDarkMode() {
    document.body.classList.toggle('dark-mode');
    const isDarkMode = document.body.classList.contains('dark-mode');
    localStorage.setItem('darkMode', isDarkMode);
    updateToggleButton();
}

function updateToggleButton() {
    const button = document.querySelector('.dark-mode-toggle');
    const isDarkMode = document.body.classList.contains('dark-mode');
    button.textContent = isDarkMode ? '☀️ Light Mode' : '🌙 Dark Mode';
}

// Tab Switching Function
function switchTab(tabName) {
    // Hide all tab contents
    const contents = document.querySelectorAll('.tab-content');
    contents.forEach(content => {
        content.classList.remove('active');
    });

    // Remove active class from all buttons
    const buttons = document.querySelectorAll('.tab-btn');
    buttons.forEach(btn => {
        btn.classList.remove('active');
    });

    // Show selected tab content
    const selectedTab = document.getElementById(tabName);
    if (selectedTab) {
        selectedTab.classList.add('active');
    }

    // Add active class to clicked button
    event.target.classList.add('active');

    // Save active tab to localStorage
    localStorage.setItem('activeTab', tabName);
}

// Load dark mode preference from localStorage on page load
window.addEventListener('load', function() {
    const darkMode = localStorage.getItem('darkMode') === 'true';
    if (darkMode) {
        document.body.classList.add('dark-mode');
    }
    updateToggleButton();
});

// Load active tab from localStorage on page load
window.addEventListener('load', function() {
    const activeTab = localStorage.getItem('activeTab') || 'overview';
    const tabBtn = document.querySelector(`[onclick="switchTab('${activeTab}')"]`);
    if (tabBtn && document.getElementById(activeTab)) {
        tabBtn.click();
    }
});

// Initialize charts after data is loaded
function initializeCharts() {
    const hourlyData = window.hourlyData || [];
    
    if (hourlyData.length === 0) return;
    
    const labels = hourlyData.map(d => d.hour.substr(5, 11)).reverse();
    const tempData = hourlyData.map(d => parseFloat(d.avg_temp)).reverse();
    const humidityData = hourlyData.map(d => parseFloat(d.avg_humidity)).reverse();
    const pressureData = hourlyData.map(d => (parseFloat(d.avg_pressure) / 1000).toFixed(2)).reverse();

    // Temperature Chart
    new Chart(document.getElementById('temperatureChart'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Suhu (°C)',
                data: tempData,
                borderColor: '#FF6B6B',
                backgroundColor: 'rgba(255, 107, 107, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointBackgroundColor: '#FF6B6B'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: false }
            }
        }
    });

    // Humidity Chart
    new Chart(document.getElementById('humidityChart'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Kelembaban (%)',
                data: humidityData,
                borderColor: '#4ECDC4',
                backgroundColor: 'rgba(78, 205, 196, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointBackgroundColor: '#4ECDC4'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { min: 0, max: 100 }
            }
        }
    });

    // Pressure Chart
    new Chart(document.getElementById('pressureChart'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Tekanan (kPa)',
                data: pressureData,
                borderColor: '#45B7D1',
                backgroundColor: 'rgba(69, 183, 209, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointBackgroundColor: '#45B7D1'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: false }
            }
        }
    });
}

// Auto refresh setiap 60 detik
setInterval(() => {
    location.reload();
}, 60000);

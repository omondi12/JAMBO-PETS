// Wait for the DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize the charts
    initializeCharts();
});

function initializeCharts() {
    // Set new default font family and font color to mimic Bootstrap's default styling
    Chart.defaults.font.family = 'Nunito, -apple-system, system-ui, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif';
    Chart.defaults.color = '#858796';
    
    // Initialize Area Chart - Monthly Sales Overview
    const salesChartCanvas = document.getElementById('myAreaChart');
    if (salesChartCanvas) {
        initializeSalesChart(salesChartCanvas);
    }
    
    // Initialize Pie Chart - User Distribution by County
    const countyChartCanvas = document.getElementById('myPieChart');
    if (countyChartCanvas) {
        initializeCountyChart(countyChartCanvas);
    }
}

function initializeSalesChart(canvas) {
    // Get the labels and data from PHP
    // Note: These should be defined in your PHP code and passed to JavaScript
    const labels = JSON.parse(canvas.getAttribute('data-labels') || '[]');
    const salesData = JSON.parse(canvas.getAttribute('data-sales') || '[]');
    
    // Create the chart
    new Chart(canvas, {
        type: 'line',
        data: {
            labels: labels.length > 0 ? labels : ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: "Revenue",
                lineTension: 0.3,
                backgroundColor: "rgba(78, 115, 223, 0.05)",
                borderColor: "rgba(78, 115, 223, 1)",
                pointRadius: 3,
                pointBackgroundColor: "rgba(78, 115, 223, 1)",
                pointBorderColor: "rgba(78, 115, 223, 1)",
                pointHoverRadius: 3,
                pointHoverBackgroundColor: "rgba(78, 115, 223, 1)",
                pointHoverBorderColor: "rgba(78, 115, 223, 1)",
                pointHitRadius: 10,
                pointBorderWidth: 2,
                data: salesData.length > 0 ? salesData : [0, 10000, 5000, 15000, 10000, 20000],
            }],
        },
        options: {
            maintainAspectRatio: false,
            layout: {
                padding: {
                    left: 10,
                    right: 25,
                    top: 25,
                    bottom: 0
                }
            },
            scales: {
                x: {
                    time: {
                        unit: 'date'
                    },
                    grid: {
                        display: false,
                        drawBorder: false
                    },
                    ticks: {
                        maxTicksLimit: 7
                    }
                },
                y: {
                    ticks: {
                        maxTicksLimit: 5,
                        padding: 10,
                        // Include currency sign in the ticks
                        callback: function(value) {
                            return 'KSh ' + numberFormat(value);
                        }
                    },
                    grid: {
                        color: "rgb(234, 236, 244)",
                        zeroLineColor: "rgb(234, 236, 244)",
                        drawBorder: false,
                        borderDash: [2],
                        zeroLineBorderDash: [2]
                    }
                },
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: "rgb(255,255,255)",
                    bodyColor: "#858796",
                    titleMarginBottom: 10,
                    titleColor: '#6e707e',
                    titleFont: {
                        size: 14
                    },
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                    padding: {
                        x: 15,
                        y: 15
                    },
                    displayColors: false,
                    intersect: false,
                    mode: 'index',
                    caretPadding: 10,
                    callbacks: {
                        label: function(context) {
                            const label = context.dataset.label || '';
                            return label + ': KSh ' + numberFormat(context.parsed.y);
                        }
                    }
                }
            }
        }
    });
}

function initializeCountyChart(canvas) {
    // Create the chart
    new Chart(canvas, {
        type: 'doughnut',
        data: {
            labels: ["Nairobi", "Mombasa", "Other Counties"],
            datasets: [{
                data: [55, 30, 15],
                backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc'],
                hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf'],
                hoverBorderColor: "rgba(234, 236, 244, 1)",
            }],
        },
        options: {
            maintainAspectRatio: false,
            plugins: {
                tooltip: {
                    backgroundColor: "rgb(255,255,255)",
                    bodyColor: "#858796",
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                    padding: {
                        x: 15,
                        y: 15
                    },
                    displayColors: false,
                    caretPadding: 10,
                },
                legend: {
                    display: false
                }
            },
            cutout: '80%',
        },
    });
}

// Format number to have commas for thousands
function numberFormat(number, decimals, decPoint, thousandsSep) {
    decimals = decimals !== undefined ? decimals : 2;
    decPoint = decPoint || '.';
    thousandsSep = thousandsSep || ',';
    
    number = parseFloat(number);
    if (!isFinite(number) || (!number && number !== 0)) return '';
    
    const absNumber = Math.abs(number);
    const stringNumber = String(parseInt(number.toFixed(decimals)));
    const numDigits = stringNumber.length > 1 ? stringNumber.length : 1;
    
    const result = [];
    for (let i = 0; i < numDigits; i++) {
        const digit = stringNumber[numDigits - 1 - i] || '0';
        if (i > 0 && i % 3 === 0) {
            result.unshift(thousandsSep);
        }
        result.unshift(digit);
    }
    
    if (decimals > 0) {
        let fractional = absNumber.toFixed(decimals).slice(-decimals);
        if (fractional.length > 0) {
            result.push(decPoint);
            result.push(fractional);
        }
    }
    
    if (number < 0) {
        result.unshift('-');
    }
    
    return result.join('');
}
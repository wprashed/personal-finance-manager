jQuery(document).ready(function($) {
    var ctx = document.getElementById('pft-monthly-chart').getContext('2d');
    var chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: [],
            datasets: [{
                label: 'Income',
                backgroundColor: 'rgba(75, 192, 192, 0.6)',
                data: []
            }, {
                label: 'Expenses',
                backgroundColor: 'rgba(255, 99, 132, 0.6)',
                data: []
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value, index, values) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            },
            tooltips: {
                callbacks: {
                    label: function(tooltipItem, data) {
                        var label = data.datasets[tooltipItem.datasetIndex].label || '';
                        if (label) {
                            label += ': ';
                        }
                        label += '$' + tooltipItem.yLabel.toLocaleString();
                        return label;
                    }
                }
            }
        }
    });

    function updateChart() {
        $.ajax({
            url: pftAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'pft_get_monthly_data',
                nonce: pftAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    chart.data.labels = response.data.labels;
                    chart.data.datasets[0].data = response.data.income;
                    chart.data.datasets[1].data = response.data.expenses;
                    chart.update();
                } else {
                    console.error('Error fetching chart data:', response.data.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', status, error);
            }
        });
    }

    function updateMonthlyDetails() {
        $.ajax({
            url: pftAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'pft_get_monthly_details',
                nonce: pftAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#pft-monthly-details-content').html(response.data);
                } else {
                    console.error('Error fetching monthly details:', response.data.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', status, error);
            }
        });
    }

    // Initialize the dashboard
    updateChart();
    updateMonthlyDetails();

    // Refresh data every 5 minutes
    setInterval(function() {
        updateChart();
        updateMonthlyDetails();
    }, 300000);

    // Add event delegation for dynamically added elements
    $(document).on('click', '.pft-view-details', function(e) {
        e.preventDefault();
        var monthYear = $(this).data('month-year');
        loadMonthDetails(monthYear);
    });

    function loadMonthDetails(monthYear) {
        $.ajax({
            url: pftAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'pft_get_specific_month_details',
                nonce: pftAjax.nonce,
                month_year: monthYear
            },
            success: function(response) {
                if (response.success) {
                    $('#pft-monthly-details-content').html(response.data);
                } else {
                    console.error('Error fetching specific month details:', response.data.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', status, error);
            }
        });
    }
});
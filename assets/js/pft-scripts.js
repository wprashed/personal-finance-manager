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
                    beginAtZero: true
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
            }
        });
    }

    function updateTransactionsList() {
        $.ajax({
            url: pftAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'pft_get_recent_transactions',
                nonce: pftAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#pft-transactions').html(response.data);
                } else {
                    console.error('Error fetching transactions:', response.data.message);
                }
            }
        });
    }

    function updateSummary() {
        $.ajax({
            url: pftAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'pft_get_summary',
                nonce: pftAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#pft-total-income').text('Total Income: ' + response.data.income);
                    $('#pft-total-expenses').text('Total Expenses: ' + response.data.expenses);
                    $('#pft-balance').text('Balance: ' + response.data.balance);
                } else {
                    console.error('Error fetching summary:', response.data.message);
                }
            }
        });
    }

    $('#pft-transaction-form').submit(function(e) {
        e.preventDefault();
        $.ajax({
            url: pftAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'pft_add_transaction',
                nonce: pftAjax.nonce,
                description: $('#pft-description').val(),
                amount: $('#pft-amount').val(),
                type: $('#pft-type').val(),
                date: $('#pft-date').val()
            },
            success: function(response) {
                if (response.success) {
                    $('#pft-transaction-form')[0].reset();
                    updateChart();
                    updateTransactionsList();
                    updateSummary();
                } else {
                    alert('Error adding transaction: ' + response.data.message);
                }
            }
        });
    });

    // Initialize the dashboard
    updateChart();
    updateTransactionsList();
    updateSummary();

    // Refresh data every 5 minutes
    setInterval(function() {
        updateChart();
        updateTransactionsList();
        updateSummary();
    }, 300000);
});
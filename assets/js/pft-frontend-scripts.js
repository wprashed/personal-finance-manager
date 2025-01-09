jQuery(document).ready(function($) {
    // Existing chart code (unchanged)
    var ctx = document.getElementById('pftMonthlyChart');
    if (ctx) {
        var totalIncome = parseFloat($('#pft-total-income').text().replace(/[^0-9.-]+/g,""));
        var totalExpenses = parseFloat($('#pft-total-expenses').text().replace(/[^0-9.-]+/g,""));

        var monthlyChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Income', 'Expenses'],
                datasets: [{
                    label: 'Amount',
                    data: [totalIncome, totalExpenses],
                    backgroundColor: [
                        'rgba(16, 185, 129, 0.7)',
                        'rgba(239, 68, 68, 0.7)'
                    ],
                    borderColor: [
                        'rgb(16, 185, 129)',
                        'rgb(239, 68, 68)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': $' + context.raw.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }

    // New code for archive filtering
    $('#pft-filter-form').on('submit', function(e) {
        e.preventDefault();
        var year = $('#pft-year-filter').val();
        var month = $('#pft-month-filter').val();

        $.ajax({
            url: pftAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'pft_filter_archive',
                nonce: pftAjax.nonce,
                year: year,
                month: month
            },
            success: function(response) {
                if (response.success) {
                    $('#pft-archive-list').html(response.data);
                } else {
                    console.error('Error filtering archive:', response.data);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', error);
            }
        });
    });
});
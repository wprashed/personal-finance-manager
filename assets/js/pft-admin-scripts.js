jQuery(document).ready(function($) {
    // Initialize mini chart if it exists
    var miniChartCanvas = document.getElementById('pftMiniChart');
    if (miniChartCanvas) {
        var ctx = miniChartCanvas.getContext('2d');
        var miniChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Income',
                    borderColor: 'rgba(16, 185, 129, 1)',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    data: [],
                    fill: true
                }, {
                    label: 'Expenses',
                    borderColor: 'rgba(239, 68, 68, 1)',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    data: [],
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
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
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        });

        // Fetch data for the mini chart
        $.ajax({
            url: pftAdmin.ajaxurl,
            type: 'POST',
            data: {
                action: 'pft_get_chart_data',
                nonce: pftAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    miniChart.data.labels = response.data.labels;
                    miniChart.data.datasets[0].data = response.data.income;
                    miniChart.data.datasets[1].data = response.data.expenses;
                    miniChart.update();
                }
            }
        });
    }

    // Initialize tooltips if any
    $('[data-tooltip]').tooltip();

    // Handle dynamic form fields in the monthly finance editor
    var incomeContainer = $('#pft-income-entries');
    var expenseContainer = $('#pft-expense-entries');

    $('#pft-add-income').on('click', function() {
        addEntryRow(incomeContainer, 'income');
    });

    $('#pft-add-expense').on('click', function() {
        addEntryRow(expenseContainer, 'expense');
    });

    function addEntryRow(container, type) {
        var index = container.children().length;
        var template = `
            <div class="pft-entry-row">
                <select name="pft_${type}[${index}][type]" required>
                    <option value="">Select Type</option>
                    ${getTransactionTypeOptions()}
                </select>
                <input type="text" name="pft_${type}[${index}][description]" placeholder="Description" required>
                <input type="number" name="pft_${type}[${index}][amount]" placeholder="Amount" step="0.01" required>
                <button type="button" class="pft-remove-button">Remove</button>
            </div>
        `;
        container.append(template);
    }

    function getTransactionTypeOptions() {
        // This should be populated with actual transaction types from the server
        return $('#pft-transaction-types-template').html();
    }

    // Handle remove button clicks
    $(document).on('click', '.pft-remove-button', function() {
        $(this).closest('.pft-entry-row').remove();
    });
});
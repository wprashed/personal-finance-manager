jQuery(document).ready(function($) {
    // Initialize the monthly chart
    var ctx = document.getElementById('pftMonthlyChart').getContext('2d');
    var monthlyChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: [],
            datasets: [{
                label: 'Income',
                backgroundColor: 'rgba(16, 185, 129, 0.7)',
                borderColor: 'rgb(16, 185, 129)',
                borderWidth: 1,
                data: []
            }, {
                label: 'Expenses',
                backgroundColor: 'rgba(239, 68, 68, 0.7)',
                borderColor: 'rgb(239, 68, 68)',
                borderWidth: 1,
                data: []
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

    // Handle adding new income entries
    $('#pft-add-income').on('click', function() {
        var container = $('#pft-income-entries');
        addTransactionRow(container, 'income');
    });

    // Handle adding new expense entries
    $('#pft-add-expense').on('click', function() {
        var container = $('#pft-expense-entries');
        addTransactionRow(container, 'expense');
    });

    // Handle removing entries
    $(document).on('click', '.pft-remove-button', function() {
        $(this).closest('.pft-transaction-row').slideUp(200, function() {
            $(this).remove();
            updateTotals();
        });
    });

    // Handle amount changes
    $(document).on('input', 'input[name*="[amount]"]', function() {
        updateTotals();
    });

    function addTransactionRow(container, type) {
        var index = container.children().length;
        var template = `
            <div class="pft-transaction-row" style="display: none;">
                <select name="pft_${type}[${index}][type]" required>
                    <option value="">Select Type</option>
                    ${$('#pft-transaction-types-template').html()}
                </select>
                <input type="text" 
                       name="pft_${type}[${index}][description]" 
                       placeholder="Description" 
                       required>
                <input type="number" 
                       name="pft_${type}[${index}][amount]" 
                       placeholder="Amount" 
                       step="0.01" 
                       required>
                <button type="button" class="pft-remove-button">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 6L6 18M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        `;
        var $row = $(template);
        container.append($row);
        $row.slideDown(200);
    }

    function updateTotals() {
        var totalIncome = 0;
        var totalExpenses = 0;

        $('#pft-income-entries input[name*="[amount]"]').each(function() {
            var amount = parseFloat($(this).val()) || 0;
            if (!isNaN(amount)) {
                totalIncome += amount;
            }
        });

        $('#pft-expense-entries input[name*="[amount]"]').each(function() {
            var amount = parseFloat($(this).val()) || 0;
            if (!isNaN(amount)) {
                totalExpenses += amount;
            }
        });

        $('.pft-summary-card.income .amount').text('$' + totalIncome.toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }));

        $('.pft-summary-card.expense .amount').text('$' + totalExpenses.toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }));

        var balance = totalIncome - totalExpenses;
        $('.pft-summary-card.balance .amount').text('$' + balance.toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }));

        // Update chart data
        monthlyChart.data.datasets[0].data = [totalIncome];
        monthlyChart.data.datasets[1].data = [totalExpenses];
        monthlyChart.update();
    }

    // Add event listeners for real-time updates
    $(document).on('input', 'input[name*="[amount]"]', updateTotals);
    $(document).on('change', 'select[name*="[type]"]', updateTotals);

    // Initialize tooltips
    $('[data-tooltip]').tooltip();

    // Fetch and update chart data
    function updateChart() {
        $.ajax({
            url: pftEditor.ajaxurl,
            type: 'POST',
            data: {
                action: 'pft_get_monthly_chart_data',
                nonce: pftEditor.nonce,
                post_id: pftEditor.post_id
            },
            success: function(response) {
                if (response.success) {
                    monthlyChart.data.labels = response.data.labels;
                    monthlyChart.data.datasets[0].data = response.data.income;
                    monthlyChart.data.datasets[1].data = response.data.expenses;
                    monthlyChart.update();
                }
            }
        });
    }

    // Initial chart update
    updateChart();
    updateTotals(); // Initial update
});


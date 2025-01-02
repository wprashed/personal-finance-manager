jQuery(document).ready(function($) {
    var ctx = document.getElementById('pftMonthlyChart').getContext('2d');
    var monthlyChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Income', 'Expenses'],
            datasets: [{
                label: 'Amount',
                data: [0, 0],
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

    function updateChart() {
        var totalIncome = 0;
        var totalExpenses = 0;

        $('#pft-income-entries input[name*="[amount]"]').each(function() {
            totalIncome += parseFloat($(this).val()) || 0;
        });

        $('#pft-expense-entries input[name*="[amount]"]').each(function() {
            totalExpenses += parseFloat($(this).val()) || 0;
        });

        monthlyChart.data.datasets[0].data = [totalIncome, totalExpenses];
        monthlyChart.update();
    }

    $('#pft-add-income').on('click', function() {
        addTransactionRow($('#pft-income-entries'), 'income');
    });

    $('#pft-add-expense').on('click', function() {
        addTransactionRow($('#pft-expense-entries'), 'expense');
    });

    $(document).on('click', '.pft-remove-button', function() {
        $(this).closest('.pft-transaction-row').slideUp(200, function() {
            $(this).remove();
            updateTotals();
            updateChart();
        });
    });

    $(document).on('input', 'input[name*="[amount]"]', function() {
        updateTotals();
        updateChart();
    });

    function addTransactionRow(container, type) {
        var index = container.children().length;
        var template = `
            <div class="pft-transaction-row" style="display: none;">
                <select name="pft_${type}[${index}][type]" required>
                    <option value="">Select Category</option>
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
    }

    // Initial updates
    updateTotals();
    updateChart();
});
jQuery(document).ready(function($) {
    if ($('#pft-finance-tracker').length) {
        var ctx = document.getElementById('pft-finance-chart').getContext('2d');
        var financeChart;

        function initChart(data) {
            financeChart = new Chart(ctx, {
                type: $('#pft-finance-tracker').data('chart-type') || 'bar',
                data: {
                    labels: data.map(item => item.date),
                    datasets: [
                        {
                            label: 'Income',
                            data: data.map(item => item.income),
                            backgroundColor: 'rgba(16, 185, 129, 0.7)',
                            borderColor: 'rgb(16, 185, 129)',
                            borderWidth: 1
                        },
                        {
                            label: 'Expenses',
                            data: data.map(item => item.expenses),
                            backgroundColor: 'rgba(239, 68, 68, 0.7)',
                            borderColor: 'rgb(239, 68, 68)',
                            borderWidth: 1
                        },
                        {
                            label: 'Savings',
                            data: data.map(item => item.savings),
                            backgroundColor: 'rgba(59, 130, 246, 0.7)',
                            borderColor: 'rgb(59, 130, 246)',
                            borderWidth: 1
                        }
                    ]
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

        function updateFinancialData() {
            $.ajax({
                url: pftAjax.ajaxurl,
                type: 'GET',
                data: {
                    action: 'pft_get_financial_data',
                    nonce: pftAjax.nonce,
                    months: $('#pft-finance-tracker').data('months') || 6
                },
                success: function(response) {
                    if (response.success) {
                        updateChart(response.data);
                        updateSummary(response.data);
                        updateMonthlyDetails(response.data);
                    } else {
                        console.error('Error fetching financial data:', response.data);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', error);
                }
            });
        }

        function updateChart(data) {
            if (financeChart) {
                financeChart.destroy();
            }
            initChart(data);
        }

        function updateSummary(data) {
            var totalIncome = data.reduce((sum, item) => sum + item.income, 0);
            var totalExpenses = data.reduce((sum, item) => sum + item.expenses, 0);
            var totalSavings = totalIncome - totalExpenses;

            $('#pft-total-income').text('$' + totalIncome.toLocaleString());
            $('#pft-total-expenses').text('$' + totalExpenses.toLocaleString());
            $('#pft-balance').text('$' + totalSavings.toLocaleString());
        }

        function updateMonthlyDetails(data) {
            var detailsHtml = '<table class="pft-monthly-details-table">';
            detailsHtml += '<thead><tr><th>Date</th><th>Income</th><th>Expenses</th><th>Savings</th></tr></thead><tbody>';

            data.forEach(function(item) {
                detailsHtml += `<tr>
                    <td>${item.date}</td>
                    <td>$${item.income.toLocaleString()}</td>
                    <td>$${item.expenses.toLocaleString()}</td>
                    <td>$${item.savings.toLocaleString()}</td>
                </tr>`;
            });

            detailsHtml += '</tbody></table>';
            $('#pft-monthly-details-content').html(detailsHtml);
        }

        updateFinancialData();
    }
});
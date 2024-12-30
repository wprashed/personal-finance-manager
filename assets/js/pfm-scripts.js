jQuery(document).ready(function($) {
    // Quick Entry Form Submission
    $('#pfm-quick-entry-form').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();

        $.ajax({
            url: pfmData.root + 'pfm/v1/entries',
            method: 'POST',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', pfmData.nonce);
            },
            data: formData
        }).done(function(response) {
            alert('Entry added successfully!');
            $('#pfm-quick-entry-form')[0].reset();
            updateDashboard();
        }).fail(function(response) {
            alert('Error adding entry. Please try again.');
        });
    });

    // Generate Report
    $('#pfm-generate-report').on('click', function() {
        var startDate = $('#pfm-start-date').val();
        var endDate = $('#pfm-end-date').val();

        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
                action: 'pfm_get_report_data',
                nonce: pfmData.nonce,
                start_date: startDate,
                end_date: endDate
            }
        }).done(function(response) {
            if (response.success) {
                renderCharts(response.data);
                renderSummary(response.data);
            } else {
                alert('Error generating report. Please try again.');
            }
        }).fail(function() {
            alert('Error generating report. Please try again.');
        });
    });

    function renderCharts(data) {
        // Render income chart
        new Chart($('#pfm-income-chart'), {
            type: 'pie',
            data: {
                labels: Object.keys(data.income),
                datasets: [{
                    data: Object.values(data.income),
                    backgroundColor: generateColors(Object.keys(data.income).length)
                }]
            },
            options: {
                title: {
                    display: true,
                    text: 'Income by Category'
                }
            }
        });

        // Render expense chart
        new Chart($('#pfm-expense-chart'), {
            type: 'pie',
            data: {
                labels: Object.keys(data.expense),
                datasets: [{
                    data: Object.values(data.expense),
                    backgroundColor: generateColors(Object.keys(data.expense).length)
                }]
            },
            options: {
                title: {
                    display: true,
                    text: 'Expenses by Category'
                }
            }
        });
    }

    function renderSummary(data) {
        var summaryHtml = '<h3>Summary</h3>';
        summaryHtml += '<p>Total Income: $' + data.total_income.toFixed(2) + '</p>';
        summaryHtml += '<p>Total Expenses: $' + data.total_expense.toFixed(2) + '</p>';
        summaryHtml += '<p>Net Balance: $' + (data.total_income - data.total_expense).toFixed(2) + '</p>';

        $('#pfm-summary').html(summaryHtml);
    }

    function generateColors(count) {
        var colors = [];
        for (var i = 0; i < count; i++) {
            colors.push('#' + Math.floor(Math.random()*16777215).toString(16));
        }
        return colors;
    }

    function updateDashboard() {
        // Implement dashboard update logic here
    }

    // Initial dashboard update
    updateDashboard();
});


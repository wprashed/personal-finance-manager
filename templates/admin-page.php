<div class="wrap">
    <h1>Personal Finance Manager</h1>
    <div id="pfm-dashboard">
        <h2>Dashboard</h2>
        <div id="pfm-current-month-balance"></div>
        <div id="pfm-quick-entry">
            <h3>Quick Entry</h3>
            <form id="pfm-quick-entry-form">
                <input type="text" name="title" placeholder="Title" required>
                <input type="number" name="amount" placeholder="Amount" step="0.01" required>
                <select name="type" required>
                    <option value="income">Income</option>
                    <option value="expense">Expense</option>
                </select>
                <input type="date" name="date" required>
                <select name="category" required>
                    <?php
                    $categories = get_terms(array(
                        'taxonomy' => 'pfm_category',
                        'hide_empty' => false,
                    ));
                    foreach ($categories as $category) {
                        echo '<option value="' . esc_attr($category->name) . '">' . esc_html($category->name) . '</option>';
                    }
                    ?>
                </select>
                <button type="submit">Add Entry</button>
            </form>
        </div>
    </div>
</div>


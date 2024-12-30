<div class="wrap">
    <h1>Finance Manager Settings</h1>
    <form method="post" action="options.php">
        <?php
        settings_fields('pfm_options');
        do_settings_sections('pfm_settings');
        submit_button();
        ?>
    </form>
</div>


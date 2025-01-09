=== Money Metrics ===
Contributors: wprashed
Tags: finance, money, budget, expense tracker, income tracker, financial management
Requires at least: 5.8
Tested up to: 6.4
Stable tag: 1.0.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A comprehensive personal finance tracking solution with beautiful reports, income and expense categorization, and detailed monthly summaries.

== Description ==

Personal Finance Tracker is a powerful WordPress plugin that helps you manage and track your personal finances with ease. Whether you're tracking your monthly income, categorizing expenses, or analyzing spending patterns, this plugin provides all the tools you need for effective financial management.

= Key Features =

* **Monthly Finance Tracking**: Record and manage your income and expenses on a monthly basis
* **Category Management**: Organize your finances with customizable income and expense categories
* **Beautiful Reports**: Visual representation of your financial data with interactive charts
* **Real-time Updates**: See your financial summary update instantly as you enter data
* **Filtering Options**: Filter and analyze your financial data by category and date
* **Responsive Design**: Works perfectly on all devices - desktop, tablet, and mobile
* **User-friendly Interface**: Clean and intuitive interface for easy data entry and management

= Perfect For =

* Personal finance management
* Small business expense tracking
* Freelancer income and expense monitoring
* Budget planning and analysis
* Financial goal setting and tracking

= Pro Features =

* Export reports to PDF/Excel
* Multiple currency support
* Advanced filtering options
* Custom category colors
* Budget planning tools
* Financial goal tracking
* Email notifications and reminders

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/personal-finance-tracker` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Go to 'Personal Finances' in your WordPress admin menu to start tracking your finances
4. Set up your income and expense categories under 'Personal Finances > Categories'

= Minimum Requirements =

* WordPress 5.8 or greater
* PHP version 7.4 or greater
* MySQL version 5.6 or greater

== Frequently Asked Questions ==

= How do I add a new monthly finance entry? =

1. Go to Personal Finances in your WordPress admin menu
2. Click "Add New"
3. Enter your income and expense entries for the month
4. Click Publish to save your entries

= Can I categorize my income and expenses? =

Yes! The plugin comes with pre-defined categories for both income and expenses. You can also add your own custom categories through the Personal Finances > Categories menu.

= How do I generate reports? =

Reports are automatically generated based on your entered data. You can view them by:
1. Going to any monthly finance entry
2. Using the filter options to select specific categories or date ranges
3. The charts and summaries will update automatically

= Can I export my financial data? =

The free version allows you to view all your data within WordPress. For export capabilities to PDF or Excel, please consider upgrading to our Pro version.

= Is my financial data secure? =

Yes! All your financial data is stored securely in your WordPress database. The plugin doesn't send any of your financial data to external servers.

== Screenshots ==

1. Monthly finance entry screen
2. Financial reports and charts
3. Category management
4. Filter and search interface
5. Mobile responsive design

== Changelog ==

= 1.0.0 =
* Initial release
* Monthly finance tracking
* Income and expense categories
* Visual reports and charts
* Filtering system
* Responsive design implementation

== Upgrade Notice ==

= 1.0.0 =
Initial release of Personal Finance Tracker. Includes all core features for personal finance management.

== Privacy Policy ==

Personal Finance Tracker stores all financial data locally in your WordPress database. We do not collect or store any personal or financial information on external servers.

The plugin does not:
* Share any data with external services
* Track user behavior
* Store personal information beyond what you enter
* Connect to external APIs

For more information, please see our full privacy policy at: [Your Privacy Policy URL]

== Additional Information ==

= Contributing =

If you want to contribute to the development of this plugin, visit our GitHub repository: [Your GitHub Repository URL]

= Support =

For support queries:
* Visit our documentation at [Your Documentation URL]
* Create a support ticket at [Your Support URL]
* Email us at [Your Support Email]

= Credits =

Personal Finance Tracker uses the following open-source libraries:
* Chart.js for data visualization
* WordPress REST API
* jQuery for enhanced functionality

== Custom Hooks and Filters ==

Developers can extend the plugin's functionality using these hooks:

= Actions =
* `pft_after_save_finance` - Triggered after saving finance data
* `pft_before_delete_finance` - Triggered before deleting finance data
* `pft_after_category_update` - Triggered after updating categories

= Filters =
* `pft_income_categories` - Modify income categories
* `pft_expense_categories` - Modify expense categories
* `pft_chart_colors` - Customize chart colors
* `pft_currency_format` - Customize currency format
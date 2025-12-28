=== Free Form Certificate ===
Contributors: alexmeusburger
Tags: certificate, form builder, pdf generation, verification, validation, html2canvas, jspdf
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 2.7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Complete solution for creating dynamic forms, generating PDF certificates, and validating authenticity.

== Description ==

Free Form Certificate is a robust WordPress solution for creating dynamic forms and automated certificate issuance. The plugin allows administrators to create custom fields via drag & drop, validate submissions in real-time, and offer users a PDF certificate generated directly in the browser, ensuring high performance without overloading the server.

= Key Features =

* **Drag & Drop Form Builder:** Intuitive interface to create custom fields (Text, Email, Number, Date, Select, Radio, Textarea, Hidden).
* **Client-Side PDF Generation:** Uses html2canvas and jsPDF to generate A4 landscape certificates with support for custom background images.
* **Verification System:** Validation shortcode `[ffc_verification]` for certificate authenticity via unique code.
* **ID-Based Restriction (CPF/RF):** Control unique issuance or "reprint" mode based on document.
* **Ticket System:** Import list of exclusive codes for form access.
* **Advanced Security:** Bot protection with integrated Math Captcha and Honeypot.
* **Data Export:** CSV export tool with filters by form and date.
* **Asynchronous Notifications:** Email sending to administrator via WP-Cron to not block user flow.
* **Automatic Cleanup:** Daily routine to delete old records according to configuration.

== Installation ==

1. Upload the `wp-ffcertificate` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Access the 'Free Form Certificate' menu to create your first form.
4. Use the shortcode `[ffc_form id="FORM_ID"]` on any page or post.

== Frequently Asked Questions ==

= How do I create a form? =

1. Navigate to "Free Form Certificate" > "Add New Form"
2. Enter a title for your form
3. Use the Form Builder to add fields
4. Configure the certificate layout in the "Certificate Layout" section
5. Save and copy the shortcode

= How do I enable the verification page? =

The plugin automatically creates a `/valid` page during activation. You can also manually create a page with the shortcode `[ffc_verification]`.

= Can I restrict who can generate certificates? =

Yes! In the "Restriction & Security" section of each form, you can:
- Enable whitelist mode (only listed IDs can generate)
- Use ticket system (unique codes)
- Block specific IDs via denylist

= How do I translate the plugin? =

The plugin is fully translation-ready. Use tools like Poedit or Loco Translate to create translations from the `languages/ffc.pot` file.

== Screenshots ==

1. Form Builder with drag & drop interface
2. Certificate layout editor with live preview
3. Submissions management with PDF download
4. Security settings (whitelist, tickets, denylist)
5. Frontend certificate generation

== Changelog ==

= 2.7.0 (2024-12-28) - Major Reorganization =
* **Code Architecture:** Complete plugin reorganization with modular OOP structure
* **Classes Separation:** 
  - Simplified `class-ffc-cpt.php` (only CPT registration and duplication)
  - Consolidated all metaboxes in `class-ffc-form-editor.php`
  - Added missing methods: `update_submission()` and `delete_all_submissions()`
* **Internationalization (i18n):** Full implementation of translation support
  - All PHP strings wrapped in `__()` and `_e()` functions
  - JavaScript strings localized via `wp_localize_script`
  - Created `.pot` file for translators
* **CSS Consolidation:** Removed all inline styles
  - Moved to `admin.css` and `frontend.css`
  - Better organization and maintainability
* **Code Quality:**
  - Removed dead code
  - Eliminated redundancies
  - All comments in English
  - Cross-file dependency validation
* **Bug Fixes:**
  - Fixed missing method calls
  - Corrected duplicate metabox registration
  - Fixed SMTP settings toggle visibility

= 2.0.0 (2024-12-22) =
* **PDF Refactoring:** Migration from simple image to high-fidelity PDF (A4 Landscape) using jsPDF
* **Mobile Optimization:** Strategic delays and progress overlay for correct rendering on mobile devices
* **Security:** Implementation of dynamic Math Captcha with hash validation on backend
* **Reprint Logic:** New duplicate detection logic allowing certificate recovery
* **Admin Improvements:** PDF download buttons directly in submissions list
* **CORS Fix:** Added `crossorigin="anonymous"` attribute in image rendering

= 1.5.0 (2024-12-20)=
* Ticket system implementation (single-use codes)
* Form cloning functionality
* Global settings tab with automatic log cleanup

= 1.0.0 (2024-12-13)=
* Initial release with basic Form Builder and CSV export

== Layout & Placeholders ==

In the certificate layout editor, you can use the following dynamic tags:

= System Tags =
* `{{auth_code}}`: 12-digit authentication code
* `{{form_title}}`: Current form title
* `{{submission_date}}`: Issuance date (formatted according to WP settings)
* `{{submission_id}}`: Numeric ID of the submission in database
* `{{validation_url}}`: URL of the verification page

= Form Field Tags =
* `{{field_name}}`: Any field name defined in Form Builder
* Common examples: `{{name}}`, `{{email}}`, `{{cpf_rf}}`, `{{ticket}}`

= Formatting Notes =
* CPF/RF fields are automatically formatted with dots and dashes
* Auth codes are formatted as XXXX-XXXX-XXXX
* Date fields respect WordPress date format settings

== Shortcodes ==

= [ffc_form] =
Displays the certificate issuance form.

Parameters:
* `id` (required): Form ID

Example: `[ffc_form id="123"]`

= [ffc_verification] =
Displays the certificate verification interface.

No parameters required.

Example: `[ffc_verification]`

== File Structure ==

wp-ffcertificate/
├── wp-ffcertificate.php (Main plugin file)
├── readme.txt
├── includes/
│   ├── class-ffc-activator.php (Installation logic)
│   ├── class-ffc-admin.php (Admin interface)
│   ├── class-ffc-cpt.php (Custom Post Type - SIMPLIFIED)
│   ├── class-ffc-deactivator.php (Cleanup logic)
│   ├── class-ffc-form-editor.php (Metaboxes - CONSOLIDATED)
│   ├── class-ffc-frontend.php (Public interface)
│   ├── class-ffc-loader.php (Main orchestrator)
│   ├── class-ffc-settings.php (Plugin settings)
│   ├── class-ffc-submission-handler.php (Data processing - COMPLETE)
│   ├── class-ffc-submissions-list-table.php (Admin table)
│   └── class-ffc-utils.php (Shared utilities)
├── assets/
│   ├── css/
│   │   ├── ffc-pdf-core.css (PDF rendering - critical)
│   │   ├── admin.css (Admin styles - consolidated)
│   │   └── frontend.css (Public styles - consolidated)
│   └── js/
│       ├── html2canvas.min.js (v1.4.1)
│       ├── jspdf.umd.min.js (v2.5.1)
│       ├── admin.js (Admin logic - optimized)
│       └── frontend.js (Public logic - optimized)
├── html/ (Optional certificate templates)
└── languages/
    └── ffc.pot (Translation template)


== Submission Flow ==

1. **Frontend** sends AJAX (`ffc_submit_form`)
   - Nonce validation
   - Honeypot validation
   - Fields sanitized and validated

2. **Handler** processes submission
   - Saves to `wp_ffc_submissions` table
   - Schedules admin notification (WP-Cron)
   - Returns submission ID + template HTML

3. **Frontend** generates certificate
   - Receives JSON success
   - Replaces {{placeholders}} in template
   - Uses html2canvas to render HTML/CSS
   - Embeds in PDF via jsPDF
   - Triggers automatic download

4. **Reprint Detection**
   - Checks if CPF/RF already exists for this form
   - If YES: Retrieves original data (reprint mode)
   - If NO: Saves new data and generates auth code

== Security Features ==

= Whitelist Mode =
Restrict certificate issuance to a specific list of IDs/CPFs.
Configure in: Form Editor > Restriction & Security > Allowlist

= Ticket System =
Require a unique ticket code to generate certificate.
Tickets are automatically removed after use.
Configure in: Form Editor > Restriction & Security > Ticket Generator

= Denylist =
Block specific IDs or tickets from generating certificates.
Configure in: Form Editor > Restriction & Security > Denylist

= Math Captcha =
Built-in protection against bots on all forms.
Dynamically generated math questions with hash validation.

= Honeypot =
Hidden field to trap spam bots.
Automatically included in all forms.

== Data Management ==

= CSV Export =
- Available in "Submissions" screen
- Optional filtering by form
- Includes all submission metadata
- Translatable headers
- Semicolon delimiter (Excel compatible)

= Automatic Cleanup =
- Daily WP-Cron event (`ffc_daily_cleanup_hook`)
- Deletes submissions older than configured days
- Configure in: Settings > General > Auto-delete (days)
- Set to 0 to disable

= Danger Zone =
- Delete all submissions (with confirmation)
- Delete submissions from specific form
- Available in: Settings > General > Danger Zone

== Technical Notes ==

= Database Table =
The plugin creates a custom table: `wp_ffc_submissions`

Columns:
- `id`: Primary key
- `form_id`: Reference to form CPT
- `submission_date`: Timestamp
- `data`: JSON encoded submission data
- `user_ip`: User IP address
- `email`: User email (extracted for indexing)
- `status`: 'publish' or 'trash'

= WP-Cron Events =
- `ffc_process_submission_hook`: Asynchronous email sending
- `ffc_daily_cleanup_hook`: Old submissions cleanup

= PDF Generation =
- Client-side rendering (no server load)
- A4 Landscape (1123x794px)
- Background image support
- Custom HTML/CSS layouts
- Mobile optimized with strategic delays

== Support ==

For bug reports and feature requests, please contact the plugin author.
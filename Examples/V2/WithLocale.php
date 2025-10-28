<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use FormGenerator\V2\Builder\FormBuilder;
use FormGenerator\V2\Builder\DatePickerManager;
use FormGenerator\V2\Builder\TimePickerManager;
use FormGenerator\V2\Builder\DateTimePickerManager;
use FormGenerator\V2\Builder\RangeSliderManager;
use FormGenerator\V2\Renderer\TwigRenderer;
use FormGenerator\V2\Theme\Bootstrap5Theme;
use FormGenerator\V2\Contracts\{TextDirection, OutputFormat};

/**
 * FormGenerator V2 - Form-Level Locale Support Examples
 *
 * Demonstrates form-level locale support:
 * - Set locale once at form level
 * - Automatically applies to all pickers (date, time, datetime, range)
 * - No need to set locale for each picker individually
 * - Cleaner, more maintainable code
 */

// Initialize renderer and theme
$renderer = new TwigRenderer(__DIR__ . '/../../src/V2/Theme/templates');
$theme = new Bootstrap5Theme();

echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>\n";
echo "<div class='container my-5'>\n";

echo "<h1 class='mb-4'>Form-Level Locale Support</h1>";
echo "<p class='lead'>Set locale once at form level - automatically applies to all pickers!</p>";

// =============================================================================
// Example 1: English Form with Form-Level Locale
// =============================================================================
echo "<h2 class='mb-4'>Example 1: English Form (Form-Level Locale)</h2>";
echo "<div class='card mb-5'><div class='card-body'>";

$formEnglish = FormBuilder::create('english_form')
    ->setRenderer($renderer)
    ->setTheme($theme)
    ->setAction('/api/submit')
    ->setLocale(DatePickerManager::LOCALE_EN);  // 🎯 Set locale once!

// All pickers automatically use English locale
$formEnglish->addText('name', 'Full Name')
    ->required()
    ->placeholder('Enter your full name')
    ->add();

$formEnglish->addEmail('email', 'Email Address')
    ->required()
    ->add();

// Date picker - no need to set locale!
$formEnglish->addDate('birth_date', 'Birth Date')
    ->required()
    ->setPickerOptions([
        'format' => 'mm-dd-yyyy',
        'weekStart' => 0, // Sunday
    ])
    // Locale automatically applied!
    ->add();

// Time picker - no need to set locale!
$formEnglish->addTime('meeting_time', 'Meeting Time')
    ->setPickerOptions([
        'format' => '12',
    ])
    // Locale automatically applied!
    ->add();

// DateTime picker - no need to set locale!
$formEnglish->addDatetime('appointment', 'Appointment Date & Time')
    ->setPickerOptions([
        'timeFormat' => '12',
    ])
    // Locale automatically applied!
    ->add();

// Range slider - no need to set locale!
$formEnglish->addRange('budget', 'Budget')
    ->setPickerOptions([
        'min' => 0,
        'max' => 10000,
        'prefix' => '$',
    ])
    // Locale automatically applied!
    ->add();

$formEnglish->addSubmit('submit', 'Submit');

echo $formEnglish->build();

echo "</div></div>";

// =============================================================================
// Example 2: Turkish Form with Form-Level Locale
// =============================================================================
echo "<h2 class='mb-4'>Example 2: Turkish Form (Form-Level Locale)</h2>";
echo "<div class='card mb-5'><div class='card-body'>";

$formTurkish = FormBuilder::create('turkish_form')
    ->setRenderer($renderer)
    ->setTheme($theme)
    ->setAction('/api/kaydet')
    ->setLocale(DatePickerManager::LOCALE_TR);  // 🎯 Set Turkish locale once!

$formTurkish->addText('ad_soyad', 'Ad Soyad')
    ->required()
    ->placeholder('Adınızı ve soyadınızı girin')
    ->add();

$formTurkish->addDate('dogum_tarihi', 'Doğum Tarihi')
    ->required()
    ->setPickerOptions([
        'format' => 'dd-mm-yyyy',
        'weekStart' => 1, // Monday
    ])
    // Turkish locale automatically applied!
    ->add();

$formTurkish->addTime('randevu_saati', 'Randevu Saati')
    ->setPickerOptions([
        'format' => '24',
    ])
    // Turkish locale automatically applied!
    ->add();

$formTurkish->addDatetime('etkinlik_zamani', 'Etkinlik Zamanı')
    ->setPickerOptions([
        'timeFormat' => '24',
    ])
    // Turkish locale automatically applied!
    ->add();

$formTurkish->addRange('fiyat_araligi', 'Fiyat Aralığı')
    ->setPickerOptions([
        'min' => 0,
        'max' => 5000,
        'dual' => true,
        'from' => 500,
        'to' => 2000,
        'suffix' => ' ₺',
    ])
    // Turkish locale automatically applied!
    ->add();

$formTurkish->addSubmit('submit', 'Gönder');

echo $formTurkish->build();

echo "</div></div>";

// =============================================================================
// Example 3: German Form with Form-Level Locale
// =============================================================================
echo "<h2 class='mb-4'>Example 3: German Form (Form-Level Locale)</h2>";
echo "<div class='card mb-5'><div class='card-body'>";

$formGerman = FormBuilder::create('german_form')
    ->setRenderer($renderer)
    ->setTheme($theme)
    ->setAction('/api/speichern')
    ->setLocale(DatePickerManager::LOCALE_DE);  // 🎯 Set German locale once!

$formGerman->addText('name', 'Vollständiger Name')
    ->required()
    ->add();

$formGerman->addDate('geburtsdatum', 'Geburtsdatum')
    ->required()
    ->setPickerOptions([
        'format' => 'dd.mm.yyyy',
        'weekStart' => 1,
    ])
    ->add();

$formGerman->addTime('besprechungszeit', 'Besprechungszeit')
    ->setPickerOptions([
        'format' => '24',
    ])
    ->add();

$formGerman->addDatetime('termin', 'Termin Datum & Uhrzeit')
    ->setPickerOptions([
        'timeFormat' => '24',
    ])
    ->add();

$formGerman->addSubmit('submit', 'Absenden');

echo $formGerman->build();

echo "</div></div>";

// =============================================================================
// Example 4: French Form with Form-Level Locale
// =============================================================================
echo "<h2 class='mb-4'>Example 4: French Form (Form-Level Locale)</h2>";
echo "<div class='card mb-5'><div class='card-body'>";

$formFrench = FormBuilder::create('french_form')
    ->setRenderer($renderer)
    ->setTheme($theme)
    ->setAction('/api/enregistrer')
    ->setLocale(DatePickerManager::LOCALE_FR);  // 🎯 Set French locale once!

$formFrench->addText('nom', 'Nom complet')
    ->required()
    ->add();

$formFrench->addDate('date_naissance', 'Date de naissance')
    ->required()
    ->setPickerOptions([
        'format' => 'dd/mm/yyyy',
        'weekStart' => 1,
    ])
    ->add();

$formFrench->addTime('heure_reunion', 'Heure de réunion')
    ->setPickerOptions([
        'format' => '24',
    ])
    ->add();

$formFrench->addDatetime('rendez_vous', 'Rendez-vous Date & Heure')
    ->setPickerOptions([
        'timeFormat' => '24',
    ])
    ->add();

$formFrench->addSubmit('submit', 'Soumettre');

echo $formFrench->build();

echo "</div></div>";

// =============================================================================
// Example 5: Spanish Form with Form-Level Locale
// =============================================================================
echo "<h2 class='mb-4'>Example 5: Spanish Form (Form-Level Locale)</h2>";
echo "<div class='card mb-5'><div class='card-body'>";

$formSpanish = FormBuilder::create('spanish_form')
    ->setRenderer($renderer)
    ->setTheme($theme)
    ->setAction('/api/guardar')
    ->setLocale(DatePickerManager::LOCALE_ES);  // 🎯 Set Spanish locale once!

$formSpanish->addText('nombre', 'Nombre completo')
    ->required()
    ->add();

$formSpanish->addDate('fecha_nacimiento', 'Fecha de nacimiento')
    ->required()
    ->setPickerOptions([
        'format' => 'dd/mm/yyyy',
        'weekStart' => 1,
    ])
    ->add();

$formSpanish->addTime('hora_reunion', 'Hora de reunión')
    ->setPickerOptions([
        'format' => '24',
    ])
    ->add();

$formSpanish->addDatetime('cita', 'Cita Fecha y Hora')
    ->setPickerOptions([
        'timeFormat' => '24',
    ])
    ->add();

$formSpanish->addSubmit('submit', 'Enviar');

echo $formSpanish->build();

echo "</div></div>";

// =============================================================================
// Example 6: Arabic Form with Form-Level Locale AND Direction
// =============================================================================
echo "<h2 class='mb-4'>Example 6: Arabic Form (Locale + RTL Direction)</h2>";
echo "<div class='card mb-5'><div class='card-body'>";

$formArabic = FormBuilder::create('arabic_form')
    ->setRenderer($renderer)
    ->setTheme($theme)
    ->setAction('/api/save')
    ->setDirection(TextDirection::RTL)              // Set RTL once
    ->setLocale(DateTimePickerManager::LOCALE_AR);  // Set Arabic locale once

// All pickers get both RTL and Arabic locale automatically!
$formArabic->addText('name', 'الاسم الكامل')
    ->required()
    ->add();

$formArabic->addDate('birth_date', 'تاريخ الميلاد')
    ->required()
    ->setPickerOptions([
        'format' => 'dd-mm-yyyy',
        'weekStart' => 6, // Saturday
    ])
    // Both RTL and Arabic locale applied automatically!
    ->add();

$formArabic->addTime('appointment_time', 'وقت الموعد')
    ->setPickerOptions([
        'format' => '12',
    ])
    // Both RTL and Arabic locale applied automatically!
    ->add();

$formArabic->addDatetime('meeting_datetime', 'تاريخ ووقت الاجتماع')
    ->setPickerOptions([
        'timeFormat' => '24',
    ])
    // Both RTL and Arabic locale applied automatically!
    ->add();

$formArabic->addSubmit('submit', 'إرسال');

echo $formArabic->build();

echo "</div></div>";

// =============================================================================
// Example 7: Hebrew Form with Form-Level Locale AND Direction
// =============================================================================
echo "<h2 class='mb-4'>Example 7: Hebrew Form (Locale + RTL Direction)</h2>";
echo "<div class='card mb-5'><div class='card-body'>";

$formHebrew = FormBuilder::create('hebrew_form')
    ->setRenderer($renderer)
    ->setTheme($theme)
    ->setAction('/api/save')
    ->setDirection(TextDirection::RTL)              // Set RTL once
    ->setLocale(DateTimePickerManager::LOCALE_HE);  // Set Hebrew locale once

$formHebrew->addText('name', 'שם מלא')
    ->required()
    ->add();

$formHebrew->addDate('birth_date', 'תאריך לידה')
    ->required()
    ->setPickerOptions([
        'format' => 'dd-mm-yyyy',
        'weekStart' => 0, // Sunday
    ])
    ->add();

$formHebrew->addTime('meeting_time', 'שעת פגישה')
    ->setPickerOptions([
        'format' => '24',
    ])
    ->add();

$formHebrew->addDatetime('appointment', 'תאריך ושעת פגישה')
    ->setPickerOptions([
        'timeFormat' => '24',
    ])
    ->add();

$formHebrew->addSubmit('submit', 'שלח');

echo $formHebrew->build();

echo "</div></div>";

// =============================================================================
// Example 8: Custom Locale
// =============================================================================
echo "<h2 class='mb-4'>Example 8: Custom Locale</h2>";
echo "<div class='card mb-5'><div class='card-body'>";

// Define custom locale
$customLocale = [
    'months' => [
        'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
        'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
    ],
    'weekdays' => ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
    'today' => 'Today',
    'clear' => 'Clear',
    'now' => 'Now',
    'selectDate' => 'Pick Date',
    'selectTime' => 'Pick Time',
    'hours' => 'Hours',
    'minutes' => 'Minutes',
    'seconds' => 'Seconds',
    'am' => 'AM',
    'pm' => 'PM',
    'from' => 'From',
    'to' => 'To',
];

$formCustom = FormBuilder::create('custom_locale_form')
    ->setRenderer($renderer)
    ->setTheme($theme)
    ->setAction('/api/submit')
    ->setLocale($customLocale);  // Use custom locale

$formCustom->addDate('date', 'Date')
    ->add();

$formCustom->addTime('time', 'Time')
    ->add();

$formCustom->addDatetime('datetime', 'Date & Time')
    ->add();

$formCustom->addSubmit('submit', 'Submit');

echo $formCustom->build();

echo "</div></div>";

// =============================================================================
// Example 9: JSON Output with Locale
// =============================================================================
echo "<h2 class='mb-4'>Example 9: JSON Output with Locale</h2>";
echo "<div class='card mb-5'><div class='card-body'>";

$formJson = FormBuilder::create('json_form')
    ->setRenderer($renderer)
    ->setTheme($theme)
    ->setAction('/api/submit')
    ->setDirection(TextDirection::RTL)
    ->setLocale(DatePickerManager::LOCALE_TR);

$formJson->addText('name', 'Ad Soyad')
    ->required()
    ->add();

$formJson->addDate('date', 'Tarih')
    ->add();

$formJson->addTime('time', 'Saat')
    ->add();

$jsonOutput = $formJson->build(OutputFormat::JSON);

echo "<h5>JSON Output (includes both direction and locale):</h5>";
echo "<pre style='direction: ltr; text-align: left; max-height: 400px; overflow: auto;'>" . htmlspecialchars($jsonOutput) . "</pre>";

echo "</div></div>";

// =============================================================================
// Example 10: Override Form-Level Locale for Specific Picker
// =============================================================================
echo "<h2 class='mb-4'>Example 10: Override Form-Level Locale</h2>";
echo "<div class='card mb-5'><div class='card-body'>";
echo "<p>You can still override the form-level locale for specific pickers if needed:</p>";

$formOverride = FormBuilder::create('override_form')
    ->setRenderer($renderer)
    ->setTheme($theme)
    ->setAction('/api/submit')
    ->setLocale(DatePickerManager::LOCALE_EN);  // Form-level: English

// Uses form-level English locale
$formOverride->addDate('date1', 'Date (English)')
    ->add();

// Override with Turkish locale for this specific picker
$formOverride->addDate('date2', 'Date (Turkish Override)')
    ->setPickerLocale(DatePickerManager::LOCALE_TR)
    ->add();

// Uses form-level English locale again
$formOverride->addTime('time1', 'Time (English)')
    ->add();

$formOverride->addSubmit('submit', 'Submit');

echo $formOverride->build();

echo "</div></div>";

// =============================================================================
// Example 11: Comparison - Old vs New Approach
// =============================================================================
echo "<h2 class='mb-4'>Example 11: Old vs New Approach</h2>";
echo "<div class='card mb-5'><div class='card-body'>";

echo "<h5 class='text-danger'>❌ Old Approach (setting locale for each picker):</h5>";
echo "<pre style='direction: ltr; text-align: left;'>" . htmlspecialchars(
<<<'PHP'
$form = FormBuilder::create('form')
    ->setRenderer($renderer)
    ->setTheme($theme);

// Had to set locale for EACH picker individually
$form->addDate('date1', 'Date')
    ->setPickerLocale(DatePickerManager::LOCALE_TR)  // Manual
    ->add();

$form->addTime('time1', 'Time')
    ->setPickerLocale(TimePickerManager::LOCALE_TR)  // Manual again
    ->add();

$form->addDatetime('datetime1', 'DateTime')
    ->setPickerLocale(DateTimePickerManager::LOCALE_TR)  // Manual again!
    ->add();

$form->addRange('range1', 'Range')
    ->setPickerLocale(RangeSliderManager::LOCALE_TR)  // Manual again!!
    ->add();
PHP
) . "</pre>";

echo "<h5 class='text-success mt-4'>✅ New Approach (set once at form level):</h5>";
echo "<pre style='direction: ltr; text-align: left;'>" . htmlspecialchars(
<<<'PHP'
$form = FormBuilder::create('form')
    ->setRenderer($renderer)
    ->setTheme($theme)
    ->setLocale(DatePickerManager::LOCALE_TR);  // Set ONCE!

// All pickers automatically use Turkish locale
$form->addDate('date1', 'Date')->add();
$form->addTime('time1', 'Time')->add();
$form->addDatetime('datetime1', 'DateTime')->add();
$form->addRange('range1', 'Range')->add();
// No need to set locale for each picker!
PHP
) . "</pre>";

echo "<div class='alert alert-info mt-3'>";
echo "<strong>Benefits of the new approach:</strong>";
echo "<ul>";
echo "<li><strong>Set once</strong>: Configure locale at form level, not for each picker</li>";
echo "<li><strong>DRY Principle</strong>: Don't Repeat Yourself - write less code</li>";
echo "<li><strong>Cleaner code</strong>: No repetitive setPickerLocale() calls</li>";
echo "<li><strong>Easier maintenance</strong>: Change locale in one place</li>";
echo "<li><strong>Less error-prone</strong>: Can't forget to set locale on a picker</li>";
echo "<li><strong>Flexible</strong>: Can still override for specific pickers if needed</li>";
echo "</ul>";
echo "</div>";

echo "</div></div>";

// =============================================================================
// Summary
// =============================================================================
echo "<div class='alert alert-success'>";
echo "<h4>Summary: Form-Level Locale Support</h4>";
echo "<p><strong>How to use:</strong></p>";
echo "<ul>";
echo "<li>Use <code>setLocale(array \$locale)</code> when creating the form</li>";
echo "<li>The locale automatically applies to all pickers (date, time, datetime, range)</li>";
echo "<li>No need to call <code>setPickerLocale()</code> for each picker</li>";
echo "<li>Can be combined with <code>setDirection()</code> for RTL/LTR support</li>";
echo "<li>Works with all output formats (HTML, JSON, XML)</li>";
echo "<li>Can still override for specific pickers if needed using <code>setPickerLocale()</code></li>";
echo "</ul>";

echo "<p><strong>Available built-in locales:</strong></p>";
echo "<ul>";
echo "<li><code>DatePickerManager::LOCALE_EN</code> - English</li>";
echo "<li><code>DatePickerManager::LOCALE_TR</code> - Turkish</li>";
echo "<li><code>DatePickerManager::LOCALE_DE</code> - German</li>";
echo "<li><code>DatePickerManager::LOCALE_FR</code> - French</li>";
echo "<li><code>DatePickerManager::LOCALE_ES</code> - Spanish</li>";
echo "<li><code>DateTimePickerManager::LOCALE_AR</code> - Arabic (RTL)</li>";
echo "<li><code>DateTimePickerManager::LOCALE_HE</code> - Hebrew (RTL)</li>";
echo "<li>Or create your own custom locale array</li>";
echo "</ul>";
echo "</div>";

echo "</div>"; // End container

echo "<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>\n";

<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use FormGenerator\V2\Builder\FormBuilder;
use FormGenerator\V2\Builder\DatePickerManager;
use FormGenerator\V2\Builder\TimePickerManager;
use FormGenerator\V2\Builder\DateTimePickerManager;
use FormGenerator\V2\Renderer\TwigRenderer;
use FormGenerator\V2\Theme\Bootstrap5Theme;
use FormGenerator\V2\Contracts\TextDirection;

/**
 * FormGenerator V2 - RTL/LTR Direction Support Examples
 *
 * Demonstrates form-level direction support:
 * - Set direction once at form level
 * - Automatically applies to all inputs
 * - Automatically applies to all pickers
 * - Supports both RTL (Arabic, Hebrew) and LTR (English, etc.)
 */

// Initialize renderer and theme
$renderer = new TwigRenderer(__DIR__ . '/../../src/V2/Theme/templates');
$theme = new Bootstrap5Theme();

echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>\n";
echo "<div class='container my-5'>\n";

// =============================================================================
// Example 1: Arabic Form (RTL)
// =============================================================================
echo "<h2 class='mb-4'>Example 1: Arabic Form (RTL - Right to Left)</h2>";
echo "<div class='card mb-5'><div class='card-body'>";

$formArabic = FormBuilder::create('arabic_form')
    ->setRenderer($renderer)
    ->setTheme($theme)
    ->setAction('/api/submit')
    ->setDirection(TextDirection::RTL);  // Set RTL once for entire form

// All inputs and pickers automatically inherit RTL
$formArabic->addText('name', 'الاسم الكامل')
    ->required()
    ->placeholder('أدخل اسمك الكامل')
    ->add();

$formArabic->addEmail('email', 'البريد الإلكتروني')
    ->required()
    ->placeholder('example@domain.com')
    ->add();

// Date picker automatically gets RTL - no need to set rtl option!
$formArabic->addDate('birth_date', 'تاريخ الميلاد')
    ->required()
    ->setPickerLocale(DatePickerManager::LOCALE_AR)
    // RTL is automatically applied - no need for setPickerOptions(['rtl' => true])
    ->setPickerOptions([
        'format' => 'yyyy-mm-dd',
        'weekStart' => 6, // Saturday
    ])
    ->add();

// Time picker automatically gets RTL
$formArabic->addTime('appointment_time', 'وقت الموعد')
    ->setPickerLocale(TimePickerManager::LOCALE_TR)  // Use Turkish or any locale
    ->setPickerOptions([
        'format' => '24',
    ])
    ->add();

// DateTime picker automatically gets RTL
$formArabic->addDatetime('meeting_datetime', 'تاريخ ووقت الاجتماع')
    ->setPickerLocale(DateTimePickerManager::LOCALE_AR)
    ->setPickerOptions([
        'timeFormat' => '24',
    ])
    ->add();

$formArabic->addTextarea('message', 'رسالتك')
    ->rows(5)
    ->placeholder('اكتب رسالتك هنا')
    ->add();

$formArabic->addSubmit('submit', 'إرسال');

echo $formArabic->build();

echo "</div></div>";

// =============================================================================
// Example 2: Hebrew Form (RTL)
// =============================================================================
echo "<h2 class='mb-4'>Example 2: Hebrew Form (RTL - Right to Left)</h2>";
echo "<div class='card mb-5'><div class='card-body'>";

$formHebrew = FormBuilder::create('hebrew_form')
    ->setRenderer($renderer)
    ->setTheme($theme)
    ->setAction('/api/submit')
    ->setDirection(TextDirection::RTL);  // Set RTL once

$formHebrew->addText('name', 'שם מלא')
    ->required()
    ->placeholder('הזן את שמך המלא')
    ->add();

$formHebrew->addDate('event_date', 'תאריך האירוע')
    ->required()
    ->setPickerLocale(DatePickerManager::LOCALE_HE)
    // RTL automatically applied
    ->setPickerOptions([
        'format' => 'dd-mm-yyyy',
        'weekStart' => 0, // Sunday
    ])
    ->add();

$formHebrew->addDatetime('meeting_time', 'תאריך ושעת פגישה')
    ->setPickerLocale(DateTimePickerManager::LOCALE_HE)
    ->add();

$formHebrew->addSelect('city', 'עיר')
    ->options([
        'jerusalem' => 'ירושלים',
        'tel_aviv' => 'תל אביב',
        'haifa' => 'חיפה',
        'beer_sheva' => 'באר שבע',
    ])
    ->add();

$formHebrew->addSubmit('submit', 'שלח');

echo $formHebrew->build();

echo "</div></div>";

// =============================================================================
// Example 3: English Form (LTR - Default)
// =============================================================================
echo "<h2 class='mb-4'>Example 3: English Form (LTR - Left to Right)</h2>";
echo "<div class='card mb-5'><div class='card-body'>";

$formEnglish = FormBuilder::create('english_form')
    ->setRenderer($renderer)
    ->setTheme($theme)
    ->setAction('/api/submit')
    ->setDirection(TextDirection::LTR);  // Explicitly set LTR (optional, as LTR is default)

$formEnglish->addText('name', 'Full Name')
    ->required()
    ->placeholder('Enter your full name')
    ->add();

$formEnglish->addEmail('email', 'Email Address')
    ->required()
    ->add();

$formEnglish->addDate('birth_date', 'Birth Date')
    ->setPickerLocale(DatePickerManager::LOCALE_EN)
    ->setPickerOptions([
        'format' => 'mm-dd-yyyy',
    ])
    ->add();

$formEnglish->addTime('meeting_time', 'Meeting Time')
    ->setPickerLocale(TimePickerManager::LOCALE_EN)
    ->setPickerOptions([
        'format' => '12',
    ])
    ->add();

$formEnglish->addSubmit('submit', 'Submit');

echo $formEnglish->build();

echo "</div></div>";

// =============================================================================
// Example 4: Turkish Form (LTR)
// =============================================================================
echo "<h2 class='mb-4'>Example 4: Turkish Form (LTR - Left to Right)</h2>";
echo "<div class='card mb-5'><div class='card-body'>";

$formTurkish = FormBuilder::create('turkish_form')
    ->setRenderer($renderer)
    ->setTheme($theme)
    ->setAction('/api/kaydet')
    ->setDirection(TextDirection::LTR);  // Turkish uses LTR

$formTurkish->addText('ad_soyad', 'Ad Soyad')
    ->required()
    ->placeholder('Adınızı ve soyadınızı girin')
    ->add();

$formTurkish->addDate('dogum_tarihi', 'Doğum Tarihi')
    ->required()
    ->setPickerLocale(DatePickerManager::LOCALE_TR)
    ->setPickerOptions([
        'format' => 'dd-mm-yyyy',
        'weekStart' => 1, // Monday
    ])
    ->add();

$formTurkish->addTime('randevu_saati', 'Randevu Saati')
    ->setPickerLocale(TimePickerManager::LOCALE_TR)
    ->setPickerOptions([
        'format' => '24',
    ])
    ->add();

$formTurkish->addTextarea('mesaj', 'Mesajınız')
    ->rows(4)
    ->add();

$formTurkish->addSubmit('submit', 'Gönder');

echo $formTurkish->build();

echo "</div></div>";

// =============================================================================
// Example 5: Mixed Content Form (Arabic labels with English inputs)
// =============================================================================
echo "<h2 class='mb-4'>Example 5: Bilingual Form (Arabic Interface, Mixed Content)</h2>";
echo "<div class='card mb-5'><div class='card-body'>";

$formBilingual = FormBuilder::create('bilingual_form')
    ->setRenderer($renderer)
    ->setTheme($theme)
    ->setAction('/api/submit')
    ->setDirection(TextDirection::RTL);  // Interface is RTL

$formBilingual->addText('name', 'الاسم (Name)')
    ->required()
    ->placeholder('John Doe')
    ->add();

$formBilingual->addEmail('email', 'البريد الإلكتروني (Email)')
    ->required()
    ->add();

$formBilingual->addNumber('phone', 'رقم الهاتف (Phone)')
    ->placeholder('+971501234567')
    ->add();

$formBilingual->addDate('preferred_date', 'التاريخ المفضل (Preferred Date)')
    ->setPickerLocale(DatePickerManager::LOCALE_AR)
    ->add();

$formBilingual->addSubmit('submit', 'إرسال (Submit)');

echo $formBilingual->build();

echo "</div></div>";

// =============================================================================
// Example 6: JSON Output with Direction
// =============================================================================
echo "<h2 class='mb-4'>Example 6: JSON Output with Direction</h2>";
echo "<div class='card mb-5'><div class='card-body'>";

use FormGenerator\V2\Contracts\OutputFormat;

$formJson = FormBuilder::create('json_form')
    ->setRenderer($renderer)
    ->setTheme($theme)
    ->setAction('/api/submit')
    ->setDirection(TextDirection::RTL);

$formJson->addText('username', 'اسم المستخدم')
    ->required()
    ->add();

$formJson->addPassword('password', 'كلمة المرور')
    ->required()
    ->add();

$jsonOutput = $formJson->build(OutputFormat::JSON);

echo "<h5>JSON Output (with direction field):</h5>";
echo "<pre style='direction: ltr; text-align: left;'>" . htmlspecialchars($jsonOutput) . "</pre>";

echo "</div></div>";

// =============================================================================
// Example 7: Comparison - Old vs New Approach
// =============================================================================
echo "<h2 class='mb-4'>Example 7: Old vs New Approach</h2>";
echo "<div class='card mb-5'><div class='card-body'>";

echo "<h5 class='text-danger'>❌ Old Approach (setting RTL for each picker):</h5>";
echo "<pre style='direction: ltr; text-align: left;'>" . htmlspecialchars(
<<<'PHP'
$form = FormBuilder::create('form')
    ->setRenderer($renderer)
    ->setTheme($theme);

// Had to set RTL for EACH picker individually
$form->addDate('date1', 'تاريخ')
    ->setPickerOptions(['rtl' => true])  // Manual RTL
    ->add();

$form->addTime('time1', 'وقت')
    ->setPickerOptions(['rtl' => true])  // Manual RTL again
    ->add();

$form->addDatetime('datetime1', 'تاريخ ووقت')
    ->setPickerOptions(['rtl' => true])  // Manual RTL again!
    ->add();
PHP
) . "</pre>";

echo "<h5 class='text-success mt-4'>✅ New Approach (set once at form level):</h5>";
echo "<pre style='direction: ltr; text-align: left;'>" . htmlspecialchars(
<<<'PHP'
$form = FormBuilder::create('form')
    ->setRenderer($renderer)
    ->setTheme($theme)
    ->setDirection(TextDirection::RTL);  // Set ONCE!

// All pickers automatically get RTL
$form->addDate('date1', 'تاريخ')->add();
$form->addTime('time1', 'وقت')->add();
$form->addDatetime('datetime1', 'تاريخ ووقت')->add();
// No need to set rtl option for each picker!
PHP
) . "</pre>";

echo "<div class='alert alert-info mt-3'>";
echo "<strong>Benefits of the new approach:</strong>";
echo "<ul>";
echo "<li>Set direction once at form level</li>";
echo "<li>Automatically applies to all inputs</li>";
echo "<li>Automatically applies to all pickers</li>";
echo "<li>Cleaner, more maintainable code</li>";
echo "<li>Less repetition</li>";
echo "</ul>";
echo "</div>";

echo "</div></div>";

// =============================================================================
// Summary
// =============================================================================
echo "<div class='alert alert-success'>";
echo "<h4>Summary</h4>";
echo "<p><strong>Form-level direction support means:</strong></p>";
echo "<ul>";
echo "<li>Use <code>setDirection(TextDirection::RTL)</code> or <code>setDirection(TextDirection::LTR)</code> once when creating the form</li>";
echo "<li>The direction automatically applies to the <code>&lt;form&gt;</code> element (<code>dir=\"rtl\"</code> or <code>dir=\"ltr\"</code>)</li>";
echo "<li>All input fields automatically get the <code>dir</code> attribute</li>";
echo "<li>All pickers (date, time, datetime, range) automatically get <code>rtl: true</code> or <code>rtl: false</code></li>";
echo "<li>No need to manually set RTL for each picker</li>";
echo "<li>Works with all output formats (HTML, JSON, XML)</li>";
echo "</ul>";
echo "</div>";

echo "</div>"; // End container

echo "<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>\n";

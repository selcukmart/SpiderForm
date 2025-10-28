<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use FormGenerator\V2\Builder\FormBuilder;
use FormGenerator\V2\Builder\DatePickerManager;
use FormGenerator\V2\Builder\TimePickerManager;
use FormGenerator\V2\Builder\RangeSliderManager;
use FormGenerator\V2\Renderer\TwigRenderer;
use FormGenerator\V2\Theme\Bootstrap5Theme;

/**
 * FormGenerator V2 - Built-in Pickers Examples
 *
 * Demonstrates date, time, datetime, and range pickers with:
 * - Multi-language support
 * - Custom configurations
 * - Enable/disable picker functionality
 */

// Initialize renderer and theme
$renderer = new TwigRenderer(__DIR__ . '/../../src/V2/Theme/templates');
$theme = new Bootstrap5Theme();

echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>\n";
echo "<div class='container my-5'>\n";

// =============================================================================
// Example 1: Date Picker with English Locale
// =============================================================================
echo "<h2 class='mb-4'>Example 1: Date Picker (English)</h2>";
echo "<div class='card mb-5'><div class='card-body'>";

$form1 = FormBuilder::create('date_form_en')
    ->setRenderer($renderer)
    ->setTheme($theme)
    ->setAction('/api/save');

// Date picker with default settings
$form1->addDate('event_date', 'Event Date')
    ->required()
    ->setPickerLocale(DatePickerManager::LOCALE_EN)
    ->setPickerOptions([
        'format' => 'yyyy-mm-dd',
        'minDate' => date('Y-m-d'), // Today or later
        'showToday' => true,
        'showClear' => true,
    ])
    ->helpText('Select event date (must be today or later)')
    ->add();

$form1->addSubmit('save', 'Save')->build();

echo $form1->build();

echo "</div></div>";

// =============================================================================
// Example 2: Date Picker with Turkish Locale
// =============================================================================
echo "<h2 class='mb-4'>Example 2: Date Picker (Turkish)</h2>";
echo "<div class='card mb-5'><div class='card-body'>";

$form2 = FormBuilder::create('date_form_tr')
    ->setRenderer($renderer)
    ->setTheme($theme)
    ->setAction('/api/save');

// Turkish date picker
$form2->addDate('dogum_tarihi', 'Doğum Tarihi')
    ->required()
    ->setPickerLocale(DatePickerManager::LOCALE_TR)
    ->setPickerOptions([
        'format' => 'dd-mm-yyyy',
        'weekStart' => 1, // Monday
        'maxDate' => date('Y-m-d'), // Cannot be in the future
    ])
    ->helpText('Doğum tarihini seçiniz')
    ->add();

$form2->addSubmit('kaydet', 'Kaydet');

echo $form2->build();

echo "</div></div>";

// =============================================================================
// Example 3: Time Picker (12-hour and 24-hour formats)
// =============================================================================
echo "<h2 class='mb-4'>Example 3: Time Pickers</h2>";
echo "<div class='card mb-5'><div class='card-body'>";

$form3 = FormBuilder::create('time_form')
    ->setRenderer($renderer)
    ->setTheme($theme)
    ->setAction('/api/save');

// 12-hour format
$form3->addTime('appointment_time', 'Appointment Time (12-hour)')
    ->setPickerLocale(TimePickerManager::LOCALE_EN)
    ->setPickerOptions([
        'format' => '12',
        'showSeconds' => false,
    ])
    ->helpText('Select time in 12-hour format')
    ->add();

// 24-hour format with seconds
$form3->addTime('meeting_time', 'Meeting Time (24-hour)')
    ->setPickerLocale(TimePickerManager::LOCALE_EN)
    ->setPickerOptions([
        'format' => '24',
        'showSeconds' => true,
        'step' => 5, // 5-minute intervals
    ])
    ->helpText('Select time in 24-hour format with seconds')
    ->add();

// Turkish time picker
$form3->addTime('randevu_saati', 'Randevu Saati')
    ->setPickerLocale(TimePickerManager::LOCALE_TR)
    ->setPickerOptions([
        'format' => '24',
    ])
    ->helpText('Saat seçiniz')
    ->add();

$form3->addSubmit('save', 'Save');

echo $form3->build();

echo "</div></div>";

// =============================================================================
// Example 4: Range Slider (Single Handle)
// =============================================================================
echo "<h2 class='mb-4'>Example 4: Range Slider (Single Handle)</h2>";
echo "<div class='card mb-5'><div class='card-body'>";

$form4 = FormBuilder::create('range_form_single')
    ->setRenderer($renderer)
    ->setTheme($theme)
    ->setAction('/api/save');

// Age slider
$form4->addRange('age', 'Your Age')
    ->setPickerOptions([
        'min' => 18,
        'max' => 100,
        'value' => 30,
        'step' => 1,
        'suffix' => ' years',
        'showValue' => true,
        'showTooltip' => true,
    ])
    ->helpText('Drag the slider to select your age')
    ->add();

// Budget slider with prefix
$form4->addRange('budget', 'Budget')
    ->setPickerOptions([
        'min' => 0,
        'max' => 10000,
        'value' => 5000,
        'step' => 100,
        'prefix' => '$',
        'showValue' => true,
    ])
    ->helpText('Set your budget')
    ->add();

// Percentage slider
$form4->addRange('discount', 'Discount')
    ->setPickerOptions([
        'min' => 0,
        'max' => 100,
        'value' => 20,
        'step' => 5,
        'suffix' => '%',
    ])
    ->add();

$form4->addSubmit('save', 'Save');

echo $form4->build();

echo "</div></div>";

// =============================================================================
// Example 5: Range Slider (Dual Handle)
// =============================================================================
echo "<h2 class='mb-4'>Example 5: Range Slider (Dual Handle)</h2>";
echo "<div class='card mb-5'><div class='card-body'>";

$form5 = FormBuilder::create('range_form_dual')
    ->setRenderer($renderer)
    ->setTheme($theme)
    ->setAction('/api/save');

// Price range
$form5->addRange('price_range', 'Price Range')
    ->setPickerOptions([
        'min' => 0,
        'max' => 1000,
        'from' => 200,
        'to' => 800,
        'dual' => true,
        'prefix' => '$',
        'step' => 10,
        'showValue' => true,
        'locale' => RangeSliderManager::LOCALE_EN,
    ])
    ->helpText('Select min and max price')
    ->add();

// Age range
$form5->addRange('age_range', 'Age Range')
    ->setPickerOptions([
        'min' => 0,
        'max' => 120,
        'from' => 25,
        'to' => 45,
        'dual' => true,
        'suffix' => ' years',
        'locale' => RangeSliderManager::LOCALE_EN,
    ])
    ->add();

$form5->addSubmit('search', 'Search');

echo $form5->build();

echo "</div></div>";

// =============================================================================
// Example 6: Disabled Picker (Use Native HTML5 or Custom)
// =============================================================================
echo "<h2 class='mb-4'>Example 6: Disabled Built-in Picker (Native HTML5)</h2>";
echo "<div class='card mb-5'><div class='card-body'>";

$form6 = FormBuilder::create('native_picker_form')
    ->setRenderer($renderer)
    ->setTheme($theme)
    ->setAction('/api/save');

// Use native HTML5 date input
$form6->addDate('native_date', 'Native Date Input')
    ->disablePicker() // Disable built-in picker
    ->required()
    ->helpText('This uses native HTML5 date input')
    ->add();

// Use native HTML5 time input
$form6->addTime('native_time', 'Native Time Input')
    ->disablePicker()
    ->helpText('This uses native HTML5 time input')
    ->add();

// Use native HTML5 range input
$form6->addRange('native_range', 'Native Range Input')
    ->disablePicker()
    ->attributes(['min' => '0', 'max' => '100', 'value' => '50'])
    ->helpText('This uses native HTML5 range input')
    ->add();

$form6->addSubmit('save', 'Save');

echo $form6->build();

echo "</div></div>";

// =============================================================================
// Example 7: Combined Form with All Pickers
// =============================================================================
echo "<h2 class='mb-4'>Example 7: Event Booking Form (All Pickers)</h2>";
echo "<div class='card mb-5'><div class='card-body'>";

$form7 = FormBuilder::create('event_booking')
    ->setRenderer($renderer)
    ->setTheme($theme)
    ->setAction('/api/bookings/create');

$form7->addText('event_name', 'Event Name')
    ->required()
    ->add();

// Event date
$form7->addDate('event_date', 'Event Date')
    ->required()
    ->setPickerLocale(DatePickerManager::LOCALE_EN)
    ->setPickerOptions([
        'format' => 'yyyy-mm-dd',
        'minDate' => date('Y-m-d'),
        'disabledDates' => [
            date('Y-m-d', strtotime('+3 days')), // Example: disable specific date
        ],
    ])
    ->add();

// Event time
$form7->addTime('event_time', 'Event Time')
    ->required()
    ->setPickerOptions([
        'format' => '12',
        'showSeconds' => false,
    ])
    ->add();

// Number of attendees
$form7->addRange('attendees', 'Number of Attendees')
    ->required()
    ->setPickerOptions([
        'min' => 1,
        'max' => 100,
        'value' => 10,
        'step' => 1,
        'suffix' => ' people',
        'showValue' => true,
    ])
    ->add();

// Budget range
$form7->addRange('budget_range', 'Budget Range')
    ->setPickerOptions([
        'min' => 0,
        'max' => 50000,
        'from' => 5000,
        'to' => 20000,
        'dual' => true,
        'prefix' => '$',
        'step' => 500,
        'locale' => RangeSliderManager::LOCALE_EN,
    ])
    ->add();

$form7->addSubmit('book', 'Book Event');

echo $form7->build();

echo "</div></div>";

// =============================================================================
// Example 8: Custom Locale
// =============================================================================
echo "<h2 class='mb-4'>Example 8: Custom Locale</h2>";
echo "<div class='card mb-5'><div class='card-body'>";

$form8 = FormBuilder::create('custom_locale_form')
    ->setRenderer($renderer)
    ->setTheme($theme)
    ->setAction('/api/save');

// Custom locale (example: Arabic-like structure)
$customLocale = [
    'months' => ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'],
    'monthsShort' => ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'],
    'weekdays' => ['الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'],
    'weekdaysShort' => ['أحد', 'اثنين', 'ثلاثاء', 'أربعاء', 'خميس', 'جمعة', 'سبت'],
    'weekdaysMin' => ['أح', 'إث', 'ثل', 'أر', 'خم', 'جم', 'سب'],
    'today' => 'اليوم',
    'clear' => 'مسح',
    'close' => 'إغلاق',
];

$form8->addDate('custom_date', 'تاريخ مخصص')
    ->setPickerLocale($customLocale)
    ->setPickerOptions([
        'weekStart' => 6, // Saturday
    ])
    ->helpText('مثال على التقويم بلغة مخصصة')
    ->add();

$form8->addSubmit('save', 'حفظ');

echo $form8->build();

echo "</div></div>";

// =============================================================================
// JavaScript API Demonstration
// =============================================================================
echo "<h2 class='mb-4'>Example 9: JavaScript API</h2>";
echo "<div class='card mb-5'><div class='card-body'>";

$form9 = FormBuilder::create('js_api_form')
    ->setRenderer($renderer)
    ->setTheme($theme);

$form9->addDate('api_date', 'Controlled Date')
    ->add();

$form9->addTime('api_time', 'Controlled Time')
    ->add();

$form9->addRange('api_range', 'Controlled Range')
    ->setPickerOptions([
        'min' => 0,
        'max' => 100,
        'value' => 50,
    ])
    ->add();

echo $form9->build();

echo <<<'HTML'
<div class="mt-3">
    <button onclick="setDateToToday()" class="btn btn-sm btn-primary">Set Date to Today</button>
    <button onclick="setTimeToNow()" class="btn btn-sm btn-primary">Set Time to Now</button>
    <button onclick="setRangeTo75()" class="btn btn-sm btn-primary">Set Range to 75</button>
    <button onclick="getRangeValue()" class="btn btn-sm btn-info">Get Range Value</button>
</div>

<script>
function setDateToToday() {
    const picker = window.DatePicker_api_date;
    if (picker) {
        const today = new Date();
        const dateStr = today.getFullYear() + '-' +
                       String(today.getMonth() + 1).padStart(2, '0') + '-' +
                       String(today.getDate()).padStart(2, '0');
        picker.selectDate(dateStr);
        alert('Date set to today!');
    }
}

function setTimeToNow() {
    const picker = window.TimePicker_api_time;
    if (picker) {
        picker.setNow();
        alert('Time set to now!');
    }
}

function setRangeTo75() {
    const slider = window.RangeSlider_api_range;
    if (slider) {
        slider.setValue(75);
        alert('Range set to 75!');
    }
}

function getRangeValue() {
    const slider = window.RangeSlider_api_range;
    if (slider) {
        const values = slider.getValues();
        alert('Current range value: ' + values.value);
    }
}

// Listen to picker events
document.getElementById('api_date')?.addEventListener('datepicker:change', (e) => {
    console.log('Date changed:', e.detail);
});

document.getElementById('api_time')?.addEventListener('timepicker:change', (e) => {
    console.log('Time changed:', e.detail);
});

document.getElementById('api_range')?.addEventListener('rangeslider:change', (e) => {
    console.log('Range changed:', e.detail);
});
</script>
HTML;

echo "</div></div>";

echo "</div>"; // End container

echo "<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>\n";


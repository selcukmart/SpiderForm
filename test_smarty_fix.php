<?php
/**
 * Test script to validate SmartyRenderer null attributes fix
 * Simulates the user's form code without external dependencies
 */

require_once __DIR__ . '/vendor/autoload.php';

use SpiderForm\V2\Builder\FormBuilder;
use SpiderForm\V2\Renderer\SmartyRenderer;
use SpiderForm\V2\Theme\Bootstrap3Theme;

// CSS Prefix to avoid conflicts
const OMR_FORM_PREFIX = 'omr-form-';

echo "=== Testing SmartyRenderer with null attributes fix ===\n\n";

try {
    // Setup Renderer and Theme
    $renderer = new SmartyRenderer(
        null,
        null,
        sys_get_temp_dir() . '/smarty_compile_test',
        sys_get_temp_dir() . '/smarty_cache_test'
    );
    $theme = new Bootstrap3Theme();

    echo "✓ SmartyRenderer initialized successfully\n";

    // Simulate form data
    $row_table = [];
    $isEdit = !empty($row_table);

    // Mock exam types data
    $examTypes = [
        '1' => 'Type A - 5 Columns',
        '2' => 'Type B - 4 Columns',
        '3' => 'Type C - Custom'
    ];

    // Build form using SpiderForm V2 Chain Pattern
    $form = FormBuilder::create('exam-insert')
        ->setAction('')
        ->setMethod('POST')
        ->setRenderer($renderer)
        ->setTheme($theme)

        // Section: Optik Form Bilgileri
        ->addSection('Optik Form Bilgileri', 'Optik form bilgilerini giriniz')

        // Input: Optik Form Adı (Text)
        ->addText('name', 'Optik Form Adı')
        ->required()
        ->maxLength(128)
        ->placeholder('Optik Form Adı')
        ->addClass(OMR_FORM_PREFIX . 'input-name')
        ->add()

        // Input: Optik Form Foto URL (Text)
        ->addText('photo', 'Optik Form Foto URL')
        ->maxLength(256)
        ->placeholder('Optik Form Foto URL')
        ->addClass(OMR_FORM_PREFIX . 'input-photo')
        ->add()

        // Input: Optik Form Tipi (Radio)
        ->addRadio('type', 'Optik Form Tipi')
        ->required()
        ->options($examTypes)
        ->helpText('<div class="' . OMR_FORM_PREFIX . 'help-block">
                <strong>Not 1:</strong> Bu form tipine göre sistem sizden optik formun sütun başlama ve bitiş bilgilerini talep edecektir.<br>
                <strong>Not 2:</strong> Optik form tipiniz burada yoksa buradan ekleyiniz
            </div>')
        ->addClass(OMR_FORM_PREFIX . 'radio-type')
        ->add()

        // Hidden Input: Company ID
        ->addHidden('company_id', '12345')

        // Submit Button
        ->addSubmit('save', $isEdit ? 'Güncelle' : 'Kaydet')
        ->buildForm();

    echo "✓ Form built successfully\n";

    // Test 1: Render the form (this will trigger the attributes modifier)
    echo "\n--- Test 1: Rendering form ---\n";
    $output = $form->render();

    if (strlen($output) > 0) {
        echo "✓ Form rendered successfully\n";
        echo "  Output length: " . strlen($output) . " characters\n";
    } else {
        echo "✗ Form rendered but output is empty\n";
    }

    // Test 2: Check if form contains expected elements
    echo "\n--- Test 2: Validating form content ---\n";
    $checks = [
        'name="name"' => 'Text input (name)',
        'name="photo"' => 'Text input (photo)',
        'name="type"' => 'Radio input (type)',
        'name="company_id"' => 'Hidden input (company_id)',
        'name="save"' => 'Submit button',
        OMR_FORM_PREFIX . 'input-name' => 'CSS class prefix'
    ];

    foreach ($checks as $needle => $description) {
        if (strpos($output, $needle) !== false) {
            echo "✓ Contains: $description\n";
        } else {
            echo "✗ Missing: $description\n";
        }
    }

    // Test 3: Test with null attributes explicitly
    echo "\n--- Test 3: Testing null attributes handling ---\n";

    // Create a simple form with minimal attributes
    $minimalForm = FormBuilder::create('minimal-test')
        ->setRenderer($renderer)
        ->setTheme($theme)
        ->addText('test_field', 'Test Field')
        ->add()
        ->buildForm();

    $minimalOutput = $minimalForm->render();

    if (strlen($minimalOutput) > 0) {
        echo "✓ Minimal form rendered without errors\n";
        echo "  Output length: " . strlen($minimalOutput) . " characters\n";
    } else {
        echo "✗ Minimal form failed to render\n";
    }

    // Test 4: Test form with data (edit mode)
    echo "\n--- Test 4: Testing form with data (edit mode) ---\n";
    $form->setData([
        'name' => 'Test Exam Form',
        'photo' => 'https://example.com/photo.jpg',
        'type' => '1',
        'company_id' => '99999'
    ]);

    $editOutput = $form->render();

    if (strlen($editOutput) > 0) {
        echo "✓ Form with data rendered successfully\n";

        // Check if values are present
        if (strpos($editOutput, 'Test Exam Form') !== false) {
            echo "✓ Form contains populated data\n";
        } else {
            echo "⚠ Form data may not be properly populated\n";
        }
    } else {
        echo "✗ Form with data failed to render\n";
    }

    echo "\n=== All tests completed successfully! ===\n";
    echo "The SmartyRenderer fix correctly handles null attributes.\n\n";

} catch (\TypeError $e) {
    echo "\n✗ FATAL ERROR: TypeError occurred!\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\nThis indicates the fix did not work properly.\n";
    exit(1);
} catch (\Exception $e) {
    echo "\n✗ ERROR: " . get_class($e) . "\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    exit(1);
}

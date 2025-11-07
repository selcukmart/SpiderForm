<?php
/**
 * @author selcukmart
 * 25.01.2021
 * 13:28
 *
 * SpiderForm V2 - Chain Pattern Format
 * Converted from array format to modern chain pattern
 *
 * CORRECTED VERSION - Uses buildForm() instead of build()
 */

use OpticalMarkReader\OpticalMarkReaderModels\OpticalMarkReaderExamTypesEntity;
use Sayfa\Template\AdminSmarty;
use Sistem\Company;
use SpiderForm\V2\Builder\FormBuilder;
use SpiderForm\V2\Renderer\SmartyRenderer;
use SpiderForm\V2\Theme\Bootstrap3Theme;

// CSS Prefix to avoid conflicts
const OMR_FORM_PREFIX = 'omr-form-';

// Setup Renderer and Theme
$AdminSmarty = new AdminSmarty();
$renderer = new SmartyRenderer(
    $AdminSmarty->getAdminSmarty(),
    $AdminSmarty->getTemplateDir(),
    $AdminSmarty->getCompileDir(),
    $AdminSmarty->getCacheDir()
);
$theme = new Bootstrap3Theme();

// Get row_table for edit mode (if exists)
$row_table = $row_table ?? [];
$isEdit = !empty($row_table);

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
        ->options(OpticalMarkReaderExamTypesEntity::getAsTypes())
        ->helpText('<div class="' . OMR_FORM_PREFIX . 'help-block">
            <strong>Not 1:</strong> Bu form tipine göre sistem sizden optik formun sütun başlama ve bitiş bilgilerini talep edecektir.<br>
            <strong>Not 2:</strong> Optik form tipiniz burada yoksa <a href="' . ADMIN_URL . '/optical-reader-exam-types/index.php" class="' . OMR_FORM_PREFIX . 'external-link">buradan ekleyiniz</a>
        </div>')
        ->addClass(OMR_FORM_PREFIX . 'radio-type')
        ->add()

    // Hidden Input: Company ID
    ->addHidden('company_id_not_auto_selected', !empty($row_table['company_id']) ? $row_table['company_id'] : Company::getID())
        ->add()

    // Submit Button
    ->addSubmit('save', $isEdit ? 'Güncelle' : 'Kaydet')

    // IMPORTANT: Use buildForm() to get Form object (not build()!)
    ->buildForm();

// Set data for edit mode
if ($isEdit) {
    $form->setData($row_table);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form->submit($_POST);

    if ($form->isValid()) {
        $data = $form->getData();

        // Process the validated data
        // Save to database, redirect, etc.
        // Example:
        // $omrTemplatesModel = new OMRTemplatesModel();
        // if ($isEdit) {
        //     $omrTemplatesModel->update($row_table['id'], $data);
        // } else {
        //     $omrTemplatesModel->insert($data);
        // }
        // header('Location: /success.php');
        // exit;

        echo '<div class="alert alert-success">Form başarıyla kaydedildi!</div>';
    } else {
        // Get validation errors - Two options available:

        // Option 1: Get as ErrorList object (v2.9.0 - Recommended)
        $errorList = $form->getErrorList(deep: true);
        foreach ($errorList as $error) {
            echo '<div class="alert alert-danger">';
            echo htmlspecialchars($error->getMessage());
            echo '</div>';
        }

        // Option 2: Get as array (Legacy)
        /*
        $errors = $form->getErrors(deep: true);
        foreach ($errors as $fieldName => $fieldErrors) {
            if (is_array($fieldErrors)) {
                foreach ($fieldErrors as $errorMessage) {
                    echo '<div class="alert alert-danger">';
                    echo htmlspecialchars($fieldName . ': ' . $errorMessage);
                    echo '</div>';
                }
            } else {
                echo '<div class="alert alert-danger">';
                echo htmlspecialchars($fieldName . ': ' . $fieldErrors);
                echo '</div>';
            }
        }
        */
    }
}

// Render the form
echo $form->render();
// or simply:
// echo $form;

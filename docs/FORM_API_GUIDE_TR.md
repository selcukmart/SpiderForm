# SpiderForm V2 - Form API Kullanım Kılavuzu

## Sorun: Neden Metodlar Çalışmıyor?

SpiderForm V2'de **iki farklı API** bulunmaktadır:

### 1. Stateless API (HTML String) - `build()`
```php
$html = FormBuilder::create('my-form')
    ->addText('name')->add()
    ->build(); // Returns string

echo $html; // HTML çıktısı
```

**Bu API ile şunlar ÇALIŞMAZ:**
- ❌ `$html->setData()`
- ❌ `$html->submit()`
- ❌ `$html->isValid()`
- ❌ `$html->getData()`
- ❌ `$html->getErrorList()`

### 2. Stateful API (Form Object) - `buildForm()`
```php
$form = FormBuilder::create('my-form')
    ->addText('name')->add()
    ->buildForm(); // Returns Form object

$form->setData(['name' => 'John']);
$form->submit($_POST);
if ($form->isValid()) {
    $data = $form->getData();
}
```

**Bu API ile şunlar ÇALIŞIR:**
- ✅ `$form->setData()`
- ✅ `$form->submit()`
- ✅ `$form->isValid()`
- ✅ `$form->getData()`
- ✅ `$form->getErrorList()`

## Tam Örnek: Form Submission İşlemi

```php
<?php
use SpiderForm\V2\Builder\FormBuilder;
use SpiderForm\V2\Renderer\SmartyRenderer;
use SpiderForm\V2\Theme\Bootstrap3Theme;

// Setup
$renderer = new SmartyRenderer(/* ... */);
$theme = new Bootstrap3Theme();

// Build Form Object (NOT HTML String!)
$form = FormBuilder::create('user-form')
    ->setAction('')
    ->setMethod('POST')
    ->setRenderer($renderer)
    ->setTheme($theme)

    // Add fields
    ->addText('name', 'İsim')
        ->required()
        ->maxLength(100)
        ->add()

    ->addEmail('email', 'E-posta')
        ->required()
        ->add()

    ->addSubmit('save', 'Kaydet')

    // IMPORTANT: Use buildForm() here!
    ->buildForm();

// Set data for edit mode
if ($isEdit) {
    $form->setData($existingData);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form->submit($_POST);

    if ($form->isValid()) {
        // Form valid - get data
        $data = $form->getData();

        // Save to database
        // ...

        // Redirect
        header('Location: /success');
        exit;
    } else {
        // Form invalid - show errors
        $errorList = $form->getErrorList(deep: true);

        foreach ($errorList as $error) {
            echo '<div class="alert alert-danger">';
            echo htmlspecialchars($error->getMessage());
            echo '</div>';
        }
    }
}

// Render the form
echo $form->render();
// or simply: echo $form;
```

## API Karşılaştırması

| Özellik | `build()` | `buildForm()` |
|---------|-----------|---------------|
| Dönen Değer | `string` (HTML) | `FormInterface` (Object) |
| setData() | ❌ Yok | ✅ Var |
| submit() | ❌ Yok | ✅ Var |
| isValid() | ❌ Yok | ✅ Var |
| getData() | ❌ Yok | ✅ Var |
| getErrorList() | ❌ Yok | ✅ Var |
| render() | ✅ (otomatik) | ✅ (manuel) |
| Kullanım Senaryosu | Basit formlar | Form validation gerekli |

## Hata Alma Yöntemleri

### Yöntem 1: ErrorList (v2.9.0 - Önerilen)
```php
$errorList = $form->getErrorList(deep: true);

foreach ($errorList as $error) {
    echo $error->getMessage();
    echo $error->getLevel(); // ERROR, WARNING, INFO
    echo $error->getPath();  // Field path
}

// Array olarak al
$errorsArray = $form->getErrorsAsArray(deep: true);

// Flat array (dot notation)
$errorsFlat = $form->getErrorsFlattened(deep: true);
// ['email' => 'Email is required', 'address.street' => 'Street is required']
```

### Yöntem 2: Legacy Array
```php
$errors = $form->getErrors(deep: true);

foreach ($errors as $fieldName => $fieldErrors) {
    foreach ($fieldErrors as $error) {
        echo "$fieldName: $error";
    }
}
```

## Form Lifecycle (İş Akışı)

```
1. Build Form
   $form = FormBuilder::create()->...->buildForm()

2. Set Initial Data (Edit mode için)
   $form->setData($data)

3. Submit Form
   $form->submit($_POST)

4. Validate
   if ($form->isValid())

5. Get Data
   $data = $form->getData()

6. Render
   echo $form->render()
```

## Migration Guide (Eski Koddan Yeni Koda)

### ÖNCE (Yanlış):
```php
$form = FormBuilder::create('my-form')
    ->addText('name')->add()
    ->build(); // ❌ String döner

// Bunlar çalışmaz:
$form->setData($data);    // ❌ Fatal error
$form->submit($_POST);    // ❌ Fatal error
$form->isValid();         // ❌ Fatal error
```

### SONRA (Doğru):
```php
$form = FormBuilder::create('my-form')
    ->addText('name')->add()
    ->buildForm(); // ✅ Form object döner

// Bunlar çalışır:
$form->setData($data);    // ✅ OK
$form->submit($_POST);    // ✅ OK
$form->isValid();         // ✅ OK
```

## Önemli Notlar

1. **`buildForm()` kullanın**, `build()` değil!
2. Renderer ve Theme `buildForm()` çağrısından **önce** set edilmeli
3. `render()` otomatik olarak çağrılmaz, manuel yapmalısınız
4. `getErrorList()` v2.9.0'da eklendi, daha zengin error handling sağlar
5. Form object `__toString()` magic method'u sayesinde direkt echo edilebilir

## İlgili Dosyalar

- FormBuilder: `src/V2/Builder/FormBuilder.php`
  - `build()`: Line 1309 (HTML string döner)
  - `buildForm()`: Line 2051 (Form object döner)

- Form: `src/V2/Form/Form.php`
  - `setData()`: Line 131
  - `submit()`: Line 206
  - `isValid()`: Line 241
  - `getData()`: Line 162
  - `getErrorList()`: Line 307

## Örnek Dosyalar

- Doğru kullanım: `examples/corrected_form_example.php`
- Symfony-style kullanım: `examples/form_type_example.php`

# ⚠️ This Repository Has Moved

## FormGenerator is now SpiderForm!

This repository has been **abandoned** and is no longer maintained. The project has been **rebranded and relocated** to a new repository.

---

## 🕷️ New Repository

**SpiderForm** - Modern PHP Form Builder

**New Location:** [https://github.com/selcukmart/SpiderForm](https://github.com/selcukmart/SpiderForm)

---

## 📦 Installation

### Use the new package name:

```bash
composer require selcukmart/spider-form
```

The old package name `selcukmart/form-generator` is automatically replaced by `spider-form` in composer.

---

## 🔄 Migration Guide

### For Existing Users

**Good News!** The migration is seamless:

1. **Update your composer.json:**
   ```json
   {
       "require": {
           "selcukmart/spider-form": "^3.0"
       }
   }
   ```

2. **Update your code:**
   - V1 Users: Replace `FormGenerator` namespace with `SpiderForm`
   - V2/V3 Users: Most classes have been renamed from `FormGenerator*` to `SpiderForm*`

3. **Run composer update:**
   ```bash
   composer update selcukmart/spider-form
   ```

### Breaking Changes

All class names have been updated:

**V1 (Legacy):**
- `FormGeneratorDirector` → `SpiderFormDirector`
- `FormGeneratorBuilder\*` → `SpiderFormBuilder\*`
- `FormGeneratorInputTypes\*` → `SpiderFormInputTypes\*`
- `FormGeneratorClassTraits\*` → `SpiderFormClassTraits\*`

**V2/V3 Integrations:**
- `FormGeneratorBundle` → `SpiderFormBundle` (Symfony)
- `FormGeneratorExtension` → `SpiderFormExtension` (Twig)
- `FormGeneratorType` → `SpiderFormType` (Symfony Form)
- `FormGeneratorServiceProvider` → `SpiderFormServiceProvider` (Laravel)
- `FormGeneratorBladeDirectives` → `SpiderFormBladeDirectives` (Blade)
- `FormGeneratorPlugin` → `SpiderFormPlugin` (Smarty)

**Twig Extension:**
- Extension name changed from `formgenerator` to `spiderform`

---

## ✨ Why SpiderForm?

The rebranding reflects the library's evolution:

- 🕸️ **Weaving Perfect Forms** - Like a spider weaves its web
- 🎯 **Modern & Memorable** - A unique, brandable name
- 🚀 **Production Ready** - 500+ tests, enterprise-grade features
- 🌍 **Internationalization** - Multi-language support (v3.0.0)
- 🔒 **Auto CSRF Protection** - Security by default (v3.0.0)

---

## 📚 New Features in SpiderForm v3.0.0

### 🌍 Internationalization
```php
$translator = new FormTranslator('en_US');
$translator->addLoader('php', new PhpLoader());
$translator->loadTranslationFile(__DIR__ . '/translations/forms.en_US.php', 'en_US', 'php');

FormBuilder::setTranslator($translator);

$form = FormBuilder::create('contact')
    ->addText('name', 'form.label.name') // Auto-translated!
    ->build();
```

### 🔒 Automatic CSRF Protection
```php
$form = FormBuilder::create('user_form')
    ->setCsrfTokenId('user_form') // Automatic protection!
    ->addText('username')->required()->add()
    ->build();
```

### ⚠️ Advanced Error Handling
```php
// Three error levels: ERROR, WARNING, INFO
$form->addError('username', 'Invalid format', ErrorLevel::ERROR);
$form->addWarning('email', 'Email looks suspicious');
$form->addInfo('password', 'Password strength: medium');

// Get errors with metadata
$errors = $form->getErrorList(deep: true);
```

### 🎭 Dynamic Forms
```php
$form->addEventListener(FormEvents::PRE_SET_DATA, function($event) {
    $form = $event->getForm();
    $data = $event->getData();

    if ($data['type'] === 'premium') {
        $form->add('premium_features', FormBuilder::select(...));
    }
});
```

---

## 📖 Documentation

- **[Complete Documentation](https://github.com/selcukmart/SpiderForm/blob/main/README_V3.md)**
- **[V2 Documentation](https://github.com/selcukmart/SpiderForm/blob/main/README_V2.md)**
- **[Migration Guide](https://github.com/selcukmart/SpiderForm/blob/main/UPGRADE.md)**
- **[Changelog](https://github.com/selcukmart/SpiderForm/blob/main/CHANGELOG.md)**

---

## 🤝 Support

For issues, questions, or contributions, please visit the new repository:

**[https://github.com/selcukmart/SpiderForm](https://github.com/selcukmart/SpiderForm)**

---

## 👨‍💻 Author

**selcukmart**
- Email: admin@hostingdevi.com
- GitHub: [@selcukmart](https://github.com/selcukmart)

---

## 📝 License

MIT License - The license remains unchanged in the new repository.

---

**⚠️ This repository will be archived soon. Please update your bookmarks and dependencies.**

**🕷️ Welcome to SpiderForm - Weaving Perfect Forms for Modern PHP!**

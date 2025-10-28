<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use FormGenerator\V2\Builder\FormBuilder;
use FormGenerator\V2\Renderer\TwigRenderer;
use FormGenerator\V2\Theme\Bootstrap5Theme;
use FormGenerator\V2\Event\FieldEvent;

/**
 * Query String Based Conditional Rendering Examples
 *
 * Demonstrates how to conditionally render form fields based on
 * query string parameters using the event-driven dependency system.
 */
class QueryStringFormController extends AbstractController
{
    #[Route('/form/query-based', name: 'query_based_form')]
    public function queryBasedForm(Request $request): Response
    {
        $renderer = new TwigRenderer($this->getParameter('kernel.project_dir') . '/templates/forms');
        $theme = new Bootstrap5Theme();

        // =============================================================================
        // Yöntem 1: Hidden Field + dependsOn
        // Query string'i hidden field olarak ekle ve ona bağımlılık oluştur
        // =============================================================================

        $form1 = FormBuilder::create('query_method1_form')
            ->setRenderer($renderer)
            ->setTheme($theme)
            ->setAction($this->generateUrl('query_based_form'));

        // Query string'den değerleri al
        $mode = $request->query->get('mode', ''); // ?mode=advanced
        $userRole = $request->query->get('user_role', ''); // ?user_role=admin

        // Hidden field olarak ekle
        $form1->addHidden('query_mode', $mode)
            ->isDependency('query_mode')
            ->add();

        $form1->addHidden('query_user_role', $userRole)
            ->isDependency('query_user_role')
            ->add();

        // Normal form alanları
        $form1->addText('username', 'Username')
            ->required()
            ->add();

        // Sadece ?mode=advanced varsa göster
        $form1->addText('advanced_settings', 'Advanced Settings')
            ->dependsOn('query_mode', 'advanced')
            ->placeholder('Only visible with ?mode=advanced')
            ->add();

        // Sadece ?user_role=admin varsa göster
        $form1->addText('admin_panel', 'Admin Panel Access')
            ->dependsOn('query_user_role', 'admin')
            ->placeholder('Only visible with ?user_role=admin')
            ->add();

        $html1 = $form1->build();

        // =============================================================================
        // Yöntem 2: Server-Side Evaluation + Form Data
        // Query string'i form data'ya ekle ve server-side evaluation kullan
        // =============================================================================

        $form2 = FormBuilder::create('query_method2_form')
            ->setRenderer($renderer)
            ->setTheme($theme)
            ->enableServerSideDependencyEvaluation() // PHP-side rendering
            ->setData([
                'mode' => $mode,
                'user_role' => $userRole,
            ])
            ->setAction($this->generateUrl('query_based_form'));

        $form2->addHidden('mode', $mode)
            ->isDependency()
            ->add();

        $form2->addHidden('user_role', $userRole)
            ->isDependency()
            ->add();

        $form2->addEmail('email', 'Email')
            ->required()
            ->add();

        // Bu alan sadece ?mode=advanced varsa HTML'de render edilecek
        $form2->addTextarea('advanced_config', 'Advanced Configuration')
            ->dependsOn('mode', 'advanced')
            ->rows(5)
            ->add();

        // Bu alan sadece ?user_role=admin varsa HTML'de render edilecek
        $form2->addCheckbox('debug_mode', 'Debug Mode')
            ->dependsOn('user_role', 'admin')
            ->options(['1' => 'Enable Debug Mode'])
            ->add();

        $html2 = $form2->build();

        // =============================================================================
        // Yöntem 3: onPreRender Event ile Custom Logic
        // En esnek yöntem - query string'i event içinde kontrol et
        // =============================================================================

        $form3 = FormBuilder::create('query_method3_form')
            ->setRenderer($renderer)
            ->setTheme($theme)
            ->enableServerSideDependencyEvaluation()
            ->setAction($this->generateUrl('query_based_form'));

        $form3->addText('product_name', 'Product Name')
            ->required()
            ->add();

        // Custom query string kontrolü
        $form3->addText('premium_feature', 'Premium Feature')
            ->onPreRender(function(FieldEvent $event) use ($request) {
                // Query string kontrolü
                $isPremium = $request->query->get('tier', '') === 'premium';

                if (!$isPremium) {
                    // Premium değilse alanı render etme
                    $event->getField()->wrapperAttributes(['style' => 'display: none;']);
                    $event->getField()->disabled(true);
                }
            })
            ->add();

        $html3 = $form3->build();

        // =============================================================================
        // Yöntem 4: onDependencyCheck ile Complex Logic
        // Birden fazla query string kombinasyonu
        // =============================================================================

        $form4 = FormBuilder::create('query_method4_form')
            ->setRenderer($renderer)
            ->setTheme($theme)
            ->enableServerSideDependencyEvaluation()
            ->setData([
                'country' => $request->query->get('country', ''),
                'role' => $request->query->get('role', ''),
            ])
            ->setAction($this->generateUrl('query_based_form'));

        $form4->addHidden('country', $request->query->get('country', ''))
            ->isDependency()
            ->add();

        $form4->addHidden('role', $request->query->get('role', ''))
            ->isDependency()
            ->add();

        $form4->addText('company_name', 'Company Name')
            ->required()
            ->add();

        // Karmaşık koşul: ?country=US AND ?role=admin
        $form4->addText('tax_settings', 'US Tax Settings')
            ->dependsOn('country', 'US')
            ->onDependencyCheck(function(FieldEvent $event) use ($request) {
                // Hem country=US hem de role=admin olmalı
                $country = $request->query->get('country', '');
                $role = $request->query->get('role', '');

                $visible = ($country === 'US' && $role === 'admin');
                $event->setVisible($visible);

                if ($visible) {
                    $event->getField()->required(true);
                }
            })
            ->add();

        $html4 = $form4->build();

        // =============================================================================
        // Yöntem 5: Multiple Query Params ile Nested Dependencies
        // =============================================================================

        $form5 = FormBuilder::create('query_method5_form')
            ->setRenderer($renderer)
            ->setTheme($theme)
            ->enableServerSideDependencyEvaluation()
            ->setData([
                'feature_flag' => $request->query->get('feature', ''),
                'beta_access' => $request->query->get('beta', ''),
            ])
            ->setAction($this->generateUrl('query_based_form'));

        $form5->addHidden('feature_flag', $request->query->get('feature', ''))
            ->isDependency()
            ->add();

        $form5->addHidden('beta_access', $request->query->get('beta', ''))
            ->isDependency()
            ->add();

        $form5->addText('email', 'Email')
            ->required()
            ->email()
            ->add();

        // Level 1: ?feature=new_ui
        $form5->addSelect('ui_theme', 'UI Theme')
            ->dependsOn('feature_flag', 'new_ui')
            ->options([
                '' => '-- Select Theme --',
                'dark' => 'Dark Theme',
                'light' => 'Light Theme',
                'auto' => 'Auto',
            ])
            ->isDependency('ui_theme') // Becomes controller for next level
            ->add();

        // Level 2: ?feature=new_ui AND ui_theme=dark
        $form5->addCheckbox('dark_mode_options', 'Dark Mode Options')
            ->dependsOn('ui_theme', 'dark')
            ->options([
                'high_contrast' => 'High Contrast',
                'reduce_blue_light' => 'Reduce Blue Light',
            ])
            ->onShow(function(FieldEvent $event) {
                // Event triggered when shown
                error_log('Dark mode options shown - nested dependency met');
            })
            ->add();

        // ?beta=enabled
        $form5->addTextarea('beta_feedback', 'Beta Feedback')
            ->dependsOn('beta_access', 'enabled')
            ->placeholder('Your feedback about beta features...')
            ->rows(4)
            ->add();

        $html5 = $form5->build();

        // =============================================================================
        // Yöntem 6: Validation with Query String
        // Query string'e göre farklı validation kuralları
        // =============================================================================

        $form6 = FormBuilder::create('query_method6_form')
            ->setRenderer($renderer)
            ->setTheme($theme)
            ->setAction($this->generateUrl('query_based_form'));

        $strictMode = $request->query->get('strict', '') === 'true';

        $form6->addText('username', 'Username')
            ->required()
            ->minLength($strictMode ? 8 : 3) // Strict mode'da daha uzun
            ->onPreRender(function(FieldEvent $event) use ($strictMode) {
                if ($strictMode) {
                    $event->getField()
                        ->helpText('Strict mode enabled: minimum 8 characters required')
                        ->addClass('border-danger');
                }
            })
            ->add();

        $form6->addPassword('password', 'Password')
            ->required()
            ->minLength($strictMode ? 12 : 6)
            ->add();

        // Sadece strict mode'da göster
        if ($strictMode) {
            $form6->addPassword('password_confirmation', 'Confirm Password')
                ->required()
                ->confirmed('password')
                ->add();
        }

        $html6 = $form6->build();

        // Render all forms
        return $this->render('query_based_forms.html.twig', [
            'form1' => $html1,
            'form2' => $html2,
            'form3' => $html3,
            'form4' => $html4,
            'form5' => $html5,
            'form6' => $html6,
            'query_params' => [
                'mode' => $mode,
                'user_role' => $userRole,
                'country' => $request->query->get('country', ''),
                'role' => $request->query->get('role', ''),
                'feature' => $request->query->get('feature', ''),
                'beta' => $request->query->get('beta', ''),
                'strict' => $request->query->get('strict', ''),
            ],
        ]);
    }

    #[Route('/form/dynamic-pricing', name: 'dynamic_pricing_form')]
    public function dynamicPricingForm(Request $request): Response
    {
        /**
         * Real-world Example: Dynamic Pricing Form
         *
         * Query strings determine which pricing fields to show
         * ?plan=basic   -> Show basic fields
         * ?plan=pro     -> Show pro fields
         * ?plan=enterprise -> Show enterprise fields
         * ?promo=SAVE20 -> Show discount field
         */

        $renderer = new TwigRenderer($this->getParameter('kernel.project_dir') . '/templates/forms');
        $theme = new Bootstrap5Theme();

        $plan = $request->query->get('plan', 'basic');
        $promoCode = $request->query->get('promo', '');

        $form = FormBuilder::create('pricing_form')
            ->setRenderer($renderer)
            ->setTheme($theme)
            ->enableServerSideDependencyEvaluation()
            ->setData([
                'plan' => $plan,
                'promo_code' => $promoCode,
            ])
            ->setAction($this->generateUrl('dynamic_pricing_form'));

        // Hidden fields for query params
        $form->addHidden('plan', $plan)
            ->isDependency()
            ->add();

        $form->addHidden('promo_code', $promoCode)
            ->isDependency()
            ->add();

        // Common fields
        $form->addText('company_name', 'Company Name')
            ->required()
            ->add();

        $form->addEmail('billing_email', 'Billing Email')
            ->required()
            ->email()
            ->add();

        // Basic plan fields (?plan=basic)
        $form->addNumber('users_count', 'Number of Users')
            ->dependsOn('plan', 'basic')
            ->required()
            ->min(1)
            ->max(10)
            ->helpText('Basic plan: max 10 users')
            ->add();

        // Pro plan fields (?plan=pro)
        $form->addNumber('users_count_pro', 'Number of Users')
            ->dependsOn('plan', 'pro')
            ->required()
            ->min(1)
            ->max(100)
            ->helpText('Pro plan: max 100 users')
            ->add();

        $form->addCheckbox('integrations', 'Integrations')
            ->dependsOn('plan', ['pro', 'enterprise'])
            ->options([
                'slack' => 'Slack',
                'github' => 'GitHub',
                'jira' => 'Jira',
            ])
            ->add();

        // Enterprise plan fields (?plan=enterprise)
        $form->addText('dedicated_manager', 'Dedicated Account Manager')
            ->dependsOn('plan', 'enterprise')
            ->placeholder('Your account manager name')
            ->add();

        $form->addCheckbox('enterprise_features', 'Enterprise Features')
            ->dependsOn('plan', 'enterprise')
            ->options([
                'sso' => 'Single Sign-On (SSO)',
                'custom_domain' => 'Custom Domain',
                'api_access' => 'API Access',
                'sla' => '24/7 SLA Support',
            ])
            ->add();

        // Promo code field (?promo=SAVE20)
        $form->addText('promo_display', 'Promo Code Applied')
            ->dependsOn('promo_code', ['SAVE20', 'WELCOME10', 'ANNUAL50'])
            ->value($promoCode)
            ->readonly()
            ->helpText('You have a valid promo code!')
            ->addClass('text-success fw-bold')
            ->onShow(function(FieldEvent $event) use ($promoCode) {
                // Log promo code usage
                error_log("Promo code applied: {$promoCode}");
            })
            ->add();

        $form->addSubmit('subscribe', 'Subscribe Now');

        return $this->render('pricing_form.html.twig', [
            'form' => $form->build(),
            'plan' => $plan,
            'promo_code' => $promoCode,
        ]);
    }
}

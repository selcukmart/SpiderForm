<?php


namespace SpiderForm\SpiderFormBuilder;


use SpiderForm\SpiderFormDirector;
use Helpers\Dom;
use SpiderForm\Tools\DependencyManagerV1;
use Helpers\Classes;

abstract class AbstractSpiderFormBuilder
{
    private static
        $instances = [],
        $classnames = [];

    const
        DEFAULT_TEMPLATE_CAPSULE_TPL = 'INPUT_CAPSULE',
        DEFAULT_FORM_CAPSULE_TPL = 'FORM_CAPSULE',
        MAX_RECURSION_DEPTH = 10;
    protected
        $input_capsule,
        $form_capsule,
        $formGeneratorDirector,
        $generator_array,
        $class_names,
        $input_parts,
        $template,
        $filter,
        $recursion_depth = 0,
        $without_help_block = [
        'hidden',
        'static_text',
        'file'
    ];

    public function __construct(SpiderFormDirector $formGenerator)
    {
        $this->formGeneratorDirector = $formGenerator;
        $this->generator_array = $this->formGeneratorDirector->getGeneratorArray();
        $this->class_names = [];
        $this->filter = $this->formGeneratorDirector->getFilter();

    }

    public static function getInstance(SpiderFormDirector $formGenerator): AbstractSpiderFormBuilder
    {
        $class = static::class;
        if (!isset(self::$instances[SpiderFormDirector::getInstanceCount()][$class])) {
            self::$instances[SpiderFormDirector::getInstanceCount()][$class] = new static($formGenerator);
        }

        return self::$instances[SpiderFormDirector::getInstanceCount()][$class];
    }

    public function buildHtmlOutput($inputs = null, $parent_group = null): void
    {
        if (is_null($inputs)) {
            $inputs = $this->formGeneratorDirector->getInputs();
        }

        // Prevent infinite recursion
        if ($this->recursion_depth >= self::MAX_RECURSION_DEPTH) {
            error_log('SpiderForm: Maximum recursion depth reached. Possible infinite loop in form structure.');
            return;
        }

        foreach ($inputs as $group => $input) {
            if (!is_null($parent_group)) {
                $input['group'] = $parent_group;
            }

            if ($this->isItExceptionalSituation($input)) {
                continue;
            }

            $input['input-id'] = $this->formGeneratorDirector->inputID($input);
            $will_filtered = $this->filter->willFiltered($input, $group);
            if ($will_filtered) {
                continue;
            }
            $this->sendDataForRender($input, $group);
        }
    }

    public function createForm($item)
    {
        $this->formGeneratorDirector->setHtmlOutputType('form');
        $this->sendDataForRender($item, '');
    }

    protected function getHelpBlock(array $item): string
    {
        $str = '';
        if (isset($item['help_block']) && !empty($item['help_block']) && !in_array($item['type'], $this->without_help_block, true)) {
            $str = $this->formGeneratorDirector->renderToHtml($item, 'HELP_BLOCK', true);
        }
        return $str;

    }

    protected function prepareInputParts(array $input): array
    {
        $input_factory_class = $this->getInputFactoryClassName($input['type']);
        $input_factory = $input_factory_class::getInstance($this->formGeneratorDirector);

        $this->input_parts = $input_factory->createInput($input);
        if (!$this->isForm($input['type'])) {
            $this->addHelpBlockToInputparts($input);
            $this->addInputCapsuleAttributes2InputParts($input);
        }

        return $this->input_parts;
    }


    protected function sendDataForRender($input, $group): void
    {
        if (!empty($group) && !is_numeric($group) && is_string($group)) {
            // Check if $input is an array before recursing
            if (!is_array($input)) {
                return;
            }

            unset($input['input-id']);
            $this->recursion_depth++;
            $this->buildHtmlOutput($input, $group);
            $this->recursion_depth--;
            return;
        }
        $this->prepareInputParts($input);
        $input_capsule = $this->detectInputCapsule($input);

        $this->formGeneratorDirector->renderToHtml($this->input_parts, $input_capsule);
    }

    /**
     * @param $type
     * @return string
     * @author selcukmart
     * 8.02.2022
     * 10:57
     */
    protected function getInputFactoryClassName($type): string
    {
        if (isset(self::$classnames[$type])) {
            return self::$classnames[$type];
        }
        if (isset($this->class_names[$type])) {
            $class_name = $this->class_names[$type];
        } else {
            $class_name = Classes::prepareFromString($type);
            if ($class_name === 'Buttons') {
                $class_name = 'ButtonGroup';
            }
            $this->class_names[$type] = $class_name;
        }

        $input_factory_class = $this->formGeneratorDirector->getInputTypesNamespace() . $class_name;

        if (!class_exists($input_factory_class)) {
            $input_factory_class = $this->formGeneratorDirector->getInputTypesNamespace() . 'Generic';
        }
        self::$classnames[$type] = $input_factory_class;
        return $input_factory_class;
    }

    /**
     * @param array $item
     * @author selcukmart
     * 8.02.2022
     * 10:59
     */
    protected function addHelpBlockToInputparts(array $item): void
    {
        $help_block = $this->getHelpBlock($item);
        if (!isset($this->input_parts['input_belove_desc'])) {
            $this->input_parts['input_belove_desc'] = '';
        }

        $this->input_parts['input_belove_desc'] .= $help_block;
    }

    /**
     * @param array $item
     * @author selcukmart
     * 8.02.2022
     * 11:00
     */
    protected function addInputCapsuleAttributes2InputParts(array $item): void
    {
        if (!isset($this->input_parts['input_capsule_attributes'])) {
            $this->input_parts['input_capsule_attributes'] = '';
        }

        $this->input_parts['input_capsule_attributes'] .= DependencyManagerV1::dependend($item);
        $arr = [
            'attributes' => [
                'id' => $item['input-id']
            ]
        ];

        $this->input_parts['input_capsule_attributes'] .= Dom::makeAttr($arr);
    }

    /**
     * @param $item
     * @return bool
     * @author selcukmart
     * 8.02.2022
     * 11:02
     */
    protected function isItExceptionalSituation($item): bool
    {
        return isset($item['type'], $item['label']) && $item['type'] === 'form_section' && empty($item['label']);
    }

    public function __destruct()
    {

    }

    /**
     * @return mixed
     */
    public function getInputCapsule()
    {
        if (!is_null($this->input_capsule)) {
            return $this->input_capsule;
        }
        $this->input_capsule = $this->generator_array['input']['capsule_template'] ?? self::DEFAULT_TEMPLATE_CAPSULE_TPL;
        return $this->input_capsule;
    }

    /**
     * @return mixed
     */
    public function getFormCapsule()
    {
        if (!is_null($this->form_capsule)) {
            return $this->form_capsule;
        }
        $this->form_capsule = $this->generator_array['form']['capsule_template'] ?? self::DEFAULT_FORM_CAPSULE_TPL;
        return $this->form_capsule;
    }

    /**
     * @param $type
     * @return bool
     * @author selcukmart
     * 8.02.2022
     * 16:53
     */
    protected function isForm($type): bool
    {
        return $type === 'form';
    }

    /**
     * @param $input
     * @return mixed|string
     * @author selcukmart
     * 9.02.2022
     * 11:13
     */
    protected function detectInputCapsule($input)
    {
        if (isset($input['capsule_template'])) {
            $input_capsule = $input['capsule_template'];
        } else {
            $input_capsule = $this->getInputCapsule();

            if ($this->isForm($input['type'])) {
                $input_capsule = $this->getFormCapsule();
            }
        }
        return $input_capsule;
    }
}
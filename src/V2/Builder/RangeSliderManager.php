<?php

declare(strict_types=1);

namespace FormGenerator\V2\Builder;

/**
 * RangeSliderManager - Pure Vanilla JS Range Slider
 *
 * Features:
 * - Single and dual handle sliders
 * - Custom min/max values
 * - Step intervals
 * - Value labels and tooltips
 * - Multi-language support
 * - Custom formatting
 * - Vertical and horizontal orientation
 *
 * @author selcukmart
 * @since 2.0.0
 */
class RangeSliderManager
{
    private static array $renderedSliders = [];

    /**
     * Default locales for common languages
     */
    public const LOCALE_EN = [
        'min' => 'Min',
        'max' => 'Max',
        'from' => 'From',
        'to' => 'To',
    ];

    public const LOCALE_TR = [
        'min' => 'Min',
        'max' => 'Maks',
        'from' => 'Başlangıç',
        'to' => 'Bitiş',
    ];

    public const LOCALE_DE = [
        'min' => 'Min',
        'max' => 'Max',
        'from' => 'Von',
        'to' => 'Bis',
    ];

    public const LOCALE_FR = [
        'min' => 'Min',
        'max' => 'Max',
        'from' => 'De',
        'to' => 'À',
    ];

    public const LOCALE_ES = [
        'min' => 'Mín',
        'max' => 'Máx',
        'from' => 'Desde',
        'to' => 'Hasta',
    ];

    /**
     * Generate range slider script
     *
     * @param string $inputId Input field ID
     * @param array $options Configuration options
     * @param bool $includeScript Whether to include script tags
     * @return string Generated JavaScript code
     */
    public static function generateScript(string $inputId, array $options = [], bool $includeScript = true): string
    {
        // Prevent duplicate rendering
        if (isset(self::$renderedSliders[$inputId])) {
            return '';
        }
        self::$renderedSliders[$inputId] = true;

        $defaultOptions = [
            'min' => 0,
            'max' => 100,
            'step' => 1,
            'value' => null, // Single value
            'from' => null,  // Dual slider: start value
            'to' => null,    // Dual slider: end value
            'dual' => false, // Enable dual handles
            'locale' => self::LOCALE_EN,
            'prefix' => '',  // e.g., '$', '€'
            'suffix' => '',  // e.g., '%', 'kg'
            'showValue' => true,
            'showTooltip' => true,
            'vertical' => false,
            'rtl' => false, // Right-to-left support
        ];

        $config = array_merge($defaultOptions, $options);

        // Auto-detect dual mode if from/to are set
        if ($config['from'] !== null || $config['to'] !== null) {
            $config['dual'] = true;
        }

        $configJson = json_encode($config, JSON_HEX_APOS | JSON_HEX_QUOT);

        $script = self::generateRangeSliderJS($inputId, $configJson);

        if ($includeScript) {
            return "<script>\n{$script}\n</script>";
        }

        return $script;
    }

    /**
     * Generate the complete range slider JavaScript
     */
    private static function generateRangeSliderJS(string $inputId, string $configJson): string
    {
        return <<<JAVASCRIPT
(function() {
    'use strict';

    const config = {$configJson};
    const input = document.getElementById('{$inputId}');

    if (!input) {
        console.error('RangeSlider: Input element not found:', '{$inputId}');
        return;
    }

    // Create RangeSlider class
    class RangeSlider {
        constructor(inputElement, options) {
            this.input = inputElement;
            this.options = options;
            this.isDragging = false;
            this.currentHandle = null;

            // Initialize values
            if (this.options.dual) {
                this.valueFrom = this.options.from !== null ? this.options.from : this.options.min;
                this.valueTo = this.options.to !== null ? this.options.to : this.options.max;
            } else {
                this.value = this.options.value !== null ? this.options.value : this.options.min;
            }

            this.init();
        }

        init() {
            // Hide original input
            this.input.style.display = 'none';

            // Create slider container
            this.createSlider();

            // Bind events
            this.bindEvents();

            // Initial update
            this.updateSlider();
        }

        createSlider() {
            this.container = document.createElement('div');
            this.container.className = 'range-slider' + (this.options.vertical ? ' range-slider-vertical' : '');

            let html = '';

            // Value display (top)
            if (this.options.showValue && !this.options.vertical) {
                html += '<div class="range-slider-values">';
                if (this.options.dual) {
                    html += '<span class="range-slider-value-from"></span>';
                    html += '<span class="range-slider-value-to"></span>';
                } else {
                    html += '<span class="range-slider-value-single"></span>';
                }
                html += '</div>';
            }

            // Slider track
            html += '<div class="range-slider-track">';
            html += '<div class="range-slider-range"></div>';

            // Handles
            if (this.options.dual) {
                html += '<div class="range-slider-handle range-slider-handle-from" data-handle="from">';
                if (this.options.showTooltip) {
                    html += '<span class="range-slider-tooltip"></span>';
                }
                html += '</div>';
                html += '<div class="range-slider-handle range-slider-handle-to" data-handle="to">';
                if (this.options.showTooltip) {
                    html += '<span class="range-slider-tooltip"></span>';
                }
                html += '</div>';
            } else {
                html += '<div class="range-slider-handle" data-handle="single">';
                if (this.options.showTooltip) {
                    html += '<span class="range-slider-tooltip"></span>';
                }
                html += '</div>';
            }

            html += '</div>';

            // Min/Max labels
            if (!this.options.vertical) {
                html += '<div class="range-slider-labels">';
                html += '<span class="range-slider-label-min">' + this.formatValue(this.options.min) + '</span>';
                html += '<span class="range-slider-label-max">' + this.formatValue(this.options.max) + '</span>';
                html += '</div>';
            }

            this.container.innerHTML = html;

            // Insert after input
            this.input.parentNode.insertBefore(this.container, this.input.nextSibling);

            // Get DOM references
            this.track = this.container.querySelector('.range-slider-track');
            this.range = this.container.querySelector('.range-slider-range');
            this.handles = this.container.querySelectorAll('.range-slider-handle');
        }

        bindEvents() {
            this.handles.forEach(handle => {
                handle.addEventListener('mousedown', (e) => this.startDrag(e, handle));
                handle.addEventListener('touchstart', (e) => this.startDrag(e, handle));
            });

            document.addEventListener('mousemove', (e) => this.onDrag(e));
            document.addEventListener('touchmove', (e) => this.onDrag(e));
            document.addEventListener('mouseup', () => this.stopDrag());
            document.addEventListener('touchend', () => this.stopDrag());

            // Track click to jump
            this.track.addEventListener('click', (e) => {
                if (!e.target.classList.contains('range-slider-handle')) {
                    this.jumpToPosition(e);
                }
            });

            // Keyboard support
            this.handles.forEach(handle => {
                handle.setAttribute('tabindex', '0');
                handle.addEventListener('keydown', (e) => this.handleKeyboard(e, handle));
            });
        }

        startDrag(e, handle) {
            this.isDragging = true;
            this.currentHandle = handle;
            handle.classList.add('active');
            e.preventDefault();
        }

        onDrag(e) {
            if (!this.isDragging) return;

            const position = this.getPosition(e);
            const value = this.positionToValue(position);

            if (this.options.dual) {
                const handleType = this.currentHandle.dataset.handle;
                if (handleType === 'from') {
                    this.valueFrom = Math.min(value, this.valueTo);
                } else {
                    this.valueTo = Math.max(value, this.valueFrom);
                }
            } else {
                this.value = value;
            }

            this.updateSlider();
        }

        stopDrag() {
            if (!this.isDragging) return;

            this.isDragging = false;
            if (this.currentHandle) {
                this.currentHandle.classList.remove('active');
            }
            this.currentHandle = null;

            this.updateInput();
        }

        jumpToPosition(e) {
            const position = this.getPosition(e);
            const value = this.positionToValue(position);

            if (this.options.dual) {
                // Jump closest handle
                const distFrom = Math.abs(value - this.valueFrom);
                const distTo = Math.abs(value - this.valueTo);

                if (distFrom < distTo) {
                    this.valueFrom = value;
                } else {
                    this.valueTo = value;
                }
            } else {
                this.value = value;
            }

            this.updateSlider();
            this.updateInput();
        }

        handleKeyboard(e, handle) {
            const step = e.shiftKey ? this.options.step * 10 : this.options.step;
            let updated = false;

            if (e.key === 'ArrowRight' || e.key === 'ArrowUp') {
                e.preventDefault();
                if (this.options.dual) {
                    if (handle.dataset.handle === 'from') {
                        this.valueFrom = Math.min(this.valueFrom + step, this.valueTo);
                    } else {
                        this.valueTo = Math.min(this.valueTo + step, this.options.max);
                    }
                } else {
                    this.value = Math.min(this.value + step, this.options.max);
                }
                updated = true;
            } else if (e.key === 'ArrowLeft' || e.key === 'ArrowDown') {
                e.preventDefault();
                if (this.options.dual) {
                    if (handle.dataset.handle === 'from') {
                        this.valueFrom = Math.max(this.valueFrom - step, this.options.min);
                    } else {
                        this.valueTo = Math.max(this.valueTo - step, this.valueFrom);
                    }
                } else {
                    this.value = Math.max(this.value - step, this.options.min);
                }
                updated = true;
            }

            if (updated) {
                this.updateSlider();
                this.updateInput();
            }
        }

        getPosition(e) {
            const clientX = e.touches ? e.touches[0].clientX : e.clientX;
            const clientY = e.touches ? e.touches[0].clientY : e.clientY;
            const rect = this.track.getBoundingClientRect();

            if (this.options.vertical) {
                return ((rect.bottom - clientY) / rect.height) * 100;
            } else {
                return ((clientX - rect.left) / rect.width) * 100;
            }
        }

        positionToValue(position) {
            position = Math.max(0, Math.min(100, position));
            const range = this.options.max - this.options.min;
            let value = this.options.min + (range * position / 100);

            // Snap to step
            value = Math.round(value / this.options.step) * this.options.step;

            return Math.max(this.options.min, Math.min(this.options.max, value));
        }

        valueToPosition(value) {
            const range = this.options.max - this.options.min;
            return ((value - this.options.min) / range) * 100;
        }

        updateSlider() {
            if (this.options.dual) {
                const fromPos = this.valueToPosition(this.valueFrom);
                const toPos = this.valueToPosition(this.valueTo);

                const handleFrom = this.container.querySelector('.range-slider-handle-from');
                const handleTo = this.container.querySelector('.range-slider-handle-to');

                if (this.options.vertical) {
                    handleFrom.style.bottom = fromPos + '%';
                    handleTo.style.bottom = toPos + '%';
                    this.range.style.bottom = fromPos + '%';
                    this.range.style.height = (toPos - fromPos) + '%';
                } else {
                    handleFrom.style.left = fromPos + '%';
                    handleTo.style.left = toPos + '%';
                    this.range.style.left = fromPos + '%';
                    this.range.style.width = (toPos - fromPos) + '%';
                }

                // Update tooltips
                if (this.options.showTooltip) {
                    handleFrom.querySelector('.range-slider-tooltip').textContent = this.formatValue(this.valueFrom);
                    handleTo.querySelector('.range-slider-tooltip').textContent = this.formatValue(this.valueTo);
                }

                // Update value display
                if (this.options.showValue) {
                    const valueFrom = this.container.querySelector('.range-slider-value-from');
                    const valueTo = this.container.querySelector('.range-slider-value-to');
                    if (valueFrom) valueFrom.textContent = this.options.locale.from + ': ' + this.formatValue(this.valueFrom);
                    if (valueTo) valueTo.textContent = this.options.locale.to + ': ' + this.formatValue(this.valueTo);
                }
            } else {
                const pos = this.valueToPosition(this.value);
                const handle = this.handles[0];

                if (this.options.vertical) {
                    handle.style.bottom = pos + '%';
                    this.range.style.height = pos + '%';
                } else {
                    handle.style.left = pos + '%';
                    this.range.style.width = pos + '%';
                }

                // Update tooltip
                if (this.options.showTooltip) {
                    handle.querySelector('.range-slider-tooltip').textContent = this.formatValue(this.value);
                }

                // Update value display
                if (this.options.showValue) {
                    const valueDisplay = this.container.querySelector('.range-slider-value-single');
                    if (valueDisplay) valueDisplay.textContent = this.formatValue(this.value);
                }
            }
        }

        updateInput() {
            if (this.options.dual) {
                this.input.value = this.valueFrom + ',' + this.valueTo;
                this.input.dataset.from = this.valueFrom;
                this.input.dataset.to = this.valueTo;
            } else {
                this.input.value = this.value;
            }

            // Dispatch events
            const event = new Event('change', { bubbles: true });
            this.input.dispatchEvent(event);

            this.dispatchEvent('change', this.getValues());
        }

        formatValue(value) {
            return this.options.prefix + value + this.options.suffix;
        }

        getValues() {
            if (this.options.dual) {
                return {
                    from: this.valueFrom,
                    to: this.valueTo,
                    min: this.options.min,
                    max: this.options.max
                };
            } else {
                return {
                    value: this.value,
                    min: this.options.min,
                    max: this.options.max
                };
            }
        }

        setValue(value) {
            if (this.options.dual && typeof value === 'object') {
                this.valueFrom = value.from;
                this.valueTo = value.to;
            } else {
                this.value = value;
            }
            this.updateSlider();
            this.updateInput();
        }

        dispatchEvent(eventName, detail = {}) {
            const event = new CustomEvent('rangeslider:' + eventName, {
                detail: detail,
                bubbles: true
            });
            this.input.dispatchEvent(event);
        }

        destroy() {
            this.container.remove();
            this.input.style.display = '';
        }
    }

    // Initialize RangeSlider
    const slider = new RangeSlider(input, config);

    // Store instance globally for API access
    window.RangeSlider_{$inputId} = slider;

    // Add default styles if not already added
    if (!document.getElementById('rangeslider-styles')) {
        const style = document.createElement('style');
        style.id = 'rangeslider-styles';
        style.textContent = `
            .range-slider {
                padding: 20px 0;
            }
            .range-slider-values {
                display: flex;
                justify-content: space-between;
                margin-bottom: 12px;
                font-size: 14px;
                font-weight: 600;
            }
            .range-slider-track {
                position: relative;
                height: 6px;
                background: #e9ecef;
                border-radius: 3px;
                cursor: pointer;
            }
            .range-slider-range {
                position: absolute;
                height: 100%;
                background: #0d6efd;
                border-radius: 3px;
                pointer-events: none;
            }
            .range-slider-handle {
                position: absolute;
                top: 50%;
                transform: translate(-50%, -50%);
                width: 20px;
                height: 20px;
                background: #fff;
                border: 2px solid #0d6efd;
                border-radius: 50%;
                cursor: grab;
                transition: box-shadow 0.2s;
            }
            .range-slider-handle:hover,
            .range-slider-handle:focus {
                box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.25);
                outline: none;
            }
            .range-slider-handle.active {
                cursor: grabbing;
                box-shadow: 0 0 0 6px rgba(13, 110, 253, 0.25);
            }
            .range-slider-tooltip {
                position: absolute;
                bottom: 100%;
                left: 50%;
                transform: translateX(-50%);
                margin-bottom: 8px;
                padding: 4px 8px;
                background: #333;
                color: #fff;
                font-size: 12px;
                border-radius: 4px;
                white-space: nowrap;
                pointer-events: none;
            }
            .range-slider-tooltip::after {
                content: '';
                position: absolute;
                top: 100%;
                left: 50%;
                transform: translateX(-50%);
                border: 4px solid transparent;
                border-top-color: #333;
            }
            .range-slider-labels {
                display: flex;
                justify-content: space-between;
                margin-top: 8px;
                font-size: 12px;
                color: #666;
            }
            /* Vertical slider */
            .range-slider-vertical {
                display: inline-block;
                padding: 0 20px;
            }
            .range-slider-vertical .range-slider-track {
                width: 6px;
                height: 200px;
            }
            .range-slider-vertical .range-slider-range {
                width: 100%;
                height: auto;
                bottom: 0;
            }
            .range-slider-vertical .range-slider-handle {
                left: 50%;
                top: auto;
                transform: translate(-50%, 50%);
            }
        `;
        document.head.appendChild(style);
    }
})();
JAVASCRIPT;
    }
}


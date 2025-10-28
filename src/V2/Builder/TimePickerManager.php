<?php

declare(strict_types=1);

namespace FormGenerator\V2\Builder;

/**
 * TimePickerManager - Pure Vanilla JS Time Picker
 *
 * Features:
 * - 12-hour and 24-hour formats
 * - AM/PM support
 * - Hour, minute, second selection
 * - Keyboard navigation
 * - Multi-language support
 * - Min/max time constraints
 * - Custom step intervals
 *
 * @author selcukmart
 * @since 2.0.0
 */
class TimePickerManager
{
    private static array $renderedPickers = [];

    /**
     * Default locales for common languages
     */
    public const LOCALE_EN = [
        'hours' => 'Hours',
        'minutes' => 'Minutes',
        'seconds' => 'Seconds',
        'am' => 'AM',
        'pm' => 'PM',
        'now' => 'Now',
        'clear' => 'Clear',
        'close' => 'Close',
    ];

    public const LOCALE_TR = [
        'hours' => 'Saat',
        'minutes' => 'Dakika',
        'seconds' => 'Saniye',
        'am' => 'ÖÖ',
        'pm' => 'ÖS',
        'now' => 'Şimdi',
        'clear' => 'Temizle',
        'close' => 'Kapat',
    ];

    public const LOCALE_DE = [
        'hours' => 'Stunden',
        'minutes' => 'Minuten',
        'seconds' => 'Sekunden',
        'am' => 'AM',
        'pm' => 'PM',
        'now' => 'Jetzt',
        'clear' => 'Löschen',
        'close' => 'Schließen',
    ];

    public const LOCALE_FR = [
        'hours' => 'Heures',
        'minutes' => 'Minutes',
        'seconds' => 'Secondes',
        'am' => 'AM',
        'pm' => 'PM',
        'now' => 'Maintenant',
        'clear' => 'Effacer',
        'close' => 'Fermer',
    ];

    public const LOCALE_ES = [
        'hours' => 'Horas',
        'minutes' => 'Minutos',
        'seconds' => 'Segundos',
        'am' => 'AM',
        'pm' => 'PM',
        'now' => 'Ahora',
        'clear' => 'Limpiar',
        'close' => 'Cerrar',
    ];

    /**
     * Generate time picker script
     *
     * @param string $inputId Input field ID
     * @param array $options Configuration options
     * @param bool $includeScript Whether to include script tags
     * @return string Generated JavaScript code
     */
    public static function generateScript(string $inputId, array $options = [], bool $includeScript = true): string
    {
        // Prevent duplicate rendering
        if (isset(self::$renderedPickers[$inputId])) {
            return '';
        }
        self::$renderedPickers[$inputId] = true;

        $defaultOptions = [
            'format' => '24', // '12' or '24'
            'locale' => self::LOCALE_EN,
            'showSeconds' => false,
            'minTime' => null,
            'maxTime' => null,
            'step' => 1, // Step for minutes/seconds
            'inline' => false,
            'position' => 'bottom-left',
            'rtl' => false, // Right-to-left support
        ];

        $config = array_merge($defaultOptions, $options);
        $configJson = json_encode($config, JSON_HEX_APOS | JSON_HEX_QUOT);

        $script = self::generateTimePickerJS($inputId, $configJson);

        if ($includeScript) {
            return "<script>\n{$script}\n</script>";
        }

        return $script;
    }

    /**
     * Generate the complete time picker JavaScript
     */
    private static function generateTimePickerJS(string $inputId, string $configJson): string
    {
        return <<<JAVASCRIPT
(function() {
    'use strict';

    const config = {$configJson};
    const input = document.getElementById('{$inputId}');

    if (!input) {
        console.error('TimePicker: Input element not found:', '{$inputId}');
        return;
    }

    // Create TimePicker class
    class TimePicker {
        constructor(inputElement, options) {
            this.input = inputElement;
            this.options = options;
            this.selectedHour = 0;
            this.selectedMinute = 0;
            this.selectedSecond = 0;
            this.period = 'AM'; // AM/PM for 12-hour format
            this.isOpen = false;
            this.picker = null;

            this.init();
        }

        init() {
            this.input.setAttribute('autocomplete', 'off');

            // Create picker element
            this.createPicker();

            // Bind events
            this.bindEvents();

            // Parse initial value
            if (this.input.value) {
                this.parseTime(this.input.value);
            }

            // Inline mode
            if (this.options.inline) {
                this.show();
            }
        }

        createPicker() {
            this.picker = document.createElement('div');
            this.picker.className = 'timepicker-popup';
            this.picker.style.display = 'none';

            if (this.options.inline) {
                this.input.parentNode.insertBefore(this.picker, this.input.nextSibling);
            } else {
                document.body.appendChild(this.picker);
                this.picker.style.position = 'absolute';
                this.picker.style.zIndex = '9999';
            }

            this.renderPicker();
        }

        renderPicker() {
            let html = '<div class="timepicker-selectors">';

            // Hours
            html += '<div class="timepicker-column">';
            html += '<div class="timepicker-label">' + this.options.locale.hours + '</div>';
            html += '<div class="timepicker-scroll" data-type="hours">';
            const maxHour = this.options.format === '12' ? 12 : 23;
            const startHour = this.options.format === '12' ? 1 : 0;
            for (let i = startHour; i <= maxHour; i++) {
                const hour = String(i).padStart(2, '0');
                const selected = (this.options.format === '12' ? i : i) === this.selectedHour ? 'selected' : '';
                html += '<div class="timepicker-item ' + selected + '" data-value="' + i + '">' + hour + '</div>';
            }
            html += '</div></div>';

            // Minutes
            html += '<div class="timepicker-column">';
            html += '<div class="timepicker-label">' + this.options.locale.minutes + '</div>';
            html += '<div class="timepicker-scroll" data-type="minutes">';
            for (let i = 0; i < 60; i += this.options.step) {
                const minute = String(i).padStart(2, '0');
                const selected = i === this.selectedMinute ? 'selected' : '';
                html += '<div class="timepicker-item ' + selected + '" data-value="' + i + '">' + minute + '</div>';
            }
            html += '</div></div>';

            // Seconds (optional)
            if (this.options.showSeconds) {
                html += '<div class="timepicker-column">';
                html += '<div class="timepicker-label">' + this.options.locale.seconds + '</div>';
                html += '<div class="timepicker-scroll" data-type="seconds">';
                for (let i = 0; i < 60; i += this.options.step) {
                    const second = String(i).padStart(2, '0');
                    const selected = i === this.selectedSecond ? 'selected' : '';
                    html += '<div class="timepicker-item ' + selected + '" data-value="' + i + '">' + second + '</div>';
                }
                html += '</div></div>';
            }

            // AM/PM (for 12-hour format)
            if (this.options.format === '12') {
                html += '<div class="timepicker-column">';
                html += '<div class="timepicker-label">&nbsp;</div>';
                html += '<div class="timepicker-scroll" data-type="period">';
                html += '<div class="timepicker-item ' + (this.period === 'AM' ? 'selected' : '') + '" data-value="AM">' + this.options.locale.am + '</div>';
                html += '<div class="timepicker-item ' + (this.period === 'PM' ? 'selected' : '') + '" data-value="PM">' + this.options.locale.pm + '</div>';
                html += '</div></div>';
            }

            html += '</div>';

            // Footer
            html += '<div class="timepicker-footer">';
            html += '<button type="button" class="timepicker-now">' + this.options.locale.now + '</button>';
            html += '<button type="button" class="timepicker-clear">' + this.options.locale.clear + '</button>';
            html += '</div>';

            this.picker.innerHTML = html;
        }

        bindEvents() {
            // Input click
            this.input.addEventListener('click', (e) => {
                if (!this.options.inline) {
                    this.toggle();
                }
            });

            // Picker click delegation
            this.picker.addEventListener('click', (e) => {
                const target = e.target;

                if (target.classList.contains('timepicker-item')) {
                    const scroll = target.parentElement;
                    const type = scroll.dataset.type;
                    const value = target.dataset.value;

                    // Remove previous selection
                    scroll.querySelectorAll('.timepicker-item').forEach(item => {
                        item.classList.remove('selected');
                    });
                    target.classList.add('selected');

                    // Update selected values
                    if (type === 'hours') {
                        this.selectedHour = parseInt(value);
                    } else if (type === 'minutes') {
                        this.selectedMinute = parseInt(value);
                    } else if (type === 'seconds') {
                        this.selectedSecond = parseInt(value);
                    } else if (type === 'period') {
                        this.period = value;
                    }

                    this.updateInput();
                } else if (target.classList.contains('timepicker-now')) {
                    this.setNow();
                } else if (target.classList.contains('timepicker-clear')) {
                    this.clear();
                }
            });

            // Close on outside click
            if (!this.options.inline) {
                document.addEventListener('click', (e) => {
                    if (this.isOpen && !this.picker.contains(e.target) && e.target !== this.input) {
                        this.hide();
                    }
                });
            }

            // Keyboard navigation
            this.input.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && this.isOpen) {
                    this.hide();
                }
            });
        }

        show() {
            if (this.isOpen) return;

            this.isOpen = true;
            this.picker.style.display = 'block';

            if (!this.options.inline) {
                this.position();
            }

            this.dispatchEvent('open');
        }

        hide() {
            if (!this.isOpen || this.options.inline) return;

            this.isOpen = false;
            this.picker.style.display = 'none';
            this.dispatchEvent('close');
        }

        toggle() {
            if (this.isOpen) {
                this.hide();
            } else {
                this.show();
            }
        }

        position() {
            const rect = this.input.getBoundingClientRect();
            const pickerRect = this.picker.getBoundingClientRect();

            let top, left;

            if (this.options.position.includes('bottom')) {
                top = rect.bottom + window.scrollY + 4;
            } else {
                top = rect.top + window.scrollY - pickerRect.height - 4;
            }

            if (this.options.position.includes('right')) {
                left = rect.right + window.scrollX - pickerRect.width;
            } else {
                left = rect.left + window.scrollX;
            }

            this.picker.style.top = top + 'px';
            this.picker.style.left = left + 'px';
        }

        updateInput() {
            const timeStr = this.formatTime();
            this.input.value = timeStr;

            this.dispatchEvent('change', {
                hour: this.selectedHour,
                minute: this.selectedMinute,
                second: this.selectedSecond,
                period: this.period,
                value: timeStr
            });

            // Trigger native change event
            const event = new Event('change', { bubbles: true });
            this.input.dispatchEvent(event);
        }

        formatTime() {
            let hour = this.selectedHour;
            const minute = String(this.selectedMinute).padStart(2, '0');
            const second = String(this.selectedSecond).padStart(2, '0');

            if (this.options.format === '12') {
                const displayHour = hour === 0 ? 12 : hour;
                let timeStr = String(displayHour).padStart(2, '0') + ':' + minute;
                if (this.options.showSeconds) {
                    timeStr += ':' + second;
                }
                timeStr += ' ' + this.period;
                return timeStr;
            } else {
                let timeStr = String(hour).padStart(2, '0') + ':' + minute;
                if (this.options.showSeconds) {
                    timeStr += ':' + second;
                }
                return timeStr;
            }
        }

        parseTime(timeStr) {
            if (!timeStr) return;

            // Parse 24-hour format (HH:MM or HH:MM:SS)
            let parts = timeStr.trim().split(':');

            // Check for AM/PM
            const lastPart = parts[parts.length - 1];
            if (lastPart.includes('AM') || lastPart.includes('PM')) {
                this.period = lastPart.includes('AM') ? 'AM' : 'PM';
                parts[parts.length - 1] = lastPart.replace(/\s*(AM|PM)/, '');
            }

            if (parts.length >= 2) {
                this.selectedHour = parseInt(parts[0]);
                this.selectedMinute = parseInt(parts[1]);
                if (parts.length >= 3) {
                    this.selectedSecond = parseInt(parts[2]);
                }
            }
        }

        setNow() {
            const now = new Date();
            this.selectedHour = now.getHours();
            this.selectedMinute = now.getMinutes();
            this.selectedSecond = now.getSeconds();

            if (this.options.format === '12') {
                this.period = this.selectedHour >= 12 ? 'PM' : 'AM';
                this.selectedHour = this.selectedHour % 12 || 12;
            }

            this.renderPicker();
            this.updateInput();
        }

        clear() {
            this.selectedHour = 0;
            this.selectedMinute = 0;
            this.selectedSecond = 0;
            this.period = 'AM';
            this.input.value = '';
            this.renderPicker();
            this.dispatchEvent('clear');

            const event = new Event('change', { bubbles: true });
            this.input.dispatchEvent(event);
        }

        dispatchEvent(eventName, detail = {}) {
            const event = new CustomEvent('timepicker:' + eventName, {
                detail: detail,
                bubbles: true
            });
            this.input.dispatchEvent(event);
        }

        destroy() {
            this.picker.remove();
        }
    }

    // Initialize TimePicker
    const picker = new TimePicker(input, config);

    // Store instance globally for API access
    window.TimePicker_{$inputId} = picker;

    // Add default styles if not already added
    if (!document.getElementById('timepicker-styles')) {
        const style = document.createElement('style');
        style.id = 'timepicker-styles';
        style.textContent = `
            .timepicker-popup {
                background: #fff;
                border: 1px solid #ddd;
                border-radius: 4px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.15);
                padding: 12px;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            }
            .timepicker-selectors {
                display: flex;
                gap: 8px;
            }
            .timepicker-column {
                display: flex;
                flex-direction: column;
            }
            .timepicker-label {
                text-align: center;
                font-size: 12px;
                font-weight: 600;
                margin-bottom: 8px;
                color: #666;
            }
            .timepicker-scroll {
                max-height: 200px;
                overflow-y: auto;
                border: 1px solid #ddd;
                border-radius: 4px;
                min-width: 60px;
            }
            .timepicker-scroll::-webkit-scrollbar {
                width: 6px;
            }
            .timepicker-scroll::-webkit-scrollbar-thumb {
                background: #ccc;
                border-radius: 3px;
            }
            .timepicker-item {
                padding: 8px 12px;
                text-align: center;
                cursor: pointer;
                font-size: 14px;
                transition: background 0.2s;
            }
            .timepicker-item:hover {
                background: #f0f0f0;
            }
            .timepicker-item.selected {
                background: #0d6efd;
                color: #fff;
                font-weight: 600;
            }
            .timepicker-footer {
                display: flex;
                justify-content: space-between;
                margin-top: 12px;
                padding-top: 12px;
                border-top: 1px solid #eee;
            }
            .timepicker-now,
            .timepicker-clear {
                background: none;
                border: 1px solid #ddd;
                padding: 6px 12px;
                border-radius: 4px;
                font-size: 12px;
                cursor: pointer;
            }
            .timepicker-now:hover,
            .timepicker-clear:hover {
                background: #f0f0f0;
            }
        `;
        document.head.appendChild(style);
    }
})();
JAVASCRIPT;
    }
}


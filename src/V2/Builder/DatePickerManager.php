<?php

declare(strict_types=1);

namespace FormGenerator\V2\Builder;

/**
 * DatePickerManager - Pure Vanilla JS Date Picker
 *
 * Features:
 * - Multi-language support (i18n)
 * - Multiple date formats
 * - Min/max date constraints
 * - Disabled dates
 * - Keyboard navigation
 * - Accessibility (ARIA)
 * - Custom events
 * - Theme support
 *
 * @author selcukmart
 * @since 2.0.0
 */
class DatePickerManager
{
    private static array $renderedPickers = [];

    /**
     * Default locales for common languages
     */
    public const LOCALE_EN = [
        'months' => ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
        'monthsShort' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        'weekdays' => ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
        'weekdaysShort' => ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
        'weekdaysMin' => ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'],
        'today' => 'Today',
        'clear' => 'Clear',
        'close' => 'Close',
    ];

    public const LOCALE_TR = [
        'months' => ['Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'],
        'monthsShort' => ['Oca', 'Şub', 'Mar', 'Nis', 'May', 'Haz', 'Tem', 'Ağu', 'Eyl', 'Eki', 'Kas', 'Ara'],
        'weekdays' => ['Pazar', 'Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi'],
        'weekdaysShort' => ['Paz', 'Pzt', 'Sal', 'Çar', 'Per', 'Cum', 'Cmt'],
        'weekdaysMin' => ['Pz', 'Pt', 'Sa', 'Ça', 'Pe', 'Cu', 'Ct'],
        'today' => 'Bugün',
        'clear' => 'Temizle',
        'close' => 'Kapat',
    ];

    public const LOCALE_DE = [
        'months' => ['Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'],
        'monthsShort' => ['Jan', 'Feb', 'Mär', 'Apr', 'Mai', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dez'],
        'weekdays' => ['Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'],
        'weekdaysShort' => ['So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa'],
        'weekdaysMin' => ['So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa'],
        'today' => 'Heute',
        'clear' => 'Löschen',
        'close' => 'Schließen',
    ];

    public const LOCALE_FR = [
        'months' => ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
        'monthsShort' => ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'],
        'weekdays' => ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'],
        'weekdaysShort' => ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'],
        'weekdaysMin' => ['Di', 'Lu', 'Ma', 'Me', 'Je', 'Ve', 'Sa'],
        'today' => 'Aujourd\'hui',
        'clear' => 'Effacer',
        'close' => 'Fermer',
    ];

    public const LOCALE_ES = [
        'months' => ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
        'monthsShort' => ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
        'weekdays' => ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
        'weekdaysShort' => ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'],
        'weekdaysMin' => ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sá'],
        'today' => 'Hoy',
        'clear' => 'Limpiar',
        'close' => 'Cerrar',
    ];

    /**
     * Generate date picker script
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
            'format' => 'yyyy-mm-dd',
            'locale' => self::LOCALE_EN,
            'minDate' => null,
            'maxDate' => null,
            'disabledDates' => [],
            'weekStart' => 0, // 0 = Sunday, 1 = Monday
            'showToday' => true,
            'showClear' => true,
            'inline' => false,
            'position' => 'bottom-left', // bottom-left, bottom-right, top-left, top-right
            'rtl' => false, // Right-to-left support for Arabic, Hebrew, etc.
        ];

        $config = array_merge($defaultOptions, $options);
        $configJson = json_encode($config, JSON_HEX_APOS | JSON_HEX_QUOT);

        $script = self::generateDatePickerJS($inputId, $configJson);

        if ($includeScript) {
            return "<script>\n{$script}\n</script>";
        }

        return $script;
    }

    /**
     * Generate the complete date picker JavaScript
     */
    private static function generateDatePickerJS(string $inputId, string $configJson): string
    {
        return <<<JAVASCRIPT
(function() {
    'use strict';

    const config = {$configJson};
    const input = document.getElementById('{$inputId}');

    if (!input) {
        console.error('DatePicker: Input element not found:', '{$inputId}');
        return;
    }

    // Create DatePicker class
    class DatePicker {
        constructor(inputElement, options) {
            this.input = inputElement;
            this.options = options;
            this.currentDate = new Date();
            this.selectedDate = null;
            this.isOpen = false;
            this.calendar = null;

            this.init();
        }

        init() {
            // Set input as readonly to prevent manual entry (optional)
            this.input.setAttribute('autocomplete', 'off');

            // Apply RTL if needed
            if (this.options.rtl) {
                this.input.setAttribute('dir', 'rtl');
            }

            // Create calendar element
            this.createCalendar();

            // Bind events
            this.bindEvents();

            // Parse initial value
            if (this.input.value) {
                this.selectedDate = this.parseDate(this.input.value);
                if (this.selectedDate) {
                    this.currentDate = new Date(this.selectedDate);
                }
            }

            // Inline mode
            if (this.options.inline) {
                this.show();
            }
        }

        createCalendar() {
            this.calendar = document.createElement('div');
            this.calendar.className = 'datepicker-calendar' + (this.options.rtl ? ' rtl' : '');
            this.calendar.style.display = 'none';

            if (this.options.inline) {
                this.input.parentNode.insertBefore(this.calendar, this.input.nextSibling);
            } else {
                document.body.appendChild(this.calendar);
                this.calendar.style.position = 'absolute';
                this.calendar.style.zIndex = '9999';
            }

            this.renderCalendar();
        }

        renderCalendar() {
            const year = this.currentDate.getFullYear();
            const month = this.currentDate.getMonth();

            let html = '<div class="datepicker-header">';
            html += '<button type="button" class="datepicker-prev" aria-label="Previous month">&lsaquo;</button>';
            html += '<span class="datepicker-title">' + this.options.locale.months[month] + ' ' + year + '</span>';
            html += '<button type="button" class="datepicker-next" aria-label="Next month">&rsaquo;</button>';
            html += '</div>';

            html += '<div class="datepicker-weekdays">';
            for (let i = 0; i < 7; i++) {
                const day = (i + this.options.weekStart) % 7;
                html += '<span class="datepicker-weekday">' + this.options.locale.weekdaysMin[day] + '</span>';
            }
            html += '</div>';

            html += '<div class="datepicker-days">';
            html += this.renderDays(year, month);
            html += '</div>';

            if (this.options.showToday || this.options.showClear) {
                html += '<div class="datepicker-footer">';
                if (this.options.showToday) {
                    html += '<button type="button" class="datepicker-today">' + this.options.locale.today + '</button>';
                }
                if (this.options.showClear) {
                    html += '<button type="button" class="datepicker-clear">' + this.options.locale.clear + '</button>';
                }
                html += '</div>';
            }

            this.calendar.innerHTML = html;
        }

        renderDays(year, month) {
            const firstDay = new Date(year, month, 1);
            const lastDay = new Date(year, month + 1, 0);
            const prevLastDay = new Date(year, month, 0);

            const firstDayOfWeek = (firstDay.getDay() - this.options.weekStart + 7) % 7;
            const daysInMonth = lastDay.getDate();
            const daysInPrevMonth = prevLastDay.getDate();

            let html = '';

            // Previous month days
            for (let i = firstDayOfWeek - 1; i >= 0; i--) {
                const day = daysInPrevMonth - i;
                html += '<button type="button" class="datepicker-day datepicker-day-other" data-date="' +
                    year + '-' + (month) + '-' + day + '">' + day + '</button>';
            }

            // Current month days
            for (let day = 1; day <= daysInMonth; day++) {
                const date = new Date(year, month, day);
                const dateStr = this.formatDate(date, 'yyyy-mm-dd');
                const isDisabled = this.isDateDisabled(date);
                const isSelected = this.selectedDate && this.isSameDay(date, this.selectedDate);
                const isToday = this.isSameDay(date, new Date());

                let classes = 'datepicker-day';
                if (isSelected) classes += ' datepicker-day-selected';
                if (isToday) classes += ' datepicker-day-today';
                if (isDisabled) classes += ' datepicker-day-disabled';

                html += '<button type="button" class="' + classes + '" data-date="' + dateStr + '" ' +
                    (isDisabled ? 'disabled' : '') + '>' + day + '</button>';
            }

            // Next month days to fill grid
            const totalCells = Math.ceil((firstDayOfWeek + daysInMonth) / 7) * 7;
            const remainingCells = totalCells - (firstDayOfWeek + daysInMonth);
            for (let day = 1; day <= remainingCells; day++) {
                html += '<button type="button" class="datepicker-day datepicker-day-other" data-date="' +
                    year + '-' + (month + 2) + '-' + day + '">' + day + '</button>';
            }

            return html;
        }

        bindEvents() {
            // Input click
            this.input.addEventListener('click', (e) => {
                if (!this.options.inline) {
                    this.toggle();
                }
            });

            // Calendar click delegation
            this.calendar.addEventListener('click', (e) => {
                const target = e.target;

                if (target.classList.contains('datepicker-prev')) {
                    this.previousMonth();
                } else if (target.classList.contains('datepicker-next')) {
                    this.nextMonth();
                } else if (target.classList.contains('datepicker-day') && !target.disabled) {
                    this.selectDate(target.dataset.date);
                } else if (target.classList.contains('datepicker-today')) {
                    this.selectToday();
                } else if (target.classList.contains('datepicker-clear')) {
                    this.clear();
                }
            });

            // Close on outside click
            if (!this.options.inline) {
                document.addEventListener('click', (e) => {
                    if (this.isOpen && !this.calendar.contains(e.target) && e.target !== this.input) {
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
            this.calendar.style.display = 'block';

            if (!this.options.inline) {
                this.position();
            }

            this.dispatchEvent('open');
        }

        hide() {
            if (!this.isOpen || this.options.inline) return;

            this.isOpen = false;
            this.calendar.style.display = 'none';
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
            const calendarRect = this.calendar.getBoundingClientRect();

            let top, left;

            if (this.options.position.includes('bottom')) {
                top = rect.bottom + window.scrollY + 4;
            } else {
                top = rect.top + window.scrollY - calendarRect.height - 4;
            }

            if (this.options.position.includes('right')) {
                left = rect.right + window.scrollX - calendarRect.width;
            } else {
                left = rect.left + window.scrollX;
            }

            this.calendar.style.top = top + 'px';
            this.calendar.style.left = left + 'px';
        }

        previousMonth() {
            this.currentDate.setMonth(this.currentDate.getMonth() - 1);
            this.renderCalendar();
        }

        nextMonth() {
            this.currentDate.setMonth(this.currentDate.getMonth() + 1);
            this.renderCalendar();
        }

        selectDate(dateStr) {
            this.selectedDate = this.parseDate(dateStr);
            this.input.value = this.formatDate(this.selectedDate, this.options.format);
            this.renderCalendar();

            if (!this.options.inline) {
                this.hide();
            }

            this.dispatchEvent('change', { date: this.selectedDate, value: this.input.value });

            // Trigger native change event
            const event = new Event('change', { bubbles: true });
            this.input.dispatchEvent(event);
        }

        selectToday() {
            this.selectDate(this.formatDate(new Date(), 'yyyy-mm-dd'));
        }

        clear() {
            this.selectedDate = null;
            this.input.value = '';
            this.renderCalendar();
            this.dispatchEvent('clear');

            const event = new Event('change', { bubbles: true });
            this.input.dispatchEvent(event);
        }

        isDateDisabled(date) {
            // Check min date
            if (this.options.minDate) {
                const minDate = this.parseDate(this.options.minDate);
                if (date < minDate) return true;
            }

            // Check max date
            if (this.options.maxDate) {
                const maxDate = this.parseDate(this.options.maxDate);
                if (date > maxDate) return true;
            }

            // Check disabled dates
            const dateStr = this.formatDate(date, 'yyyy-mm-dd');
            if (this.options.disabledDates.includes(dateStr)) {
                return true;
            }

            return false;
        }

        isSameDay(date1, date2) {
            return date1.getFullYear() === date2.getFullYear() &&
                   date1.getMonth() === date2.getMonth() &&
                   date1.getDate() === date2.getDate();
        }

        parseDate(dateStr) {
            if (!dateStr) return null;

            // Parse yyyy-mm-dd format
            const parts = dateStr.split('-');
            if (parts.length === 3) {
                return new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, parseInt(parts[2]));
            }

            return new Date(dateStr);
        }

        formatDate(date, format) {
            if (!date) return '';

            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');

            return format
                .replace('yyyy', year)
                .replace('mm', month)
                .replace('dd', day);
        }

        dispatchEvent(eventName, detail = {}) {
            const event = new CustomEvent('datepicker:' + eventName, {
                detail: detail,
                bubbles: true
            });
            this.input.dispatchEvent(event);
        }

        destroy() {
            this.calendar.remove();
            // Remove event listeners would go here
        }
    }

    // Initialize DatePicker
    const picker = new DatePicker(input, config);

    // Store instance globally for API access
    window.DatePicker_{$inputId} = picker;

    // Add default styles if not already added
    if (!document.getElementById('datepicker-styles')) {
        const style = document.createElement('style');
        style.id = 'datepicker-styles';
        style.textContent = `
            .datepicker-calendar {
                background: #fff;
                border: 1px solid #ddd;
                border-radius: 4px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.15);
                padding: 8px;
                min-width: 280px;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            }
            .datepicker-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 8px;
            }
            .datepicker-prev,
            .datepicker-next {
                background: none;
                border: none;
                font-size: 20px;
                cursor: pointer;
                padding: 4px 8px;
                color: #666;
            }
            .datepicker-prev:hover,
            .datepicker-next:hover {
                background: #f0f0f0;
                border-radius: 4px;
            }
            .datepicker-title {
                font-weight: 600;
                font-size: 14px;
            }
            .datepicker-weekdays {
                display: grid;
                grid-template-columns: repeat(7, 1fr);
                gap: 4px;
                margin-bottom: 4px;
            }
            .datepicker-weekday {
                text-align: center;
                font-size: 12px;
                font-weight: 600;
                color: #666;
                padding: 4px;
            }
            .datepicker-days {
                display: grid;
                grid-template-columns: repeat(7, 1fr);
                gap: 4px;
            }
            .datepicker-day {
                border: none;
                background: none;
                padding: 8px;
                text-align: center;
                cursor: pointer;
                border-radius: 4px;
                font-size: 13px;
            }
            .datepicker-day:hover:not(:disabled) {
                background: #f0f0f0;
            }
            .datepicker-day-today {
                font-weight: 700;
                color: #0d6efd;
            }
            .datepicker-day-selected {
                background: #0d6efd;
                color: #fff;
            }
            .datepicker-day-selected:hover {
                background: #0b5ed7;
            }
            .datepicker-day-other {
                color: #ccc;
            }
            .datepicker-day-disabled {
                color: #ccc;
                cursor: not-allowed;
            }
            .datepicker-footer {
                display: flex;
                justify-content: space-between;
                margin-top: 8px;
                padding-top: 8px;
                border-top: 1px solid #eee;
            }
            .datepicker-today,
            .datepicker-clear {
                background: none;
                border: 1px solid #ddd;
                padding: 4px 12px;
                border-radius: 4px;
                font-size: 12px;
                cursor: pointer;
            }
            .datepicker-today:hover,
            .datepicker-clear:hover {
                background: #f0f0f0;
            }
            /* RTL Support */
            .datepicker-calendar.rtl {
                direction: rtl;
            }
            .datepicker-calendar.rtl .datepicker-header {
                flex-direction: row-reverse;
            }
        `;
        document.head.appendChild(style);
    }
})();
JAVASCRIPT;
    }
}


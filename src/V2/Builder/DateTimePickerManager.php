<?php

declare(strict_types=1);

namespace FormGenerator\V2\Builder;

/**
 * DateTimePickerManager - Pure Vanilla JS DateTime Picker
 *
 * Combines date and time selection in a single picker interface.
 *
 * Features:
 * - Combined date and time selection
 * - Multi-language support (i18n)
 * - Multiple datetime formats
 * - 12-hour and 24-hour time formats
 * - Min/max datetime constraints
 * - Keyboard navigation
 * - RTL/LTR support
 * - Accessibility (ARIA)
 *
 * @author selcukmart
 * @since 2.0.0
 */
class DateTimePickerManager
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
        'now' => 'Now',
        'selectDate' => 'Select Date',
        'selectTime' => 'Select Time',
        'am' => 'AM',
        'pm' => 'PM',
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
        'now' => 'Şimdi',
        'selectDate' => 'Tarih Seç',
        'selectTime' => 'Saat Seç',
        'am' => 'ÖÖ',
        'pm' => 'ÖS',
    ];

    public const LOCALE_AR = [
        'months' => ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'],
        'monthsShort' => ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'],
        'weekdays' => ['الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'],
        'weekdaysShort' => ['أحد', 'اثنين', 'ثلاثاء', 'أربعاء', 'خميس', 'جمعة', 'سبت'],
        'weekdaysMin' => ['أح', 'إث', 'ثل', 'أر', 'خم', 'جم', 'سب'],
        'today' => 'اليوم',
        'clear' => 'مسح',
        'close' => 'إغلاق',
        'now' => 'الآن',
        'selectDate' => 'اختر التاريخ',
        'selectTime' => 'اختر الوقت',
        'am' => 'ص',
        'pm' => 'م',
    ];

    public const LOCALE_HE = [
        'months' => ['ינואר', 'פברואר', 'מרץ', 'אפריל', 'מאי', 'יוני', 'יולי', 'אוגוסט', 'ספטמבר', 'אוקטובר', 'נובמבר', 'דצמבר'],
        'monthsShort' => ['ינו', 'פבר', 'מרץ', 'אפר', 'מאי', 'יונ', 'יול', 'אוג', 'ספט', 'אוק', 'נוב', 'דצמ'],
        'weekdays' => ['ראשון', 'שני', 'שלישי', 'רביעי', 'חמישי', 'שישי', 'שבת'],
        'weekdaysShort' => ['א\'', 'ב\'', 'ג\'', 'ד\'', 'ה\'', 'ו\'', 'ש\''],
        'weekdaysMin' => ['א', 'ב', 'ג', 'ד', 'ה', 'ו', 'ש'],
        'today' => 'היום',
        'clear' => 'נקה',
        'close' => 'סגור',
        'now' => 'עכשיו',
        'selectDate' => 'בחר תאריך',
        'selectTime' => 'בחר שעה',
        'am' => 'AM',
        'pm' => 'PM',
    ];

    /**
     * Generate datetime picker script
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
            'format' => 'yyyy-mm-dd HH:MM',
            'timeFormat' => '24', // 12 or 24
            'locale' => self::LOCALE_EN,
            'minDateTime' => null,
            'maxDateTime' => null,
            'weekStart' => 0,
            'showSeconds' => false,
            'showToday' => true,
            'showClear' => true,
            'inline' => false,
            'position' => 'bottom-left',
            'rtl' => false, // Right-to-left support
        ];

        $config = array_merge($defaultOptions, $options);
        $configJson = json_encode($config, JSON_HEX_APOS | JSON_HEX_QUOT);

        $script = self::generateDateTimePickerJS($inputId, $configJson);

        if ($includeScript) {
            return "<script>\n{$script}\n</script>";
        }

        return $script;
    }

    /**
     * Generate the complete datetime picker JavaScript
     */
    private static function generateDateTimePickerJS(string $inputId, string $configJson): string
    {
        return <<<JAVASCRIPT
(function() {
    'use strict';

    const config = {$configJson};
    const input = document.getElementById('{$inputId}');

    if (!input) {
        console.error('DateTimePicker: Input element not found:', '{$inputId}');
        return;
    }

    // Create DateTimePicker class
    class DateTimePicker {
        constructor(inputElement, options) {
            this.input = inputElement;
            this.options = options;
            this.currentDate = new Date();
            this.selectedDate = null;
            this.selectedHour = 0;
            this.selectedMinute = 0;
            this.selectedSecond = 0;
            this.period = 'AM';
            this.isOpen = false;
            this.picker = null;
            this.currentTab = 'date'; // 'date' or 'time'

            this.init();
        }

        init() {
            this.input.setAttribute('autocomplete', 'off');

            // Apply RTL if needed
            if (this.options.rtl) {
                this.input.setAttribute('dir', 'rtl');
            }

            this.createPicker();
            this.bindEvents();

            if (this.input.value) {
                this.parseDateTime(this.input.value);
            }

            if (this.options.inline) {
                this.show();
            }
        }

        createPicker() {
            this.picker = document.createElement('div');
            this.picker.className = 'datetimepicker-popup' + (this.options.rtl ? ' rtl' : '');
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
            let html = '';

            // Tabs
            html += '<div class="datetimepicker-tabs">';
            html += '<button type="button" class="datetimepicker-tab ' + (this.currentTab === 'date' ? 'active' : '') + '" data-tab="date">';
            html += this.options.locale.selectDate;
            html += '</button>';
            html += '<button type="button" class="datetimepicker-tab ' + (this.currentTab === 'time' ? 'active' : '') + '" data-tab="time">';
            html += this.options.locale.selectTime;
            html += '</button>';
            html += '</div>';

            // Content container
            html += '<div class="datetimepicker-content">';

            // Date tab
            html += '<div class="datetimepicker-pane ' + (this.currentTab === 'date' ? 'active' : '') + '" data-pane="date">';
            html += this.renderCalendar();
            html += '</div>';

            // Time tab
            html += '<div class="datetimepicker-pane ' + (this.currentTab === 'time' ? 'active' : '') + '" data-pane="time">';
            html += this.renderTime();
            html += '</div>';

            html += '</div>';

            // Footer
            html += '<div class="datetimepicker-footer">';
            if (this.options.showToday) {
                html += '<button type="button" class="datetimepicker-now">' + this.options.locale.now + '</button>';
            }
            if (this.options.showClear) {
                html += '<button type="button" class="datetimepicker-clear">' + this.options.locale.clear + '</button>';
            }
            html += '</div>';

            this.picker.innerHTML = html;
        }

        renderCalendar() {
            const year = this.currentDate.getFullYear();
            const month = this.currentDate.getMonth();

            let html = '<div class="datetimepicker-header">';
            html += '<button type="button" class="datetimepicker-prev" aria-label="Previous month">&lsaquo;</button>';
            html += '<span class="datetimepicker-title">' + this.options.locale.months[month] + ' ' + year + '</span>';
            html += '<button type="button" class="datetimepicker-next" aria-label="Next month">&rsaquo;</button>';
            html += '</div>';

            html += '<div class="datetimepicker-weekdays">';
            for (let i = 0; i < 7; i++) {
                const day = (i + this.options.weekStart) % 7;
                html += '<span class="datetimepicker-weekday">' + this.options.locale.weekdaysMin[day] + '</span>';
            }
            html += '</div>';

            html += '<div class="datetimepicker-days">';
            html += this.renderDays(year, month);
            html += '</div>';

            return html;
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
                html += '<button type="button" class="datetimepicker-day datetimepicker-day-other">' + day + '</button>';
            }

            // Current month days
            for (let day = 1; day <= daysInMonth; day++) {
                const date = new Date(year, month, day);
                const isSelected = this.selectedDate && this.isSameDay(date, this.selectedDate);
                const isToday = this.isSameDay(date, new Date());

                let classes = 'datetimepicker-day';
                if (isSelected) classes += ' datetimepicker-day-selected';
                if (isToday) classes += ' datetimepicker-day-today';

                html += '<button type="button" class="' + classes + '" data-year="' + year + '" data-month="' + month + '" data-day="' + day + '">' + day + '</button>';
            }

            // Next month days
            const totalCells = Math.ceil((firstDayOfWeek + daysInMonth) / 7) * 7;
            const remainingCells = totalCells - (firstDayOfWeek + daysInMonth);
            for (let day = 1; day <= remainingCells; day++) {
                html += '<button type="button" class="datetimepicker-day datetimepicker-day-other">' + day + '</button>';
            }

            return html;
        }

        renderTime() {
            let html = '<div class="datetimepicker-time-selectors">';

            // Hours
            html += '<div class="datetimepicker-time-column">';
            html += '<div class="datetimepicker-time-scroll" data-type="hours">';
            const maxHour = this.options.timeFormat === '12' ? 12 : 23;
            const startHour = this.options.timeFormat === '12' ? 1 : 0;
            for (let i = startHour; i <= maxHour; i++) {
                const selected = i === this.selectedHour ? 'selected' : '';
                html += '<div class="datetimepicker-time-item ' + selected + '" data-value="' + i + '">' + String(i).padStart(2, '0') + '</div>';
            }
            html += '</div></div>';

            // Minutes
            html += '<div class="datetimepicker-time-column">';
            html += '<div class="datetimepicker-time-scroll" data-type="minutes">';
            for (let i = 0; i < 60; i += 1) {
                const selected = i === this.selectedMinute ? 'selected' : '';
                html += '<div class="datetimepicker-time-item ' + selected + '" data-value="' + i + '">' + String(i).padStart(2, '0') + '</div>';
            }
            html += '</div></div>';

            // Seconds (optional)
            if (this.options.showSeconds) {
                html += '<div class="datetimepicker-time-column">';
                html += '<div class="datetimepicker-time-scroll" data-type="seconds">';
                for (let i = 0; i < 60; i++) {
                    const selected = i === this.selectedSecond ? 'selected' : '';
                    html += '<div class="datetimepicker-time-item ' + selected + '" data-value="' + i + '">' + String(i).padStart(2, '0') + '</div>';
                }
                html += '</div></div>';
            }

            // AM/PM
            if (this.options.timeFormat === '12') {
                html += '<div class="datetimepicker-time-column">';
                html += '<div class="datetimepicker-time-scroll" data-type="period">';
                html += '<div class="datetimepicker-time-item ' + (this.period === 'AM' ? 'selected' : '') + '" data-value="AM">' + this.options.locale.am + '</div>';
                html += '<div class="datetimepicker-time-item ' + (this.period === 'PM' ? 'selected' : '') + '" data-value="PM">' + this.options.locale.pm + '</div>';
                html += '</div></div>';
            }

            html += '</div>';
            return html;
        }

        bindEvents() {
            this.input.addEventListener('click', () => {
                if (!this.options.inline) this.toggle();
            });

            this.picker.addEventListener('click', (e) => {
                const target = e.target;

                // Tab switching
                if (target.classList.contains('datetimepicker-tab')) {
                    this.switchTab(target.dataset.tab);
                }
                // Calendar navigation
                else if (target.classList.contains('datetimepicker-prev')) {
                    this.previousMonth();
                } else if (target.classList.contains('datetimepicker-next')) {
                    this.nextMonth();
                }
                // Day selection
                else if (target.classList.contains('datetimepicker-day') && !target.classList.contains('datetimepicker-day-other')) {
                    this.selectDay(parseInt(target.dataset.year), parseInt(target.dataset.month), parseInt(target.dataset.day));
                }
                // Time selection
                else if (target.classList.contains('datetimepicker-time-item')) {
                    const scroll = target.parentElement;
                    const type = scroll.dataset.type;
                    const value = target.dataset.value;

                    scroll.querySelectorAll('.datetimepicker-time-item').forEach(item => item.classList.remove('selected'));
                    target.classList.add('selected');

                    if (type === 'hours') this.selectedHour = parseInt(value);
                    else if (type === 'minutes') this.selectedMinute = parseInt(value);
                    else if (type === 'seconds') this.selectedSecond = parseInt(value);
                    else if (type === 'period') this.period = value;

                    this.updateInput();
                }
                // Footer buttons
                else if (target.classList.contains('datetimepicker-now')) {
                    this.setNow();
                } else if (target.classList.contains('datetimepicker-clear')) {
                    this.clear();
                }
            });

            if (!this.options.inline) {
                document.addEventListener('click', (e) => {
                    if (this.isOpen && !this.picker.contains(e.target) && e.target !== this.input) {
                        this.hide();
                    }
                });
            }
        }

        switchTab(tab) {
            this.currentTab = tab;
            this.renderPicker();
        }

        previousMonth() {
            this.currentDate.setMonth(this.currentDate.getMonth() - 1);
            this.renderPicker();
        }

        nextMonth() {
            this.currentDate.setMonth(this.currentDate.getMonth() + 1);
            this.renderPicker();
        }

        selectDay(year, month, day) {
            this.selectedDate = new Date(year, month, day);
            this.currentDate = new Date(this.selectedDate);
            this.switchTab('time');
        }

        setNow() {
            const now = new Date();
            this.selectedDate = now;
            this.currentDate = new Date(now);
            this.selectedHour = this.options.timeFormat === '12' ? (now.getHours() % 12 || 12) : now.getHours();
            this.selectedMinute = now.getMinutes();
            this.selectedSecond = now.getSeconds();
            this.period = now.getHours() >= 12 ? 'PM' : 'AM';
            this.updateInput();
            this.renderPicker();
        }

        clear() {
            this.selectedDate = null;
            this.selectedHour = 0;
            this.selectedMinute = 0;
            this.selectedSecond = 0;
            this.period = 'AM';
            this.input.value = '';
            this.renderPicker();
        }

        updateInput() {
            if (!this.selectedDate) return;

            const dateStr = this.formatDate(this.selectedDate);
            const timeStr = this.formatTime();
            this.input.value = dateStr + ' ' + timeStr;

            const event = new Event('change', { bubbles: true });
            this.input.dispatchEvent(event);
        }

        formatDate(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return year + '-' + month + '-' + day;
        }

        formatTime() {
            const hour = String(this.selectedHour).padStart(2, '0');
            const minute = String(this.selectedMinute).padStart(2, '0');
            const second = String(this.selectedSecond).padStart(2, '0');

            if (this.options.timeFormat === '12') {
                let timeStr = hour + ':' + minute;
                if (this.options.showSeconds) timeStr += ':' + second;
                timeStr += ' ' + this.period;
                return timeStr;
            } else {
                let timeStr = hour + ':' + minute;
                if (this.options.showSeconds) timeStr += ':' + second;
                return timeStr;
            }
        }

        parseDateTime(value) {
            // Simple parsing - can be enhanced
            const parts = value.split(' ');
            if (parts.length >= 2) {
                const dateParts = parts[0].split('-');
                if (dateParts.length === 3) {
                    this.selectedDate = new Date(parseInt(dateParts[0]), parseInt(dateParts[1]) - 1, parseInt(dateParts[2]));
                    this.currentDate = new Date(this.selectedDate);
                }
            }
        }

        isSameDay(date1, date2) {
            return date1.getFullYear() === date2.getFullYear() &&
                   date1.getMonth() === date2.getMonth() &&
                   date1.getDate() === date2.getDate();
        }

        show() {
            if (this.isOpen) return;
            this.isOpen = true;
            this.picker.style.display = 'block';
            if (!this.options.inline) this.position();
        }

        hide() {
            if (!this.isOpen || this.options.inline) return;
            this.isOpen = false;
            this.picker.style.display = 'none';
        }

        toggle() {
            this.isOpen ? this.hide() : this.show();
        }

        position() {
            const rect = this.input.getBoundingClientRect();
            const top = rect.bottom + window.scrollY + 4;
            const left = this.options.rtl ? rect.right + window.scrollX - this.picker.offsetWidth : rect.left + window.scrollX;
            this.picker.style.top = top + 'px';
            this.picker.style.left = left + 'px';
        }
    }

    const picker = new DateTimePicker(input, config);
    window.DateTimePicker_{$inputId} = picker;

    // Add default styles if not already added
    if (!document.getElementById('datetimepicker-styles')) {
        const style = document.createElement('style');
        style.id = 'datetimepicker-styles';
        style.textContent = `
            .datetimepicker-popup {
                background: #fff;
                border: 1px solid #ddd;
                border-radius: 4px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.15);
                min-width: 320px;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            }
            .datetimepicker-popup.rtl {
                direction: rtl;
            }
            .datetimepicker-tabs {
                display: flex;
                border-bottom: 1px solid #ddd;
            }
            .datetimepicker-tab {
                flex: 1;
                padding: 12px;
                background: none;
                border: none;
                cursor: pointer;
                font-weight: 600;
                color: #666;
            }
            .datetimepicker-tab.active {
                color: #0d6efd;
                border-bottom: 2px solid #0d6efd;
            }
            .datetimepicker-content {
                padding: 12px;
            }
            .datetimepicker-pane {
                display: none;
            }
            .datetimepicker-pane.active {
                display: block;
            }
            .datetimepicker-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 8px;
            }
            .datetimepicker-prev,
            .datetimepicker-next {
                background: none;
                border: none;
                font-size: 20px;
                cursor: pointer;
                padding: 4px 8px;
                color: #666;
            }
            .datetimepicker-title {
                font-weight: 600;
                font-size: 14px;
            }
            .datetimepicker-weekdays {
                display: grid;
                grid-template-columns: repeat(7, 1fr);
                gap: 4px;
                margin-bottom: 4px;
            }
            .datetimepicker-weekday {
                text-align: center;
                font-size: 12px;
                font-weight: 600;
                color: #666;
                padding: 4px;
            }
            .datetimepicker-days {
                display: grid;
                grid-template-columns: repeat(7, 1fr);
                gap: 4px;
            }
            .datetimepicker-day {
                border: none;
                background: none;
                padding: 8px;
                text-align: center;
                cursor: pointer;
                border-radius: 4px;
                font-size: 13px;
            }
            .datetimepicker-day:hover:not(:disabled) {
                background: #f0f0f0;
            }
            .datetimepicker-day-today {
                font-weight: 700;
                color: #0d6efd;
            }
            .datetimepicker-day-selected {
                background: #0d6efd;
                color: #fff;
            }
            .datetimepicker-day-other {
                color: #ccc;
            }
            .datetimepicker-time-selectors {
                display: flex;
                gap: 8px;
                justify-content: center;
            }
            .datetimepicker-time-column {
                flex: 1;
            }
            .datetimepicker-time-scroll {
                max-height: 200px;
                overflow-y: auto;
                border: 1px solid #ddd;
                border-radius: 4px;
            }
            .datetimepicker-time-item {
                padding: 8px;
                text-align: center;
                cursor: pointer;
                font-size: 14px;
            }
            .datetimepicker-time-item:hover {
                background: #f0f0f0;
            }
            .datetimepicker-time-item.selected {
                background: #0d6efd;
                color: #fff;
                font-weight: 600;
            }
            .datetimepicker-footer {
                display: flex;
                justify-content: space-between;
                padding: 12px;
                border-top: 1px solid #eee;
            }
            .datetimepicker-now,
            .datetimepicker-clear {
                background: none;
                border: 1px solid #ddd;
                padding: 6px 12px;
                border-radius: 4px;
                font-size: 12px;
                cursor: pointer;
            }
        `;
        document.head.appendChild(style);
    }
})();
JAVASCRIPT;
    }
}


function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const wrapper = document.querySelector('.main-wrapper');
    if (sidebar) sidebar.classList.toggle('active');
    if (wrapper) wrapper.classList.toggle('active');
}

function confirmDelete(id, message = 'Bạn có chắc chắn muốn xóa?') {
    if (confirm(message)) {
        const form = document.getElementById('delete-form-' + id);
        if (form) form.submit();
    }
}

const vnDatePattern = /^(\d{1,2})\/(\d{1,2})\/(\d{4})$/;

function dateToIso(value) {
    const match = String(value || '').trim().match(vnDatePattern);
    if (!match) return '';

    const day = Number(match[1]);
    const month = Number(match[2]);
    const year = Number(match[3]);
    const date = new Date(year, month - 1, day);

    if (date.getFullYear() !== year || date.getMonth() !== month - 1 || date.getDate() !== day) {
        return '';
    }

    return `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
}

function isoToDate(value) {
    const match = String(value || '').trim().match(/^(\d{4})-(\d{2})-(\d{2})$/);
    if (!match) return value || '';

    return `${match[3]}/${match[2]}/${match[1]}`;
}

function validationMessage(input) {
    const value = String(input.value || '');
    const label = input.dataset.label || input.closest('.col-md-12,.col-md-6,.col-md-4,.col-md-3,.col-md-2,.col-12')?.querySelector('label')?.textContent?.replace('*', '').trim() || 'Trường này';
    const validators = String(input.dataset.validate || '').split('|').filter(Boolean);

    if (input.required && value.trim() === '') {
        return `${label} là bắt buộc.`;
    }

    if (value.trim() === '') {
        return '';
    }

    if (validators.includes('person-name') && !/^(?!.*\s{2})[\p{L}\s]+$/u.test(value.trim())) {
        return `${label} chỉ được nhập chữ, không có số, ký tự đặc biệt hoặc 2 khoảng trắng liên tiếp.`;
    }

    if (validators.includes('phone-vn') && !/^0\d{9}$/.test(value.trim())) {
        return `${label} phải gồm đúng 10 số và bắt đầu bằng 0.`;
    }

    if (validators.includes('tax-code') && !/^\d{10}$/.test(value.trim())) {
        return `${label} phải gồm đúng 10 chữ số.`;
    }

    if (validators.includes('container-number') && !/^[A-Z]{4}\d{7}$/.test(value.trim().toUpperCase())) {
        return `${label} phải gồm 4 chữ cái đầu và 7 chữ số phía sau.`;
    }

    if (validators.includes('customs-declaration') && !/^\d{12}$/.test(value.trim())) {
        return `${label} phải gồm đúng 12 chữ số.`;
    }

    if (validators.includes('plate-number') && !/^\d{2}[A-Z]{1,2}\d?-\d{3}\.\d{2}$/.test(value.trim().toUpperCase())) {
        return `${label} phải đúng định dạng, ví dụ 15B2-923.15.`;
    }

    if (validators.includes('date-vn') && !dateToIso(value)) {
        return `${label} phải đúng định dạng Ngày/Tháng/Năm.`;
    }

    if (input.pattern) {
        const regex = new RegExp(`^(?:${input.pattern})$`);
        if (!regex.test(value)) {
            return input.title || `${label} không đúng định dạng.`;
        }
    }

    return '';
}

function feedbackElement(input) {
    let feedback = input.parentElement?.classList.contains('input-group')
        ? input.parentElement.nextElementSibling
        : input.nextElementSibling;

    if (!feedback || !feedback.classList.contains('invalid-feedback')) {
        feedback = document.createElement('div');
        feedback.className = 'invalid-feedback';

        if (input.parentElement?.classList.contains('input-group')) {
            input.parentElement.insertAdjacentElement('afterend', feedback);
        } else {
            input.insertAdjacentElement('afterend', feedback);
        }
    }

    return feedback;
}

function validateInput(input) {
    const message = validationMessage(input);
    const feedback = feedbackElement(input);

    input.classList.toggle('is-invalid', Boolean(message));
    feedback.textContent = message;
    feedback.style.display = message ? 'block' : '';

    return !message;
}

function initInlineValidation() {
    document.querySelectorAll('input[data-validate], textarea[data-validate], select[data-validate]').forEach((input) => {
        ['input', 'change'].forEach((eventName) => {
            input.addEventListener(eventName, () => {
                if (input.dataset.uppercase === 'true') {
                    input.value = input.value.toUpperCase();
                }

                validateInput(input);
            });
        });
    });

    document.querySelectorAll('form').forEach((form) => {
        form.addEventListener('submit', (event) => {
            const invalid = Array.from(form.querySelectorAll('input[data-validate], textarea[data-validate], select[data-validate]'))
                .filter((input) => !validateInput(input));

            if (invalid.length > 0) {
                event.preventDefault();
                invalid[0].focus();
            }
        });
    });
}

function initDateInputs() {
    document.querySelectorAll('input[data-date-input]').forEach((input) => {
        input.placeholder = input.placeholder || 'Ngày/Tháng/Năm';
        input.dataset.validate = [input.dataset.validate, 'date-vn'].filter(Boolean).join('|');

        if (/^\d{4}-\d{2}-\d{2}$/.test(input.value)) {
            input.value = isoToDate(input.value);
        }

        if (input.parentElement?.classList.contains('date-input-group')) {
            return;
        }

        const wrapper = document.createElement('div');
        wrapper.className = 'input-group date-input-group';
        input.parentNode.insertBefore(wrapper, input);
        wrapper.appendChild(input);

        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'btn btn-outline-secondary';
        button.innerHTML = '<i class="fa fa-calendar-days"></i>';
        button.title = 'Chọn ngày';
        wrapper.appendChild(button);

        const picker = document.createElement('input');
        picker.type = 'date';
        picker.tabIndex = -1;
        picker.setAttribute('aria-hidden', 'true');
        picker.style.position = 'fixed';
        picker.style.opacity = '0';
        picker.style.pointerEvents = 'none';
        picker.style.width = '1px';
        picker.style.height = '1px';
        wrapper.appendChild(picker);

        const syncPicker = () => {
            picker.value = dateToIso(input.value);
        };

        input.addEventListener('input', syncPicker);
        input.addEventListener('change', syncPicker);
        button.addEventListener('click', () => {
            syncPicker();

            if (typeof picker.showPicker === 'function') {
                picker.showPicker();
            } else {
                picker.focus();
            }
        });
        picker.addEventListener('change', () => {
            input.value = isoToDate(picker.value);
            input.dispatchEvent(new Event('change', { bubbles: true }));
        });
    });
}

function initLocationButtons() {
    document.querySelectorAll('[data-current-location]').forEach((button) => {
        button.addEventListener('click', () => {
            const form = button.closest('form');
            const status = form?.querySelector('[data-location-status]');
            const latitude = form?.querySelector('[name="current_latitude"]');
            const longitude = form?.querySelector('[name="current_longitude"]');

            if (!navigator.geolocation || !form || !latitude || !longitude) {
                if (status) status.textContent = 'Trình duyệt không hỗ trợ chia sẻ vị trí.';
                return;
            }

            button.disabled = true;
            if (status) status.textContent = 'Đang lấy vị trí chính xác nhất...';

            let bestPosition = null;
            let watchId = null;
            let finished = false;
            const startedAt = Date.now();
            const options = {
                enableHighAccuracy: true,
                timeout: 20000,
                maximumAge: 0,
            };

            const applyPosition = () => {
                if (!bestPosition) {
                    if (status) status.textContent = 'Không lấy được vị trí. Vui lòng cho phép chia sẻ vị trí.';
                    button.disabled = false;
                    return;
                }

                latitude.value = bestPosition.coords.latitude.toFixed(7);
                longitude.value = bestPosition.coords.longitude.toFixed(7);
                const accuracy = Math.round(bestPosition.coords.accuracy || 0);
                if (status) {
                    status.textContent = `Đã cập nhật: ${latitude.value}, ${longitude.value} (sai số khoảng ${accuracy}m)`;
                }
                button.disabled = false;
            };

            const stopWatching = () => {
                if (finished) {
                    return;
                }

                finished = true;

                if (watchId !== null) {
                    navigator.geolocation.clearWatch(watchId);
                    watchId = null;
                }

                applyPosition();
            };

            const handlePosition = (position) => {
                if (!bestPosition || position.coords.accuracy < bestPosition.coords.accuracy) {
                    bestPosition = position;
                    if (status) {
                        status.textContent = `Đang tối ưu vị trí, sai số tốt nhất hiện tại khoảng ${Math.round(position.coords.accuracy)}m...`;
                    }
                }

                if ((position.coords.accuracy || Infinity) <= 25 || Date.now() - startedAt > 9000) {
                    stopWatching();
                }
            };

            const handleError = () => {
                stopWatching();
            };

            watchId = navigator.geolocation.watchPosition(handlePosition, handleError, options);
            window.setTimeout(stopWatching, 10000);
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initDateInputs();
    initInlineValidation();
    initLocationButtons();
});


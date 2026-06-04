// Toggles the sidebar on mobile screens
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const wrapper = document.querySelector('.main-wrapper');
    if (sidebar) sidebar.classList.toggle('active');
    if (wrapper) wrapper.classList.toggle('active');
}

// Global confirm delete helper (optional but cleaner)
function confirmDelete(id, message = 'Bạn có chắc chắn muốn xóa?') {
    if (confirm(message)) {
        const form = document.getElementById('delete-form-' + id);
        if (form) form.submit();
    }
}

// -----------------------------------------------------------------------
// Custom HTML5 Validation Tooltip
// -----------------------------------------------------------------------
(function () {
    function getLabelText(el) {
        if (el.labels && el.labels.length > 0) {
            return el.labels[0].textContent.replace('*', '').trim();
        }
        const parent = el.closest('div');
        if (parent) {
            const label = parent.querySelector('label');
            if (label) {
                return label.textContent.replace('*', '').trim();
            }
        }
        return el.name || 'này';
    }

    const MESSAGES = {
        valueMissing: (el) => `Trường "${getLabelText(el)}" không được để trống.`,
        typeMismatch: (el) => el.type === 'email' ? 'Địa chỉ email không hợp lệ (VD: user@example.com).' : 'Giá trị không đúng định dạng.',
        patternMismatch: (el) => el.title || 'Giá trị không đúng định dạng yêu cầu.',
        tooShort: (el) => `Tối thiểu ${el.minLength} ký tự.`,
        tooLong: (el) => `Tối đa ${el.maxLength} ký tự.`,
        rangeUnderflow: (el) => `Giá trị tối thiểu là ${el.min}.`,
        rangeOverflow: (el) => `Giá trị tối đa là ${el.max}.`,
        stepMismatch: () => 'Giá trị không đúng bội số cho phép.',
        badInput: () => 'Giá trị nhập không hợp lệ.',
    };

    function getErrorMessage(el) {
        const v = el.validity;
        for (const [key, fn] of Object.entries(MESSAGES)) {
            if (v[key]) return fn(el);
        }
        return 'Trường này không hợp lệ.';
    }

    function getWrap(el) {
        const ig = el.closest('.input-group');
        return ig ? ig.parentElement : el.parentElement;
    }

    function showError(el, message) {
        el.classList.add('is-invalid');
        el.classList.remove('is-valid');

        const wrap = getWrap(el);
        wrap.classList.add('field-error-wrap');

        // Xóa tooltip cũ nếu có
        const old = wrap.querySelector('.field-error-tooltip');
        if (old) old.remove();

        const tip = document.createElement('div');
        tip.className = 'field-error-tooltip';
        tip.innerHTML = '<i class="fa fa-exclamation-circle tip-icon"></i><span>' + message + '</span>';
        wrap.appendChild(tip);
    }

    function clearError(el) {
        el.classList.remove('is-invalid');
        el.classList.add('is-valid');

        const wrap = getWrap(el);
        const tip = wrap ? wrap.querySelector('.field-error-tooltip') : null;
        if (tip) tip.remove();
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Tìm tất cả các form trong trang
        const forms = document.querySelectorAll('form');

        forms.forEach(form => {
            // Tắt popup validation mặc định của trình duyệt HTML5
            form.setAttribute('novalidate', true);

            form.addEventListener('submit', function (e) {
                if (!form.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();

                    // Focus vào phần tử lỗi đầu tiên
                    const firstInvalid = form.querySelector(':invalid');
                    if (firstInvalid) firstInvalid.focus();
                }
                form.classList.add('was-validated');

                // Hiển thị tooltip cho tất cả các field lỗi
                Array.from(form.elements).forEach(el => {
                    if (el.willValidate) {
                        if (!el.validity.valid) {
                            showError(el, getErrorMessage(el));
                        } else {
                            clearError(el);
                        }
                    }
                });
            }, false);

            // Bắt sự kiện 'input' để xóa lỗi khi người dùng bắt đầu nhập lại
            Array.from(form.elements).forEach(el => {
                if (el.willValidate) {
                    el.addEventListener('input', () => {
                        if (form.classList.contains('was-validated')) {
                            if (el.checkValidity()) {
                                clearError(el);
                            } else {
                                showError(el, getErrorMessage(el));
                            }
                        }
                    });
                }
            });
        });
    });
})();

// Global Format Functions
window.formatName = function(input) {
    let val = input.value;
    let errorMsg = '';

    if (/^\s/.test(val)) {
        errorMsg = "Không được nhập khoảng trắng ở đầu.";
    }
    val = val.replace(/^\s+/, '');
    
    if (/\s{2,}/.test(val)) {
        errorMsg = "Chỉ được nhập 1 khoảng trắng giữa các từ.";
    }
    val = val.replace(/\s{2,}/g, ' ');

    if (/[^a-zA-ZÀÁÂÃÈÉÊÌÍÒÓÔÕÙÚĂĐĨŨƠàáâãèéêìíòóôõùúăđĩũơƯĂẠẢẤẦẨẪẬẮẰẲẴẶẸẺẼỀỀỂưăạảấầẩẫậắằẳẵặẹẻẽềềểỄỆỈỊỌỎỐỒỔỖỘỚỜỞỠỢỤỦỨỪễệỉịọỏốồổỗộớờởỡợụủứừỮỰỲỴÝỶỸửữựỳỵỷỹ\s]/g.test(val)) {
        errorMsg = "Chỉ được nhập chữ cái tiếng Việt, không số hoặc ký tự đặc biệt.";
    }
    val = val.replace(/[^a-zA-ZÀÁÂÃÈÉÊÌÍÒÓÔÕÙÚĂĐĨŨƠàáâãèéêìíòóôõùúăđĩũơƯĂẠẢẤẦẨẪẬẮẰẲẴẶẸẺẼỀỀỂưăạảấầẩẫậắằẳẵặẹẻẽềềểỄỆỈỊỌỎỐỒỔỖỘỚỜỞỠỢỤỦỨỪễệỉịọỏốồổỗộớờởỡợụủứừỮỰỲỴÝỶỸửữựỳỵỷỹ\s]/g, '');

    val = val.replace(/(?:^|\s)\S/g, function(a) { return a.toUpperCase(); });
    
    input.value = val;
    
    let errorDiv = document.getElementById(input.name + '_error') || document.getElementById(input.id + '_error');
    if (errorDiv) {
        if (errorMsg) {
            input.classList.add('is-invalid');
            errorDiv.innerText = errorMsg;
            errorDiv.style.display = 'block';
            input.setCustomValidity(errorMsg);
        } else {
            input.classList.remove('is-invalid');
            errorDiv.style.display = 'none';
            input.setCustomValidity('');
        }
    } else if (errorMsg) {
        input.setCustomValidity(errorMsg);
    } else {
        input.setCustomValidity('');
    }
};

window.formatPhone = function(input) {
    let val = input.value;
    let errorMsg = '';

    val = val.replace(/[^0-9]/g, '');
    
    if (val.length > 0 && val[0] !== '0') {
        val = ''; // xóa luôn nếu số đầu tiên khác 0
        errorMsg = "Số điện thoại phải bắt đầu bằng số 0.";
    } else if (val.length > 10) {
        val = val.substring(0, 10);
    } 
    
    if (val.length > 0 && val.length < 10) {
        errorMsg = "Số điện thoại phải đủ 10 số.";
    }
    
    input.value = val;
    
    let errorDiv = document.getElementById(input.name + '_error') || document.getElementById(input.id + '_error');
    if (errorDiv) {
        if (errorMsg) {
            input.classList.add('is-invalid');
            errorDiv.innerText = errorMsg;
            errorDiv.style.display = 'block';
            input.setCustomValidity(errorMsg);
        } else {
            input.classList.remove('is-invalid');
            errorDiv.style.display = 'none';
            input.setCustomValidity('');
        }
    } else if (errorMsg) {
        input.setCustomValidity(errorMsg);
    } else {
        input.setCustomValidity('');
    }
};

window.formatLicense = function(input) {
    let val = input.value;
    let errorMsg = '';

    val = val.replace(/[^0-9]/g, '');
    
    if (val.length > 12) {
        val = val.substring(0, 12);
    } 
    
    if (val.length > 0 && val.length < 12) {
        errorMsg = "GPLX phải đủ 12 số.";
    }
    
    input.value = val;
    
    let errorDiv = document.getElementById(input.name + '_error') || document.getElementById(input.id + '_error');
    if (errorDiv) {
        if (errorMsg) {
            input.classList.add('is-invalid');
            errorDiv.innerText = errorMsg;
            errorDiv.style.display = 'block';
            input.setCustomValidity(errorMsg);
        } else {
            input.classList.remove('is-invalid');
            errorDiv.style.display = 'none';
            input.setCustomValidity('');
        }
    } else if (errorMsg) {
        input.setCustomValidity(errorMsg);
    } else {
        input.setCustomValidity('');
    }
};

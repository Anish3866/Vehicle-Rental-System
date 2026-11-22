function luhnValidate(cardNumber) {

    const cleaned = cardNumber.replace(/[\s\-]/g, '');

    if (!/^\d+$/.test(cleaned)) {
        return false;
    }

    if (cleaned.length < 13 || cleaned.length > 19) {
        return false;
    }

    let sum = 0;
    let isEven = false;

    for (let i = cleaned.length - 1; i >= 0; i--) {
        let digit = parseInt(cleaned[i], 10);
        
        if (isEven) {
            digit *= 2;
            if (digit > 9) {
                digit -= 9;
            }
        }
        
        sum += digit;
        isEven = !isEven;
    }
    
    return (sum % 10) === 0;
}

function detectCardType(cardNumber) {
    const cleaned = cardNumber.replace(/[\s\-]/g, '');
    
    const patterns = [
        { type: 'visa',       name: 'Visa',             pattern: /^4/ },
        { type: 'mastercard', name: 'Mastercard',       pattern: /^5[1-5]/ },
        { type: 'amex',       name: 'American Express', pattern: /^3[47]/ },
        { type: 'discover',   name: 'Discover',         pattern: /^6(?:011|5)/ },
        { type: 'diners',     name: 'Diners Club',      pattern: /^3(?:0[0-5]|[68])/ },
        { type: 'jcb',        name: 'JCB',              pattern: /^35(?:2[89]|[3-8])/ }
    ];
    
    for (const card of patterns) {
        if (card.pattern.test(cleaned)) {
            return { type: card.type, name: card.name };
        }
    }
    
    return null;
}

function formatCardNumber(value) {
    const cleaned = value.replace(/\D/g, '');
    const groups = cleaned.match(/.{1,4}/g);
    return groups ? groups.join(' ') : cleaned;
}

function initCardValidation() {
    const cardInput = document.getElementById('card_number');
    const cardFeedback = document.getElementById('card_feedback');
    const cardTypeDisplay = document.getElementById('card_type');
    const cardForm = document.getElementById('card_payment_form');
    
    if (!cardInput) return;

    cardInput.addEventListener('input', function(e) {

        const cursorPos = this.selectionStart;
        const oldLength = this.value.length;
        this.value = formatCardNumber(this.value);
        const newLength = this.value.length;

        const diff = newLength - oldLength;
        this.setSelectionRange(cursorPos + diff, cursorPos + diff);
        
        const raw = this.value.replace(/\s/g, '');

        if (cardTypeDisplay) {
            const cardType = detectCardType(raw);
            if (cardType && raw.length >= 2) {
                cardTypeDisplay.textContent = cardType.name;
                cardTypeDisplay.className = 'card-type card-type-' + cardType.type;
            } else {
                cardTypeDisplay.textContent = '';
                cardTypeDisplay.className = 'card-type';
            }
        }

        if (cardFeedback) {
            if (raw.length >= 13) {
                if (luhnValidate(raw)) {
                    cardFeedback.textContent = '✓ Valid card number';
                    cardFeedback.className = 'card-feedback valid';
                    cardInput.classList.remove('is-invalid');
                    cardInput.classList.add('is-valid');
                } else {
                    cardFeedback.textContent = '✗ Invalid card number';
                    cardFeedback.className = 'card-feedback invalid';
                    cardInput.classList.remove('is-valid');
                    cardInput.classList.add('is-invalid');
                }
            } else {
                cardFeedback.textContent = '';
                cardFeedback.className = 'card-feedback';
                cardInput.classList.remove('is-valid', 'is-invalid');
            }
        }
    });

    const expiryInput = document.getElementById('card_expiry');
    if (expiryInput) {
        expiryInput.addEventListener('input', function(e) {
            let value = this.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            this.value = value;
        });
    }

    if (cardForm) {
        cardForm.addEventListener('submit', function(e) {
            const raw = cardInput.value.replace(/[\s\-]/g, '');
            const cardName = document.getElementById('card_name');
            const expiry = document.getElementById('card_expiry');
            let isValid = true;

            clearCardErrors();

            if (!raw || !luhnValidate(raw)) {
                showCardError(cardInput, 'Please enter a valid card number.');
                isValid = false;
            }

            if (cardName && !cardName.value.trim()) {
                showCardError(cardName, 'Cardholder name is required.');
                isValid = false;
            }

            if (expiry) {
                const expiryVal = expiry.value.trim();
                if (!expiryVal || !/^\d{2}\/\d{2}$/.test(expiryVal)) {
                    showCardError(expiry, 'Enter a valid expiry date (MM/YY).');
                    isValid = false;
                } else {
                    const [month, year] = expiryVal.split('/');
                    const expDate = new Date(2000 + parseInt(year), parseInt(month), 0);
                    if (expDate < new Date()) {
                        showCardError(expiry, 'Card has expired.');
                        isValid = false;
                    }
                }
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    }
}

function showCardError(input, message) {
    input.classList.add('is-invalid');
    const errorDiv = document.createElement('div');
    errorDiv.className = 'form-error';
    errorDiv.textContent = message;
    input.parentNode.appendChild(errorDiv);
}

function clearCardErrors() {
    document.querySelectorAll('#card_payment_form .is-invalid').forEach(el => {
        el.classList.remove('is-invalid');
    });
    document.querySelectorAll('#card_payment_form .form-error').forEach(el => {
        el.remove();
    });
}

document.addEventListener('DOMContentLoaded', initCardValidation);

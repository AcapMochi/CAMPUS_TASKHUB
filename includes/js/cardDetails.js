// AUTO SPACE CARD NUMBER

const cardInput = document.querySelector('#card-number');

cardInput.addEventListener('input', function (e) {

    let value = e.target.value.replace(/\s/g, '');
    value = value.replace(/[^0-9]/gi, '');

    let formatted = value.match(/.{1,4}/g);

    if (formatted) {
        e.target.value = formatted.join(' ');
    }

});


// VALIDATE FORM

const form = document.querySelector('#payment-form');

form.addEventListener('submit', function (e) {

    const cardNumber = document.querySelector('#card-number').value;
    const month = document.querySelector('#month').value;
    const year = document.querySelector('#year').value;
    const cvv = document.querySelector('#cvv').value;

    if (
        cardNumber.length < 19 ||
        month === '' ||
        year === '' ||
        cvv.length < 3
    ) {

        e.preventDefault();

        alert('Please complete all card details.');

    }

});
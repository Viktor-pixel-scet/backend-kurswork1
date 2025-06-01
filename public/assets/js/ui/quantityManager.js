function changeQuantity(delta) {
    const quantityInput = document.getElementById('quantity');
    let currentValue = parseInt(quantityInput.value);
    let newValue = currentValue + delta;

    if (newValue >= parseInt(quantityInput.min) && newValue <= parseInt(quantityInput.max)) {
        quantityInput.value = newValue;
    }
}

export { changeQuantity };
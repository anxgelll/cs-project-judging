(function () {
    const form = document.querySelector('[data-rubric-form]');
    if (!form) {
        return;
    }

    const scores = Array.from(form.querySelectorAll('[data-score]'));
    const totalOutput = form.querySelector('[data-total]');

    function updatePairs() {
        const groups = new Map();

        scores.forEach((input) => {
            const pair = input.dataset.pair;
            if (!groups.has(pair)) {
                groups.set(pair, []);
            }
            groups.get(pair).push(input);
        });

        groups.forEach((inputs) => {
            const filled = inputs.find((input) => input.value.trim() !== '');

            inputs.forEach((input) => {
                if (filled && input !== filled) {
                    input.disabled = true;
                    input.closest('td').classList.add('dimmed');
                } else {
                    input.disabled = false;
                    input.closest('td').classList.remove('dimmed');
                }
            });
        });

        const total = scores.reduce((sum, input) => {
            if (input.disabled || input.value.trim() === '') {
                return sum;
            }
            return sum + Number(input.value);
        }, 0);

        if (totalOutput) {
            totalOutput.value = String(total);
            totalOutput.textContent = String(total);
        }
    }

    scores.forEach((input) => {
        input.addEventListener('input', updatePairs);
    });

    updatePairs();
})();

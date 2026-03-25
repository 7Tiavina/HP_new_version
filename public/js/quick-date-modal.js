let qdm_temp_depot_date;
let qdm_temp_retrait_date;
let qdm_editing_mode = 'depot';
let qdmEscapeHandler;
// Use global t function from translations-simple.js (with fallback for safety)
if (typeof t === 'undefined') {
    var t = (key, fallback) => (window.translateKey ? window.translateKey(key, fallback) : (fallback || key));
}
let qdm_depot_day_selection = 'custom';
let qdm_retrait_day_selection = 'custom';

function openQuickDateModal() {
    const depotDate = document.getElementById('date-depot').value;
    const depotHeure = document.getElementById('heure-depot').value;
    const retraitDate = document.getElementById('date-recuperation').value;
    const retraitHeure = document.getElementById('heure-recuperation').value;

    qdm_temp_depot_date = new Date(`${depotDate}T${depotHeure}`);
    qdm_temp_retrait_date = new Date(`${retraitDate}T${retraitHeure}`);
    qdm_editing_mode = 'depot';

    const today = new Date();
    const pad = (num) => num.toString().padStart(2, '0');
    const todayFormatted = `${today.getFullYear()}-${pad(today.getMonth() + 1)}-${pad(today.getDate())}`;
    document.getElementById('qdm-custom-date-input').min = todayFormatted;
    const retraitDateFormatted = `${qdm_temp_retrait_date.getFullYear()}-${pad(qdm_temp_retrait_date.getMonth() + 1)}-${pad(qdm_temp_retrait_date.getDate())}`;
    document.getElementById('qdm-custom-date-input').max = retraitDateFormatted;

    const tomorrow = new Date();
    tomorrow.setDate(today.getDate() + 1);

    qdm_depot_day_selection = qdm_temp_depot_date.toDateString() === today.toDateString() ? 'today' :
        qdm_temp_depot_date.toDateString() === tomorrow.toDateString() ? 'tomorrow' : 'custom';
    qdm_retrait_day_selection = qdm_temp_retrait_date.toDateString() === today.toDateString() ? 'today' :
        qdm_temp_retrait_date.toDateString() === tomorrow.toDateString() ? 'tomorrow' : 'custom';

    updateQdmDisplay();
    generateHourButtons(qdm_temp_depot_date);
    document.getElementById('quick-date-modal').classList.remove('hidden');

    // Fermer avec Escape
    const qdmModal = document.getElementById('quick-date-modal');
    const removeEscape = () => {
        document.removeEventListener('keydown', qdmEscapeHandler);
    };
    qdmEscapeHandler = function(e) {
        if (e.key === 'Escape') {
            qdmModal.classList.add('hidden');
            removeEscape();
        }
    };
    document.addEventListener('keydown', qdmEscapeHandler);
}

function updateQdmDisplay() {
    document.getElementById('quick-depot-date-display').textContent = formatQdmDate(qdm_temp_depot_date);
    document.getElementById('quick-depot-time-display').textContent = qdm_temp_depot_date.toTimeString().substring(0, 5);
    document.getElementById('quick-retrait-date-display').textContent = formatQdmDate(qdm_temp_retrait_date);
    document.getElementById('quick-retrait-time-display').textContent = qdm_temp_retrait_date.toTimeString().substring(0, 5);

    const depotBlock = document.getElementById('quick-depot-block');
    const retraitBlock = document.getElementById('quick-retrait-block');
    const editingLabel = document.getElementById('qdm-editing-label');

    if (qdm_editing_mode === 'depot') {
        depotBlock.classList.add('border-yellow-custom', 'border-2');
        retraitBlock.classList.remove('border-yellow-custom', 'border-2');
        editingLabel.textContent = 'Date de Dépôt';
    } else {
        retraitBlock.classList.add('border-yellow-custom', 'border-2');
        depotBlock.classList.remove('border-yellow-custom', 'border-2');
        editingLabel.textContent = 'Date de Retrait';
    }

    document.querySelectorAll('.qdm-day-btn').forEach(btn => {
        btn.classList.remove('bg-yellow-custom', 'text-gray-dark');
        btn.classList.add('bg-gray-200');
    });

    const currentSelection = (qdm_editing_mode === 'depot') ? qdm_depot_day_selection : qdm_retrait_day_selection;
    const selectedBtn = document.querySelector(`.qdm-day-btn[data-day="${currentSelection}"]`);
    if (selectedBtn) {
        selectedBtn.classList.remove('bg-gray-200');
        selectedBtn.classList.add('bg-yellow-custom', 'text-gray-dark');
    }

    const customDateContainer = document.getElementById('qdm-custom-date-container');
    const customHourContainer = document.getElementById('qdm-custom-hour-container');
    const hourGrid = document.getElementById('qdm-hour-grid');

    if (currentSelection === 'custom') {
        customDateContainer.classList.remove('hidden');
        hourGrid.classList.add('hidden');
        customHourContainer.classList.remove('hidden');
        const currentTempDate = (qdm_editing_mode === 'depot') ? qdm_temp_depot_date : qdm_temp_retrait_date;
        const pad = (num) => num.toString().padStart(2, '0');
        document.getElementById('qdm-custom-date-input').value = `${currentTempDate.getFullYear()}-${pad(currentTempDate.getMonth() + 1)}-${pad(currentTempDate.getDate())}`;
        document.getElementById('qdm-custom-time-input').value = `${pad(currentTempDate.getHours())}:${pad(currentTempDate.getMinutes())}`;
    } else {
        customDateContainer.classList.add('hidden');
        hourGrid.classList.remove('hidden');
        customHourContainer.classList.add('hidden');
        generateHourButtons((qdm_editing_mode === 'depot') ? qdm_temp_depot_date : qdm_temp_retrait_date);
    }
}

function generateHourButtons(date) {
    const hourGrid = document.getElementById('qdm-hour-grid');
    hourGrid.innerHTML = '';

    let startHour = 0;
    const isToday = new Date().toDateString() === date.toDateString();
    const isSameDayAsDepot = date.toDateString() === qdm_temp_depot_date.toDateString();

    if (qdm_editing_mode === 'retrait' && isSameDayAsDepot) {
        startHour = qdm_temp_depot_date.getHours() + 3;
    } else if (isToday) {
        startHour = new Date().getHours() + 1;
    }

    const selectedHour = date.getHours();

    for (let i = 8; i <= 20; i++) {
        const hour = i.toString().padStart(2, '0') + ':00';
        const button = document.createElement('button');
        button.textContent = hour;
        button.classList.add('qdm-hour-btn', 'py-2', 'px-2', 'bg-white', 'rounded-md', 'border', 'border-gray-300', 'hover:bg-gray-100');
        button.dataset.hour = hour;

        let isDisabled = false;
        if (i < startHour) {
            isDisabled = true;
            button.disabled = true;
            button.classList.add('bg-gray-100', 'text-gray-400', 'cursor-not-allowed');
        }

        if (i === selectedHour && !isDisabled) {
            button.classList.add('bg-yellow-custom', 'text-gray-dark', 'font-bold');
        }

        hourGrid.appendChild(button);
    }
}

function setupQdmListeners() {
    document.getElementById('dates-display').addEventListener('click', openQuickDateModal);
    document.getElementById('close-quick-date-modal').addEventListener('click', () => {
        document.getElementById('quick-date-modal').classList.add('hidden');
        if (typeof qdmEscapeHandler === 'function') {
            document.removeEventListener('keydown', qdmEscapeHandler);
        }
    });

    document.getElementById('quick-depot-block').addEventListener('click', () => {
        qdm_editing_mode = 'depot';
        const today = new Date();
        const pad = (num) => num.toString().padStart(2, '0');
        const todayFormatted = `${today.getFullYear()}-${pad(today.getMonth() + 1)}-${pad(today.getDate())}`;
        document.getElementById('qdm-custom-date-input').min = todayFormatted;

        const retraitDateFormatted = `${qdm_temp_retrait_date.getFullYear()}-${pad(qdm_temp_retrait_date.getMonth() + 1)}-${pad(qdm_temp_retrait_date.getDate())}`;
        document.getElementById('qdm-custom-date-input').max = retraitDateFormatted;

        document.getElementById('qdm-custom-time-input').min = '';
        updateQdmDisplay();
        generateHourButtons(qdm_temp_depot_date);
    });

    document.getElementById('quick-retrait-block').addEventListener('click', () => {
        qdm_editing_mode = 'retrait';
        const pad = (num) => num.toString().padStart(2, '0');
        const depotDateFormatted = `${qdm_temp_depot_date.getFullYear()}-${pad(qdm_temp_depot_date.getMonth() + 1)}-${pad(qdm_temp_depot_date.getDate())}`;
        document.getElementById('qdm-custom-date-input').min = depotDateFormatted;

        const maxRetraitDate = new Date(qdm_temp_depot_date);
        maxRetraitDate.setDate(maxRetraitDate.getDate() + 30);
        const maxRetraitDateFormatted = `${maxRetraitDate.getFullYear()}-${pad(maxRetraitDate.getMonth() + 1)}-${pad(maxRetraitDate.getDate())}`;
        document.getElementById('qdm-custom-date-input').max = maxRetraitDateFormatted;

        if (qdm_temp_retrait_date.toDateString() === qdm_temp_depot_date.toDateString()) {
            const minRetraitHour = qdm_temp_depot_date.getHours() + 3;
            document.getElementById('qdm-custom-time-input').min = `${pad(minRetraitHour)}:00`;
        } else {
            document.getElementById('qdm-custom-time-input').min = '';
        }

        updateQdmDisplay();
        generateHourButtons(qdm_temp_retrait_date);
    });

    document.querySelectorAll('.qdm-day-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const day = e.target.dataset.day;

            if (qdm_editing_mode === 'depot') {
                qdm_depot_day_selection = day;
            } else {
                qdm_retrait_day_selection = day;
            }

            if (day === 'today' || day === 'tomorrow') {
                let targetDate = new Date();
                if (day === 'tomorrow') {
                    targetDate.setDate(targetDate.getDate() + 1);
                }

                const dateToUpdate = (qdm_editing_mode === 'depot') ? qdm_temp_depot_date : qdm_temp_retrait_date;
                dateToUpdate.setFullYear(targetDate.getFullYear(), targetDate.getMonth(), targetDate.getDate());

                let defaultHour = 7;
                let defaultMinute = 1;

                if (day === 'today') {
                    const now = new Date();
                    const nextTime = new Date(now.getTime() + 60 * 1000);

                    if (nextTime.getHours() > defaultHour || (nextTime.getHours() === defaultHour && nextTime.getMinutes() > defaultMinute)) {
                        defaultHour = nextTime.getHours();
                        defaultMinute = nextTime.getMinutes();
                    }
                }

                if (qdm_editing_mode === 'retrait' && dateToUpdate.toDateString() === qdm_temp_depot_date.toDateString()) {
                    let minRetraitHour = qdm_temp_depot_date.getHours() + 3;
                    let minRetraitMinute = qdm_temp_depot_date.getMinutes();

                    if (minRetraitHour > defaultHour || (minRetraitHour === defaultHour && minRetraitMinute > defaultMinute)) {
                        defaultHour = minRetraitHour;
                        defaultMinute = minRetraitMinute;
                    }
                }

                if (defaultHour < 7 || (defaultHour === 7 && defaultMinute < 1)) {
                    defaultHour = 7;
                    defaultMinute = 1;
                }
                if (defaultHour > 21) {
                    defaultHour = 21;
                    defaultMinute = 0;
                }

                dateToUpdate.setHours(defaultHour, defaultMinute, 0, 0);
            }

            if (qdm_editing_mode === 'depot' && qdm_temp_depot_date >= qdm_temp_retrait_date) {
                qdm_temp_retrait_date = new Date(qdm_temp_depot_date);
                let newHour = qdm_temp_retrait_date.getHours() + 3;
                let newMinute = qdm_temp_retrait_date.getMinutes();

                if (newHour < 7 || (newHour === 7 && newMinute < 1)) {
                    newHour = 7;
                    newMinute = 1;
                }
                if (newHour > 21 || (newHour === 21 && newMinute > 0)) {
                    qdm_temp_retrait_date.setDate(qdm_temp_retrait_date.getDate() + 1);
                    newHour = 7;
                    newMinute = 1;
                }
                qdm_temp_retrait_date.setHours(newHour, newMinute, 0, 0);
            } else if (qdm_editing_mode === 'retrait' && qdm_temp_depot_date.toDateString() === qdm_temp_retrait_date.toDateString()) {
                const depotTimeInMinutes = qdm_temp_depot_date.getHours() * 60 + qdm_temp_depot_date.getMinutes();
                const retraitTimeInMinutes = qdm_temp_retrait_date.getHours() * 60 + qdm_temp_retrait_date.getMinutes();

                if (retraitTimeInMinutes < depotTimeInMinutes + 3 * 60) {
                    const requiredRetraitTime = new Date(qdm_temp_depot_date.getTime() + 3 * 60 * 60 * 1000);
                    let newRetraitHour = requiredRetraitTime.getHours();
                    let newRetraitMinute = requiredRetraitTime.getMinutes();

                    if (newRetraitHour < 7 || (newRetraitHour === 7 && newRetraitMinute < 1)) {
                        newRetraitHour = 7;
                        newRetraitMinute = 1;
                    }
                    if (newRetraitHour > 21 || (newRetraitHour === 21 && newRetraitMinute > 0)) {
                        qdm_temp_retrait_date.setDate(qdm_temp_retrait_date.getDate() + 1);
                        newRetraitHour = 7;
                        newRetraitMinute = 1;
                    }
                    qdm_temp_retrait_date.setHours(newRetraitHour, newRetraitMinute, 0, 0);
                }
            }

            updateQdmDisplay();
        });
    });

    document.getElementById('qdm-custom-date-input').addEventListener('change', (e) => {
        const newDate = new Date(e.target.value);
        const newDateUTC = new Date(newDate.getUTCFullYear(), newDate.getUTCMonth(), newDate.getUTCDate());

        if (qdm_editing_mode === 'depot') {
            qdm_temp_depot_date.setFullYear(newDateUTC.getFullYear(), newDateUTC.getMonth(), newDateUTC.getDate());
            if (qdm_temp_depot_date > qdm_temp_retrait_date) {
                qdm_temp_retrait_date = new Date(qdm_temp_depot_date);
            }
        } else {
            qdm_temp_retrait_date.setFullYear(newDateUTC.getFullYear(), newDateUTC.getMonth(), newDateUTC.getDate());
        }
        updateQdmDisplay();
        if (qdm_editing_mode === 'retrait') {
            const pad = (num) => num.toString().padStart(2, '0');
            if (qdm_temp_retrait_date.toDateString() === qdm_temp_depot_date.toDateString()) {
                const minRetraitHour = qdm_temp_depot_date.getHours() + 3;
                document.getElementById('qdm-custom-time-input').min = `${pad(minRetraitHour)}:00`;
            } else {
                document.getElementById('qdm-custom-time-input').min = '';
            }
        }
    });

    document.getElementById('qdm-hour-grid').addEventListener('click', (e) => {
        if (e.target.classList.contains('qdm-hour-btn') && !e.target.disabled) {
            const hour = e.target.dataset.hour.split(':')[0];
            const minute = e.target.dataset.hour.split(':')[1];
            if (qdm_editing_mode === 'depot') {
                qdm_temp_depot_date.setHours(hour, minute);
                if (qdm_temp_depot_date.toDateString() === qdm_temp_retrait_date.toDateString()) {
                    const minRetraitTime = new Date(qdm_temp_depot_date.getTime() + 3 * 60 * 60 * 1000);
                    if (qdm_temp_retrait_date < minRetraitTime) {
                        qdm_temp_retrait_date = minRetraitTime;
                    }
                }
            } else {
                qdm_temp_retrait_date.setHours(hour, minute);
            }
            updateQdmDisplay();
        }
    });

    document.getElementById('qdm-custom-time-input').addEventListener('change', (e) => {
        const [hour, minute] = e.target.value.split(':');
        if (qdm_editing_mode === 'depot') {
            qdm_temp_depot_date.setHours(hour, minute);
            if (qdm_temp_depot_date.toDateString() === qdm_temp_retrait_date.toDateString()) {
                const minRetraitTime = new Date(qdm_temp_depot_date.getTime() + 3 * 60 * 60 * 1000);
                if (qdm_temp_retrait_date < minRetraitTime) {
                    qdm_temp_retrait_date = minRetraitTime;
                }
            }
        } else {
            qdm_temp_retrait_date.setHours(hour, minute);
        }
        updateQdmDisplay();
    });

    document.getElementById('qdm-validate-btn').addEventListener('click', async () => {
        const validateBtn = document.getElementById('qdm-validate-btn');
        const loader = document.getElementById('loader');

        validateBtn.disabled = true;
        loader.classList.remove('hidden');

        try {
            if (qdm_temp_retrait_date <= qdm_temp_depot_date) {
                await showCustomAlert(t('date_invalid_title'), t('date_invalid_after_dropoff'));
                return;
            }

            if (qdm_temp_depot_date.toDateString() === qdm_temp_retrait_date.toDateString()) {
                const diffInMs = qdm_temp_retrait_date - qdm_temp_depot_date;
                const diffInHours = diffInMs / (1000 * 60 * 60);
                if (diffInHours < 3) {
                    await showCustomAlert(t('date_invalid_title'), t('date_invalid_same_day_min'));
                    return;
                }
            }

            const pad = (num) => num.toString().padStart(2, '0');
            document.getElementById('date-depot').value = `${qdm_temp_depot_date.getFullYear()}-${pad(qdm_temp_depot_date.getMonth() + 1)}-${pad(qdm_temp_depot_date.getDate())}`;
            document.getElementById('heure-depot').value = `${pad(qdm_temp_depot_date.getHours())}:${pad(qdm_temp_depot_date.getMinutes())}`;
            document.getElementById('date-recuperation').value = `${qdm_temp_retrait_date.getFullYear()}-${pad(qdm_temp_retrait_date.getMonth() + 1)}-${pad(qdm_temp_retrait_date.getDate())}`;
            document.getElementById('heure-recuperation').value = `${pad(qdm_temp_retrait_date.getHours())}:${pad(qdm_temp_retrait_date.getMinutes())}`;

            displaySelectedDates();
            
            // Réinitialiser les contraintes avant de recalculer
            if (typeof resetContraintes === 'function') {
                resetContraintes();
            }
            
            const isAvailable = await checkAvailability();

            if (isAvailable) {
                document.getElementById('quick-date-modal').classList.add('hidden');
                if (typeof qdmEscapeHandler === 'function') {
                    document.removeEventListener('keydown', qdmEscapeHandler);
                }
                await getQuoteAndDisplay();
            }

            saveStateToSession();
        } catch (error) {
            console.error('Erreur lors de la validation des dates:', error);
            await showCustomAlert(t('error'), t('date_update_error'));
        } finally {
            validateBtn.disabled = false;
            loader.classList.add('hidden');
        }
    });
}
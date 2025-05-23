const STATE = {
    configFP: {
        enableTime: true,
        altInput: true,
        altFormat: "F j, Y h:i K",
        dateFormat: "Y-m-d H:i",
        minDate: "today",
    },
    pickUpFP: null,
    returnFP: null,
    emailRegEx: /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/,
    phoneRegEx: /[^0-9+().\- ]/
};

$(function () {
    STATE.pickUpFP = $("#pick-up-flatpickr").flatpickr?.(STATE.configFP);
    STATE.returnFP = $("#return-flatpickr").flatpickr?.(STATE.configFP);

    $(".checkbox-container").on('click', function () {
        const checkbox = $(this).find('input');
        const isChecked = checkbox.prop('checked');
        const onReservationPage = $('body').prop('id') === 'reservation-page' || $('body').prop('id') === 'taxi-page';
        const elementToToggle = onReservationPage ? $('.custom-select.return').parent('div') : $('.custom-select.return');

        checkbox.prop('checked', !isChecked);
        elementToToggle.slideToggle(!isChecked);
    });

    $(".custom-select").on('click', function () {
        $(".custom-select").removeClass('active');
        $(this).addClass('active');
        $('html').addClass('viewing-custom-select-options');
    });

    $(".custom-select-options span").on('click', function (e) {
        e.stopPropagation();
        $('html').removeClass('viewing-custom-select-options');
        $(this).siblings().removeClass('selected');
        $(this).addClass('selected');
        const option = $(this).text();
        $(".custom-select.active > span").text(option);
        $(".custom-select").removeClass('active');
    });

    $("body .overlay").on('click', function () {
        if ($('html').hasClass('viewing-custom-select-options')) {
            $('html').removeClass('viewing-custom-select-options');
        }
        if ($('html').hasClass('viewing-hamburger-menu')) {
            $('html').removeClass('viewing-hamburger-menu');
        }
    });

    $("#intro-section form").on('submit', async function (e) {
        // prevent the form from submitting
        e.preventDefault();

        $(".form-input").removeClass('form-error');

        const returnToSameLocation = $("#return-to-same-location").prop('checked');
        const pickUpLocation = $(".pick-up .custom-select-options span.selected").text();
        const pickUpTS = STATE.pickUpFP.selectedDates[0]?.getTime?.();
        const returnTS = STATE.returnFP.selectedDates[0]?.getTime?.();

        const data = {
            action: "itinerary",
            pickUpLocation,
            returnLocation: returnToSameLocation ? pickUpLocation : $(".return .custom-select-options span.selected").text(),
            returnToSameLocation: {
                checked: returnToSameLocation,
                value: returnToSameLocation ? "on" : "off"
            },
            pickUpDate: {
                date: STATE.pickUpFP.selectedDates[0]?.toISOString?.(),
                ts: STATE.pickUpFP.selectedDates[0]?.getTime?.(),
                value: STATE.pickUpFP.input.value,
                altValue: STATE.pickUpFP.altInput.value
            },
            returnDate: {
                date: STATE.returnFP.selectedDates[0]?.toISOString?.(),
                ts: STATE.returnFP.selectedDates[0]?.getTime?.(),
                value: STATE.returnFP.input.value,
                altValue: STATE.returnFP.altInput.value
            },
            step: 2,
            days: getDifferenceInDays(pickUpTS, returnTS)
        };

        const formDataIsValid = handleInvalidFormData(data, "itinerary");

        if (!formDataIsValid) return;

        location.href = `/reservation/?itinerary[pickUpLocation]=${data.pickUpLocation}&itinerary[returnLocation]=${data.returnLocation}&itinerary[returnToSameLocation][checked]=${returnToSameLocation + 0}&itinerary[returnToSameLocation][value]=${returnToSameLocation ? "on" : "off"}&itinerary[pickUpDate][date]=${data.pickUpDate.date}&itinerary[pickUpDate][ts]=${data.pickUpDate.ts}&itinerary[pickUpDate][value]=${data.pickUpDate.value}&itinerary[pickUpDate][altValue]=${data.pickUpDate.altValue}&itinerary[returnDate][date]=${data.returnDate.date}&itinerary[returnDate][ts]=${data.returnDate.ts}&itinerary[returnDate][value]=${data.returnDate.value}&itinerary[returnDate][altValue]=${data.returnDate.altValue}&step=2&itinerary[days]=${data.days}`;
    });

    $(".faq").on('click', function () {
        $(this).toggleClass('open');
        $(this).find('.faq-answer').slideToggle();
    });

    $(".reservation-step").on('click', async function () {
        if ($(this).hasClass('active')) return;
        const step = $(this).data('step');
        $(".reservation-step").removeClass('active');
        $(this).addClass('active');
        const goToFirstStep = () => {
            $(".booking-flow-section").hide();
            $(`section[data-step="${step}"]`).first().show();
        };

        if (step === 2) {
            const ReservationSessionRes = await fetch('/includes/reservation.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',  // Set Content-Type to JSON
                },
                body: JSON.stringify({ action: "get_reservation" })
            });

            const reservation = await ReservationSessionRes.json();
            if (reservation.vehicle) return goToAddOns();
            goToFirstStep();
        } else {
            goToFirstStep();
        }
    });

    $(".vehicle-container .continue-btn").on('click', async function () {
        const vehicleContainer = $(this).closest('.vehicle-container');
        const id = vehicleContainer.data('vehicle-id');
        const name = vehicleContainer.find('.vehicle-name').text();
        const imgSrc = vehicleContainer.children('img').attr('src');
        const data = {
            action: "vehicle",
            id,
        };

        const ReservationSessionRes = await fetch('/includes/reservation.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',  // Set Content-Type to JSON
            },
            body: JSON.stringify(data)
        });

        const reservation = await ReservationSessionRes.json();

        await Swal.fire({
            title: `Choosing ${name} (${reservation.vehicle.type})...`,
            timer: 1500,
            didOpen: () => Swal.showLoading()
        });

        $('.vehicle-container').removeClass('active');
        vehicleContainer
            .addClass('active')
            .find('.continue-btn').text('CONTINUE');

        const insuranceStr = `$${reservation.vehicle.insurance}/day`;

        $(".reservation-step.vehicle-add-on .body>div:first h6, #reservation-summary>h5").text(name);
        $(".reservation-step.vehicle-add-on .body>div:first p, #reservation-summary>h6").text(`${reservation.vehicle.type} - USD${insuranceStr} Insurance`);
        $('.add-on-container[data-add-on-name="Collision Insurance"] h2').text(`Collision Insurance - ${insuranceStr}`);
        $("#reservation-summary div.car.summary").html(`<img src="${imgSrc}" alt="${name}">`);

        let priceDay = Number(reservation.vehicle.base_price_USD);
        if (reservation.discount) {
            priceDay = Number(reservation.discount.price_USD);
        }

        const rate = {
            days: reservation.itinerary ? reservation.itinerary.days : 1,
            rate: makePriceString(priceDay),
            subtotal: makePriceString(priceDay)
        };

        if (reservation.itinerary) {
            rate.subtotal = makePriceString(priceDay, rate.days);
        }

        let rateHTML = rate.rate;
        if (reservation.discount) rateHTML += `<div class="discount-tool-tip">i<div><span>Fixed price:</span><span><b>${reservation.discount.days}</b> days or more: <b>${rate.rate}</b></span></div></div>`;

        $("#reservation-summary div.rate.summary").html(`
            <h6>Rate</h6>
            <table>
                <thead>
                    <tr>
                        <th>Day(s)</th>
                        <th>Rate</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>${rate.days}</td>
                        <td>${rateHTML}</td>
                        <td>${rate.subtotal}</td>
                    </tr>
                    <tr>
                        <td colspan="2">Rental Subtotal</td>
                        <td>${rate.subtotal}</td>
                    </tr>
                </tbody>
            </table>
        `);

        const totalAddOnsCost = getAddOnsSubTotal(reservation);

        if (reservation.add_ons) {
            let rows = '', html = '', count = 0;
            const days = reservation?.itinerary?.days;

            for (const id in reservation.add_ons) {
                count++;
                const addOn = reservation.add_ons[id];
                let addOnRate = addOn.cost;
                let nameTdString = `1 x ${addOn.name}`;
                if (addOn.fixed_price !== "1") nameTdString += ` for ${days || 1} day(s)`;
                if (addOn.name === "Collision Insurance") {
                    if (reservation.vehicle) {
                        addOnRate = reservation.vehicle.insurance;
                    } else {
                        addOnRate = 0;
                    }
                }
                rows += `
                    <tr data-id="${addOn.id}">
                        <td>${nameTdString}</td>
                        <td>${makePriceString(addOnRate)}</td>
                        <td>${makePriceString(getAddOnCostForTotalDays(addOn, days, reservation.vehicle))}</td>
                    </tr>
                `;
            }

            if (rows.length) {
                html = `
                <h6>Add-ons</h6>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Rate</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${rows}
                        <tr>
                            <td>Add-ons Subtotal</td>
                            <td></td>
                            <td>${makePriceString(totalAddOnsCost)}</td>
                        </tr>
                    </tbody>
                </table>
            `
            } else {
                html = `
                <h6>Add-ons</h6>
                <div><span>--</span><span>--</span><span>--</span></div>
            `;
            }

            $("#reservation-summary div.add-ons.summary").html(html);
        }

        const rentalSubtotal = parseInt(priceDay) * rate.days;
        $("#reservation-summary .estimated-total span:last-child").text(makePriceString(totalAddOnsCost + rentalSubtotal));

        goToAddOns();
    });

    $(".more-add-on-info").on('click', function () {
        const container = $(this).closest('.add-on-container')
        const viewingInfo = container.hasClass('viewing-info');

        container.toggleClass('viewing-info', !viewingInfo);
        container.children('p').slideToggle(!viewingInfo);
    });

    $(".add-on-btn").on('click', async function () {
        const addOnContainer = $(this).closest('.add-on-container');
        const data = {
            action: $(this).hasClass('added') ? "remove_add_on" : "add_add_on",
            id: addOnContainer.data('id')
        };

        const ReservationSessionRes = await fetch('/includes/reservation.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',  // Set Content-Type to JSON
            },
            body: JSON.stringify(data)
        });

        const reservation = await ReservationSessionRes.json();

        let priceDay = Number(reservation.vehicle.base_price_USD);
        if (reservation.discount) {
            priceDay = Number(reservation.discount.price_USD);
        }

        const days = reservation.itinerary ? reservation.itinerary.days : 1;

        let rows = '', spans = '', html = '', count = 0;

        for (const id in reservation.add_ons) {
            count++;
            const addOn = reservation.add_ons[id];
            let addOnRate = addOn.cost;
            let nameTdString = `1 x ${addOn.name}`;
            if (addOn.fixed_price != "1") nameTdString += ` for ${days} day(s)`;
            if (addOn.name === "Collision Insurance") {
                if (reservation.vehicle) {
                    addOnRate = reservation.vehicle.insurance;
                } else {
                    addOnRate = 0;
                }
            }

            rows += `
                <tr data-id="${addOn.id}">
                    <td>${nameTdString}</td>
                    <td>${makePriceString(addOnRate)}</td>
                    <td>${makePriceString(getAddOnCostForTotalDays(addOn, days, reservation.vehicle))}</td>
                </tr>
            `;

            spans += `${count > 1 ? ", " : ""}<span data-id="${addOn.id}">${addOn.abbr}</span>`;
        }

        // Calculate and append the total cost row
        const totalAddOnsCost = getAddOnsSubTotal(reservation);

        if (rows.length) {
            html = `
                <h6>Add-ons</h6>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Rate</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${rows}
                        <tr>
                            <td>Add-ons Subtotal</td>
                            <td></td>
                            <td>${makePriceString(totalAddOnsCost)}</td>
                        </tr>
                    </tbody>
                </table>
            `
        } else {
            html = `
                <h6>Add-ons</h6>
                <div><span>--</span><span>--</span><span>--</span></div>
            `;
        }

        const rentalSubtotal = parseInt(priceDay) * days;
        $("#reservation-summary div.add-ons.summary").html(html);
        $(".reservation-step.vehicle-add-on .body > div:last-child p").html(spans || "--");
        $("#reservation-summary .estimated-total span:last-child").text(makePriceString(totalAddOnsCost + rentalSubtotal));

        if ($(this).hasClass('added')) {
            $(this).removeClass('added');
            $(this).addClass('show-removed');
            setTimeout(() => $(this).removeClass('show-removed'), 1000);
        } else {
            $(this).addClass('added show-added');
            setTimeout(() => $(this).removeClass('show-added'), 1000);
        }
    });

    $(".change-car-btn, .car.summary > div").on('click', function () {
        $(".reservation-step").removeClass('active');
        $(".reservation-step.vehicle-add-on").addClass('active');
        $(".booking-flow-section").hide();
        $("#vehicle-selection-section").show();
    });

    $("#vehicle-add-ons .continue-btn:not(.change-car-btn)").on('click', function () {
        $(".reservation-step[data-step='3'] .header").click();
    });

    $("#itinerary-section .continue-btn:not(.change-car-btn)").on('click', async function () {

        $(".form-input").removeClass('form-error');

        const returnToSameLocation = $("#return-to-same-location").prop('checked');
        const pickUpLocation = $(".reservation-flow-container .pick-up .custom-select-options span.selected").text();

        const data = {
            action: "itinerary",
            pickUpLocation,
            returnLocation: returnToSameLocation ? pickUpLocation : $(".reservation-flow-container .return .custom-select-options span.selected").text(),
            returnToSameLocation: {
                checked: returnToSameLocation,
                value: returnToSameLocation ? "on" : "off"
            },
            pickUpDate: {
                date: STATE.pickUpFP.selectedDates[0],
                ts: STATE.pickUpFP.selectedDates[0]?.getTime?.(),
                value: STATE.pickUpFP.input.value,
                altValue: STATE.pickUpFP.altInput.value
            },
            returnDate: {
                date: STATE.returnFP.selectedDates[0],
                ts: STATE.returnFP.selectedDates[0]?.getTime?.(),
                value: STATE.returnFP.input.value,
                altValue: STATE.returnFP.altInput.value
            },
            step: 2
        };

        const formDataIsValid = handleInvalidFormData(data, "itinerary");

        if (!formDataIsValid) return;

        const ReservationSessionRes = await fetch('/includes/reservation.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',  // Set Content-Type to JSON
            },
            body: JSON.stringify(data)
        });

        const reservation = await ReservationSessionRes.json();

        // update itinerary section
        $(".reservation-step.itinerary .body>div:first-child p").text(`${data.pickUpLocation} - ${data.pickUpDate.altValue}`);
        $(".reservation-step.itinerary .body>div:last-child p").text(`${data.returnLocation} - ${data.returnDate.altValue}`);

        // head to vehicle selection section
        await Swal.fire({
            title: "Setting Itinerary...",
            timer: 1000,
            didOpen: () => Swal.showLoading()
        });

        $(".reservation-step.vehicle-add-on .header").trigger('click');

        if (reservation.vehicle) {

            let priceDay = Number(reservation.vehicle.base_price_USD);
            if (reservation.discount) {
                priceDay = Number(reservation.discount.price_USD);
            }

            const rate = {
                days: reservation.itinerary.days,
                rate: makePriceString(priceDay),
                subtotal: makePriceString(priceDay, reservation.itinerary.days)
            };

            let rateHTML = rate.rate;
            if (reservation.discount) rateHTML += `<div class="discount-tool-tip">i<div><span>Fixed price:</span><span><b>${reservation.discount.days}</b> days or more: <b>${rate.rate}</b></span></div></div>`;

            $("#reservation-summary div.rate.summary").html(`
                <h6>Rate</h6>
                <table>
                    <thead>
                        <tr>
                            <th>Day(s)</th>
                            <th>Rate</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>${rate.days}</td>
                            <td>${rateHTML}</td>
                            <td>${rate.subtotal}</td>
                        </tr>
                        <tr>
                            <td colspan="2">Rental Subtotal</td>
                            <td>${rate.subtotal}</td>
                        </tr>
                    </tbody>
                </table>
            `);

            const totalAddOnsCost = getAddOnsSubTotal(reservation);

            if (reservation.add_ons) {
                let rows = '', html = '', count = 0;

                for (const id in reservation.add_ons) {
                    count++;
                    const addOn = reservation.add_ons[id];
                    let addOnRate = addOn.cost;
                    let nameTdString = `1 x ${addOn.name}`;
                    if (addOn.fixed_price !== "1") nameTdString += ` for ${reservation.itinerary.days} day(s)`;
                    if (addOn.name === "Collision Insurance") {
                        if (reservation.vehicle) {
                            addOnRate = reservation.vehicle.insurance;
                        } else {
                            addOnRate = 0;
                        }
                    }
                    rows += `
                        <tr data-id="${addOn.id}">
                            <td>${nameTdString}</td>
                            <td>${makePriceString(addOnRate)}</td>
                            <td>${makePriceString(getAddOnCostForTotalDays(addOn, reservation.itinerary.days, reservation.vehicle))}</td>
                        </tr>
                    `;
                }

                if (rows.length) {
                    html = `
                    <h6>Add-ons</h6>
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Rate</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${rows}
                            <tr>
                                <td>Add-ons Subtotal</td>
                                <td></td>
                                <td>${makePriceString(totalAddOnsCost)}</td>
                            </tr>
                        </tbody>
                    </table>
                `
                } else {
                    html = `
                    <h6>Add-ons</h6>
                    <div><span>--</span><span>--</span><span>--</span></div>
                `;
                }

                $("#reservation-summary div.add-ons.summary").html(html);
            }
            const rentalSubtotal = parseInt(priceDay) * rate.days;
            $("#reservation-summary .estimated-total span:last-child").text(makePriceString(totalAddOnsCost + rentalSubtotal));

            goToAddOns();
        }

        let hotelNameInputStr = "optional";

        if (data.pickUpLocation === "Your Hotel" || data.returnLocation === "Your Hotel") {
            hotelNameInputStr = "required";
        }

        $('[data-hotel-name-input]').prop('dataset').hotelNameInput = hotelNameInputStr;
    });

    $(".form-input").on('focus change input click', function () {
        $(this).removeClass('form-error');
    });

    $(".reset-data").on('click', async function () {
        const response = await Swal.fire({
            icon: 'warning',
            title: 'Are you sure you want to reset the form?',
            showCancelButton: true,
            confirmButtonText: 'Yes',
            cancelButtonText: 'No'
        });

        if (response.isConfirmed) {
            await fetch('/includes/reservation.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',  // Set Content-Type to JSON
                },
                body: JSON.stringify({ action: "reset_reservation" })
            });

            location.href = '/reservation/';
        }
    });

    $("#hamburger-button").on('click', function () {
        $("html").addClass('viewing-hamburger-menu');
    });

    $("#close-hamburger").on('click', function () {
        $("html").removeClass('viewing-hamburger-menu');
    });

    $("#final-details-form .continue-btn").on('click', async function () {
        const data = {
            "hotel": $('#final-details-form input[name="hotel"]').val(),
            "first-name": $('#final-details-form input[name="first-name"]').val(),
            "last-name": $('#final-details-form input[name="last-name"]').val(),
            "driver-license": $('#final-details-form input[name="driver-license"]').val(),
            "country-region": $('#final-details-form input[name="country-region"]').val(),
            "street": $('#final-details-form input[name="street"]').val(),
            "town-city": $('#final-details-form input[name="town-city"]').val(),
            "state-county": $('#final-details-form input[name="state-county"]').val(),
            "phone": $('#final-details-form input[name="phone"]').val(),
            "email": $('#final-details-form input[name="email"]').val(),
            "h826r2whj4fi_cjz8jxs2zuwahhhk6": ""
        };

        const formDataIsValid = handleInvalidFormData(data, "final_details");

        if (!formDataIsValid) return;

        const ReservationSessionRes = await fetch('/includes/reservation.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',  // Set Content-Type to JSON
            },
            body: JSON.stringify({ action: "get_reservation" })
        });

        const reservation = await ReservationSessionRes.json();
        if (!reservation.itinerary) {
            Swal.fire({
                icon: "warning",
                title: "Incomplete Booking",
                text: "Please complete the itinerary section of the booking before submitting."
            });
            return $(".reservation-step.itinerary").trigger('click');
        }
        if (!reservation.vehicle) {
            Swal.fire({
                icon: "warning",
                title: "Incomplete Booking",
                text: "Please complete the vehicle selection section of the booking before submitting."
            });
            return $(".reservation-step.vehicle-add-on").trigger('click');
        }

        Swal.fire({
            title: "Sending request...",
            didOpen: () => Swal.showLoading()
        });

        const emailRes = await fetch('/includes/vehicle-request-send.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',  // Set Content-Type to JSON
            },
            body: JSON.stringify(data)
        });

        const emailJSON = await emailRes.json();

        if (emailJSON.data.debugging) return console.log(emailJSON);

        if (emailJSON.success && !emailJSON.data.debugging) {
            location.href = `/confirmation/?key=${emailJSON.data.key}`;
        } else {
            Swal.fire({
                title: "Error",
                text: emailJSON.message,
                icon: "error",
                confirmButtonText: "Ok",
            });
        }
    });

    $("#contact-form-section form").on('submit', async function (e) {
        e.preventDefault();

        const data = {
            "name": $('#contact-form-section input[name="name"]').val(),
            "email": $('#contact-form-section input[name="email"]').val(),
            "message": $('#contact-form-section textarea[name="message"]').val(),
            "h826r2whj4fi_cjz8jxs2zuwahhhk6": ""
        };

        const formDataIsValid = handleInvalidFormData(data, "contact");

        if (!formDataIsValid) return;

        Swal.fire({
            title: "Submitting form...",
            didOpen: () => Swal.showLoading()
        });

        const emailRes = await fetch('/includes/contact-send.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',  // Set Content-Type to JSON
            },
            body: JSON.stringify(data)
        });

        const emailJSON = await emailRes.json();

        Swal.fire({
            title: emailJSON.success ? "Success" : emailJSON.message,
            text: emailJSON.success ? "Your message has been sent successfully." : "error",
            icon: emailJSON.success ? "success" : "error"
        });
    });

    $("#taxi-page #contact-form-section form").off('submit').on('submit', async function (e) {
        e.preventDefault();

        const data = {
            "pickUp": $('#contact-form-section input[name="pick-up"]').val(),
            "dropOff": $('#contact-form-section input[name="drop-off"]').val(),
            "pickUpTime": $('#contact-form-section input[name="pick-up-time"]').val(),
            "passengers": $('#contact-form-section input[name="passengers"]').val(),
            "phone": $('#contact-form-section input[name="phone"]').val(),
            "email": $('#contact-form-section input[name="email"]').val(),
            "name": $('#contact-form-section input[name="name"]').val(),
            "message": $('#contact-form-section textarea[name="message"]').val(),
            "h826r2whj4fi_cjz8jxs2zuwahhhk6": ""
        };

        const formDataIsValid = handleInvalidFormData(data, "taxi");

        if (!formDataIsValid) return;

        Swal.fire({
            title: "Submitting form...",
            didOpen: () => Swal.showLoading()
        });

        const emailRes = await fetch('/includes/taxi-request-send.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',  // Set Content-Type to JSON
            },
            body: JSON.stringify(data)
        });

        const emailJSON = await emailRes.json();

        Swal.fire({
            title: emailJSON.success ? "Success" : emailJSON.message,
            text: emailJSON.success ? "Your message has been sent successfully." : "error",
            icon: emailJSON.success ? "success" : "error"
        });
    });

});

function handleInvalidFormData(data, section) {
    let text;

    if (section === "itinerary") {

        if (data.pickUpLocation === 'Choose Location' || !data.pickUpLocation) {
            text = 'Please select a pick up location.';
            element = $('.custom-select.pick-up');
        } else if (!data.pickUpDate.date) {
            text = 'Please select a pick up date.';
            element = $('#pick-up-flatpickr + input');
        } else if (!isWithinBusinessHours(data.pickUpDate.date)) {
            text = 'Pick up time must be between 8am and 6pm.';
            element = $('#pick-up-flatpickr + input');
        } else if (!data.returnToSameLocation.checked && (data.returnLocation === 'Choose Location' || !data.returnLocation)) {
            text = 'Please select a return location.';
            element = $('.custom-select.return');
        } else if (!data.returnDate.date) {
            text = 'Please select a return date.';
            element = $('#return-flatpickr + input');
        } else if (!isWithinBusinessHours(data.returnDate.date)) {
            text = 'Return time must be between 8am and 6pm.';
            element = $('#return-flatpickr + input');
        } else if (pickUpDateIsSameAsReturnDate(data)) {
            text = 'You cannot pick up and return the car on the same day.';
            element = $('#pick-up-flatpickr + input, #return-flatpickr + input');
        } else if (pickUpDateIsAfterReturnDate(data)) {
            text = 'You have to return the car at least one day after pick up.';
            element = $('#pick-up-flatpickr + input, #return-flatpickr + input');
        }

    } else if (section === "final_details") {
        console.log("data", data);
        if (
            $('[data-hotel-name-input]').prop('dataset').hotelNameInput === "required" &&
            data.hotel.length === 0
        ) {
            text = 'Please enter the name of your hotel.';
            element = $('[data-hotel-name-input] input');
        } else if (data['first-name'] === '') {
            text = 'Please enter your first name.';
            element = $('#final-details-form input[name="first-name"]');
        } else if (data['last-name'] === '') {
            text = 'Please enter your last name.';
            element = $('#final-details-form input[name="last-name"]');
        } else if (data['country-region'] === '') {
            text = 'Please enter your country or region.';
            element = $('#final-details-form input[name="country-region"]');
        } else if (data.street === '') {
            text = 'Please enter your street address.';
            element = $('#final-details-form input[name="street"]');
        } else if (data['town-city'] === '') {
            text = 'Please enter your town or city.';
            element = $('#final-details-form input[name="town-city"]');
        } else if (data.phone === '') {
            text = 'Please enter your phone number.';
            element = $('#final-details-form input[name="phone"]');
        } else if (data.email === '') {
            text = 'Please enter your email address.';
            element = $('#final-details-form input[name="email"]');
        } else if (!STATE.emailRegEx.test(data.email)) {
            text = 'Please enter a valid email address.';
            element = $('#final-details-form input[name="email"]');
        }

    } else if (section === "contact") {

        if (data.name === '') {
            text = 'Please enter your name.';
            element = $('#contact-form-section input[name="name"]');
        } else if (data.email === '') {
            text = 'Please enter your email address.';
            element = $('#contact-form-section input[name="email"]');
        } else if (!STATE.emailRegEx.test(data.email)) {
            text = 'Please enter a valid email address.';
            element = $('#contact-form-section input[name="email"]');
        } else if (data.message === '') {
            text = 'Please enter your message.';
            element = $('#contact-form-section textarea[name="message"]');
        }

    } else if (section === "taxi") {

        if (data.pickUp === '') {
            text = 'Please enter a pick up location.';
            element = $('#contact-form-section input[name="pick-up"]');
        } else if (data.dropOff === '') {
            text = 'Please enter a drop off location.';
            element = $('#contact-form-section input[name="drop-off"]');
        } else if (data.pickUpTime === '') {
            text = 'Please enter a time to be picked up.';
            element = $('#contact-form-section input[name="pick-up-time"]');
        } else if (data.passengers === '') {
            text = 'Please enter how many passengers will be on the ride.';
            element = $('#contact-form-section input[name="passengers"]');
        } else if (data.phone === '') {
            text = 'Please enter your phone number.';
            element = $('#contact-form-section input[name="phone"]');
        } else if (STATE.phoneRegEx.test(data.phone)) {
            text = 'Please enter a proper phone number otherwise we will not be able to contact you.';
            element = $('#contact-form-section input[name="phone"]');
        } else if (data.email === '') {
            text = 'Please enter your email address.';
            element = $('#contact-form-section input[name="email"]');
        } else if (!STATE.emailRegEx.test(data.email)) {
            text = 'Please enter a valid email address.';
            element = $('#contact-form-section input[name="email"]');
        } else if (data.name === '') {
            text = 'Please enter your name.';
            element = $('#contact-form-section input[name="name"]');
        }

    }

    if (text) {
        Swal.fire({
            text,
            title: "Incomplete form",
            icon: "warning",
            confirmButtonText: "Ok",
        });
        element.addClass('form-error');
    }

    return !text;
}

function isWithinBusinessHours(date) {
    date = (typeof date === 'string') ? new Date(date) : date;
    const hour = date?.getHours?.();
    return hour >= 8 && hour <= 18;
}

function pickUpDateIsSameAsReturnDate(data) {
    const pickUpDay = getDayFromDate(data.pickUpDate.altValue);
    const returnDay = getDayFromDate(data.returnDate.altValue);
    return pickUpDay === returnDay && (data.pickUpDate.ts + 86400000 > data.returnDate.ts); // could be same day but different month
}

function pickUpDateIsAfterReturnDate(data) {
    return data.pickUpDate.ts > data.returnDate.ts;
}

function getDayFromDate(dateStr) {
    // dateStr = "April 24, 2024 10:00 am"
    if (!dateStr) return;
    return Number(dateStr.split(',')[0].split(' ')[1]);
}

function getYearFromDate(dateStr) {
    // dateStr = "April 24, 2024 10:00 am"
    if (!dateStr) return;
    return Number(dateStr.split(', ')[1].split(' ')[0]);
}

function goToAddOns() {
    $(".booking-flow-section").hide();
    $("#vehicle-add-ons").show();
}

function getDifferenceInDays(pickUpDate, returnDate) {
    const start = new Date(Number(pickUpDate));
    const end = new Date(Number(returnDate));
    const diffTime = Math.abs(end - start);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)); // Convert milliseconds to days
    return diffDays;
}

function makePriceString(rate, days = 1, currency = 'USD') {
    if (!rate) rate = 0;
    // Currency can only be USD or EC
    if (currency !== 'USD' && currency !== 'EC') currency = "USD";
    return `$${currency}${parseInt(rate) * days}`;
}

function getAddOnCostForTotalDays(addOn, days = 1, vehicle = undefined) {
    let newCost = Number(addOn.cost);
    if (addOn.name === "Collision Insurance") {
        if (vehicle) {
            newCost = Number(vehicle.insurance) * days;
        } else {
            newCost = 0;
        }
    } else if (addOn.name === "Child Seat (If Available)") {
        if (vehicle) {
            newCost *= days;
        } else {
            newCost = 0;
        }
        console.log("newCost", newCost);
    }
    return newCost;
}

function getAddOnsSubTotal(reservation) {
    let subTotal = 0;
    if (reservation.add_ons) {
        let days = 1;
        if (reservation.vehicle && reservation.itinerary) {
            days = getDifferenceInDays(reservation.itinerary.pickUpDate.ts, reservation.itinerary.returnDate.ts)
        }

        for (const id in reservation.add_ons) {
            const addOn = reservation.add_ons[id];
            subTotal += getAddOnCostForTotalDays(addOn, days, reservation.vehicle);
        }
    }

    return subTotal;
}

<?php

include_once '../includes/env.php';

$title_override = "Reserve Your Island Adventure taxi service with $company_name in Antigua!";
$page = "taxi";
$description = "Make your taxi reservation for a hassle-free taxi rental experience in Antigua with our reliable vehicles and excellent service.";
$extra_css = "contact";

include_once '../includes/header.php';

?>

<section class="general-header">
    <h1>Taxi Reservation</h1>
</section>

<section id="taxi-info-section">
    <div class="inner">
        <h2>Transportation Tailored for You</h2>
        <div id="taxi-info-card-container">
            <div class="taxi-info-card">
                <h3>Island Tour Package</h3>
                <div>
                    <span>$100 per person</span>
                    <span>4-hour Island tour</span>
                    <span>Comfortable ride</span>
                </div>
            </div>
            <div class="taxi-info-card">
                <h3>Private Airport Transfer</h3>
                <div>
                    <span>Transfer to/from Airport</span>
                    <span>Additional $10 regulation</span>
                    <span>Reliable taxi service</span>
                </div>
            </div>
            <div class="taxi-info-card">
                <h3>Cruise Ship Pickup</h3>
                <div>
                    <span>Pickup from cruise</span>
                    <span>Quick & Easy ride</span>
                </div>
            </div>
            <div class="taxi-info-card">
                <h3>VIP Service</h3>
                <div>
                    <span>Personalized requests</span>
                    <span>Tailored experience</span>
                    <span>Contact for more info</span>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="contact-form-section">
    <div class="inner">
        <h2>Reserve Your Taxi</h2>
        <form action="">
            <div class="left">
                <div class="mutiple-input-container">
                    <div class="input-container">
                        <label for="contact-pick-up">Pick Up Location<sup>*</sup></label>
                        <input class="form-input" id="contact-pick-up" name="pick-up" type="text" placeholder="Pick up location">
                    </div>
                    <div class="input-container">
                        <label for="contact-drop-off">Drop Off Location<sup>*</sup></label>
                        <input class="form-input" id="contact-drop-off" name="drop-off" type="text" placeholder="Drop off location">
                    </div>
                </div>
                <div class="mutiple-input-container">
                    <div class="input-container">
                        <label for="contact-pick-up-time">Pick Up Time<sup>*</sup></label>
                        <input class="form-input" id="contact-pick-up-time" name="pick-up-time" type="datetime-local" placeholder="Pick up time">
                    </div>
                    <div class="input-container">
                        <label for="contact-passengers">Number of Passengers<sup>*</sup></label>
                        <input class="form-input" id="contact-passengers" name="passengers" type="text" placeholder="5 People">
                    </div>
                </div>
                <div class="mutiple-input-container">
                    <div class="input-container">
                        <label for="contact-phone">Phone <sup>*</sup></label>
                        <input class="form-input" id="contact-phone" name="phone" type="tel" placeholder="+1 (234) 565-4321">
                    </div>
                    <div class="input-container">
                        <label for="contact-email">Email <sup>*</sup></label>
                        <input class="form-input" id="contact-email" name="email" type="email" placeholder="my_name@email.com">
                    </div>
                </div>
                <div class="input-container name-container">
                    <label for="contact-name">Name <sup>*</sup></label>
                    <input class="form-input" id="contact-name" name="name" type="text" placeholder="Enter your name">
                </div>
                <div class="input-container taxi-message" style="margin: 0;">
                    <label for="contact-message">Special Requirements</label>
                    <textarea name="message" cols="30" rows="10" placeholder="Enter any extra details..."></textarea>
                </div>
                <button type="submit">SUBMIT RESERVATION</button>
            </div>
            <div class="right">
                <div class="input-container taxi-message" style="margin: 0;">
                    <label for="contact-message">Special Requirements</label>
                    <textarea name="message" cols="30" rows="10" placeholder="Enter any extra details..."></textarea>
                </div>
            </div>
        </form>
    </div>
</section>

<script>
    $(".input-container.taxi-message textarea").off('input').on('input', function() {
        $(".input-container.taxi-message textarea").val($(this).val());
    });
</script>


<?php include_once '../includes/footer.php'; ?>
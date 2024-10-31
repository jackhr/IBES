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
        <div class="taxi-info-card">
            <h2>Island Tour Package</h2>
            <div>
                <span>$100 per person</span>
                <span>4-hour Island tour</span>
                <span>Comfortable ride</span>
            </div>
        </div>
        <div class="taxi-info-card">
            <h2>Private Airport Transfer</h2>
            <div>
                <span>Transfer to/from Airport</span>
                <span>Additional $10 regulation</span>
                <span>Reliable taxi service</span>
            </div>
        </div>
        <div class="taxi-info-card">
            <h2>Cruise Ship Pickup</h2>
            <div>
                <span>Pickup from cruise</span>
                <span>Quick & Easy ride</span>
            </div>
        </div>
        <div class="taxi-info-card">
            <h2>VIP Service</h2>
            <div>
                <span>Personalized requests</span>
                <span>Tailored experience</span>
                <span>Contact for more info</span>
            </div>
        </div>
    </div>
</section>

<section id="contact-form-section">
    <div class="inner">
        <h2>SEND US AN EMAIL</h2>
        <form action="">
            <div class="left">
                <div class="mutiple-input-container">
                    <div class="input-container">
                        <label for="contact-pick-up">Pick Up <sup>*</sup></label>
                        <input id="contact-pick-up" name="pick-up" type="text" placeholder="Pick up location">
                    </div>
                    <div class="input-container">
                        <label for="contact-drop-off">Drop Off <sup>*</sup></label>
                        <input id="contact-drop-off" name="drop-off" type="text" placeholder="Drop off location">
                    </div>
                </div>
                <div class="mutiple-input-container">
                    <div class="input-container">
                        <label for="contact-name">Name <sup>*</sup></label>
                        <input id="contact-name" name="name" type="text" placeholder="Enter your name">
                    </div>
                    <div class="input-container">
                        <label for="contact-email">Phone <sup>*</sup></label>
                        <input id="contact-email" name="email" type="tel" placeholder="+1 (234) 565-4321">
                    </div>
                </div>
                <div class="input-container taxi-message" style="margin: 0;">
                    <label for="contact-message">Extra Details</label>
                    <textarea name="message" cols="30" rows="10" placeholder="Enter any extra details..."></textarea>
                </div>
                <button type="submit">SUBMIT RESERVATION</button>
            </div>
            <div class="right">
                <div class="input-container taxi-message" style="margin: 0;">
                    <label for="contact-message">Extra Details</label>
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
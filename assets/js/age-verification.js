jQuery(document).ready(function () {
    (function ($) { // Use an IIFE to create a new scope for $

        async function showAgeVerificationPopup(redirectUrl = "https://www.google.com") {
            console.log("showAgeVerificationPopup called");

            return new Promise((resolve, reject) => {
                if (getCookie("ageVerified")) {
                    console.log("Age Verified cookie found. Popup not shown.");
                    resolve();
                    return;
                }

                // Prevent duplicate popups
                if ($(".age-verification-popup").length) { // Use jQuery here
                    console.log("Popup already exists. No action taken.");
                    resolve();
                    return;
                }

                console.log("Creating age verification popup...");
                const popupContainer = document.createElement("div");
                popupContainer.className = "age-verification-popup";
                popupContainer.style.position = "fixed";
                popupContainer.style.top = "0";
                popupContainer.style.left = "0";
                popupContainer.style.width = "100%";
                popupContainer.style.height = "100%";
                popupContainer.style.backgroundColor = "rgba(0, 0, 0, 0.5)";
                popupContainer.style.display = "flex";
                popupContainer.style.justifyContent = "center";
                popupContainer.style.alignItems = "center";
                popupContainer.style.zIndex = "1000";
                popupContainer.setAttribute("role", "dialog");
                popupContainer.setAttribute("aria-labelledby", "age-verification-heading");
                popupContainer.setAttribute("aria-describedby", "age-verification-description");

                const popupContent = document.createElement("div");
                popupContent.style.backgroundColor = "white";
                popupContent.style.padding = "20px";
                popupContent.style.borderRadius = "8px";
                popupContent.style.maxWidth = "400px";
                popupContent.style.textAlign = "center";

                const popupHeading = document.createElement("h2");
                popupHeading.id = "age-verification-heading";
                popupHeading.textContent = "Age Verification";
                popupContent.appendChild(popupHeading);

                const popupParagraph = document.createElement("p");
                popupParagraph.id = "age-verification-description";
                popupParagraph.textContent = `Are you ${ageVerificationData.minAge} years of age or older?`;
                popupContent.appendChild(popupParagraph);

                // Logo handling
                if (ageVerificationData.logoUrl) {
                    const logoImg = document.createElement("img");
                    logoImg.src = ageVerificationData.logoUrl;
                    logoImg.alt = "Age Verification Logo";
                    logoImg.style.maxWidth = "150px";
                    logoImg.style.marginBottom = "10px";
                    logoImg.onerror = () => {
                        logoImg.style.display = "none"; // Hide the image if it fails to load
                    };
                    popupContent.insertBefore(logoImg, popupHeading);
                }

                // Yes Button
                const yesButton = document.createElement("button");
                yesButton.textContent = "Yes";
                yesButton.style.backgroundColor = ageVerificationData.yesButtonColor || "#007bff";
                yesButton.style.color = "white";
                yesButton.style.border = "none";
                yesButton.style.padding = "10px 20px";
                yesButton.style.borderRadius = "5px";
                yesButton.style.marginRight = "10px";
                yesButton.addEventListener("click", () => {
                    setCookie("ageVerified", "true", 30); // 30 days expiration
                    document.body.removeChild(popupContainer);
                    resolve();
                });
                popupContent.appendChild(yesButton);

                // No Button
                const noButton = document.createElement("button");
                noButton.textContent = "No";
                noButton.style.backgroundColor = ageVerificationData.noButtonColor || "#dc3545";
                noButton.style.color = "white";
                noButton.style.border = "none";
                noButton.style.padding = "10px 20px";
                noButton.style.borderRadius = "5px";
                noButton.addEventListener("click", () => {
                    deleteCookie("ageVerified");
                    window.location.href = redirectUrl;
                });
                popupContent.appendChild(noButton);

                popupContainer.appendChild(popupContent);
                document.body.appendChild(popupContainer);

                // Handle Escape key
                document.addEventListener("keydown", function (event) {
                    if (event.key === "Escape" && document.body.contains(popupContainer)) {
                        document.body.removeChild(popupContainer);
                        reject(new Error("Popup dismissed with Escape key"));
                    }
                });
            });
        }

        async function runAgeVerification() {
            await showAgeVerificationPopup();
            console.log("Age verification completed.");
        }

        if (!getCookie("ageVerified")) {
            runAgeVerification();
        }

        function setCookie(name, value, days) {
            let expires = "";
            if (days) {
                const date = new Date();
                date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                expires = "; expires=" + date.toUTCString();
            }
            document.cookie = name + "=" + (value || "") + expires + "; path=/; Secure";
        }

        function getCookie(name) {
            const nameEQ = name + "=";
            const ca = document.cookie.split(";");
            for (let i = 0; i < ca.length; i++) {
                let c = ca[i].trim();
                if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
            }
            return null;
        }

        function deleteCookie(name) {
            setCookie(name, "", -1);
        }

        $('#age_verification_logo_upload_button').click(function (e) {
            e.preventDefault();

            try {
                var mediaUploader = wp.media({
                    title: 'Upload Logo',
                    button: {
                        text: 'Use this logo'
                    },
                    multiple: false
                });

                mediaUploader.on('select', function () {
                    var attachment = mediaUploader.state().get('selection').first().toJSON();
                    $('#age_verification_logo').val(attachment.url);
                });

                mediaUploader.open();
            } catch (error) {
                console.error("Error opening media uploader:", error);
                alert("An error occurred while opening the media uploader.");
            }
        });

    })(jQuery); // Pass jQuery as $ to the IIFE

});
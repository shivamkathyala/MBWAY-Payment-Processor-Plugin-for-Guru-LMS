document.addEventListener('DOMContentLoaded', function() {
//console.log(window.location)
const pathname = window.location.pathname
    
    // country code
    var phoneInput1 = null;
    const phoneInputField = document.querySelector("#phone");
    if (phoneInputField) {
        phoneInput1 = window.intlTelInput(phoneInputField, {
            initialCountry: "PT",
            utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js",
        });
    }

    // loader functions
    function loaderStart() {
        $(".spinner-border").fadeIn();
        $("#mbwayform").css("opacity", "0.5");
        $("#mbwayform :input").prop("disabled", true);
        $("#mbwayform button").prop("disabled", true);
    }

    function loaderStop() {
        $(".spinner-border").fadeOut();
        $("#mbwayform").css("opacity", "1");
        $("#mbwayform :input").prop("disabled", false);
        $("#mbwayform button").prop("disabled", false);
    }

    // start payment
    $("#make-payment").on("click", function(e) {
        e.preventDefault();
        loaderStart();
        // Get form data
        const phone = phoneInput1.getNumber();
        if (phone === "") {
            if (pathname.indexOf('/en') !== -1) {
                //exists
                alert("Please fill out the phone number correctly.");
            } else {
                alert("Por favor, preencha o número de telefone corretamente.");

            }
            
            loaderStop();
            return false;
        }
        let orderid = $("#orderid").val();
        let amountTotal = $("#amountid").val();
        let cancelReturn = $("#cancel_return").val();
        let mbwayKey = $("#mbwayKey").val();
        let myorder_url = $("#myorder_url").val();
        // console.log("Cancel URL:", cancelReturn);
        // console.log("Phone Number:", phone);
        // console.log("Order Id:", orderid);
        // console.log("Total Amount:", amountTotal);
        // console.log("MBwayKey: ", mbwayKey);

        function onPaymentErrorRedirect() {
            window.location.href = cancelReturn;
        }

        // AJAX call
        $.ajax({
            type: "POST",
            //url: currentUrl, // Ensure currentUrl is defined and correct
            url: 'index.php?option=com_ajax&plugin=mbway&group=gurupayment&format=json', // Ensure currentUrl is defined and correct
            data: {
                phone: phone,
                orderid: orderid,
                amountTotal: amountTotal,
                mbwayKey: mbwayKey
            },
            success: function(response) {
                console.log(response);
                let responseObject = response;
                switch (responseObject.Status) {
                    case "000":
                        // Payment initialized successfully
                        $(".spinner-border").fadeOut();
                        $("#mbwayform").fadeOut(function() {
                            $("#timer").fadeIn();
                        });
                        let timeLeft = 240; // 4 minutes
                        const timerElement = document.getElementById("time");
                        const statusElement = document.getElementById("payment-status");

                        // Function to format time in minutes and seconds
                        function formatTime(seconds) {
                            let min = Math.floor(seconds / 60);
                            let sec = seconds % 60;
                            return min + " min " + (sec < 10 ? "0" : "") + sec + " sec";
                        }

                        // Initial display of time
                        timerElement.innerText = formatTime(timeLeft);

                        // Timer function
                        const countdown = setInterval(() => {
                            if (timeLeft <= 0) {
                                clearInterval(countdown);
                                // Handle timeout logic
                                
                                if (pathname.indexOf('/en') !== -1) {
                                    //exists
                                    alert("Payment timed out");
                                } else {
                                    alert("Pagamento expirado");
                                }
                                onPaymentErrorRedirect();
                            } else {
                                // Update timer display
                                timerElement.innerText = formatTime(timeLeft);
                                timeLeft--; // Decrease timeLeft
                            }
                        }, 1000);

                        // Function to check payment status
                        let checkInterval;
                        const checkPaymentStatus = () => {
                            let reqID = responseObject.RequestId;
                            // AJAX call to check payment status
                            $.ajax({
                                type: "POST",
                                url: 'index.php?option=com_ajax&plugin=mbway&group=gurupayment&format=json', 
                                data: {
                                    reqID: reqID,
                                    amount: amountTotal,
                                    order_id: orderid
                                },
                                success: function(res) {
                                    //console.log("Raw response:", res);
                                    let response = res;
                                    //console.log(response);
                                    
                                        if (response.Status === "000") {
                                            function handleSuccess() {
                                                clearInterval(checkInterval);
                                                clearInterval(countdown);
                                                statusElement.innerText = "success";
                                                let audio = document.getElementById('myAudio');
                                                setTimeout(function() {
                                                    $("#timer").fadeOut(function() {
                                                        $("#pay-check").fadeIn();
                                                        audio.play();
                                                    });
                                                }, 2000);
                                                setTimeout(function() {
                                                    window.location.href = myorder_url;
                                                }, 5000);
                                            }
                                            handleSuccess();
                                        } else if (response.Status === "123") {
                                            console.log(response.Message);
                                            
                                            // Handle specific case for status code 123
                                        } else if (response.Status === "020") {
                                            console.log(response.Message);
                                            clearInterval(checkInterval);
                                            
                                            if (pathname.indexOf('/en') !== -1) {
                                                alert("Transaction rejected by the user.");
                                            } else {
                                                alert("Transação rejeitada pelo usuário.");
                                            }
                                            onPaymentErrorRedirect();
                                        } else if (response.Status === "101") {
                                            console.log(response.Message);
                                            clearInterval(checkInterval);
                                            
                                            if (pathname.indexOf('/en') !== -1) {
                                                alert("Transaction expired");
                                            } else {
                                                alert("Transação expirada");
                                            }
                                            onPaymentErrorRedirect();
                                        } else if (response.Status === "122") {
                                            console.log(response.Message);
                                            // Handle specific case for status code 122
                                        } else {
                                            console.log("Unhandled statusCode");
                                            // Handle any other status code that isn't explicitly handled
                                        }
                                },
                                error: function(xhr, status, error) {
                                    console.error("AJAX Error:", status, error);
                                    console.log("Response Text:", xhr.responseText);
                                    // Handle AJAX error, such as displaying an error message to the user
                                }
                                
                            });
                        };

                        // Check payment status every 5 seconds
                        checkInterval = setInterval(checkPaymentStatus, 5000);
                        break;
                    case "100":
                        if (pathname.indexOf('/en') !== -1) {
                            alert("The initialization request could not be completed. You can try again.");
                        } else {
                            alert("O pedido de inicialização não pôde ser concluído. Você pode tentar novamente.");
                        }
                        
                        loaderStop();
                        break;
                    case "122":
                        if (pathname.indexOf('/en') !== -1) {
                            alert("Transaction declined to the user.");
                        } else {
                            alert("Transação recusada pelo usuário.");
                        }
                        
                        loaderStop();
                        onPaymentErrorRedirect();
                        break;
                    case "999":
                        if (pathname.indexOf('/en') !== -1) {
                            alert("Error on initializing the request. You can try again.");
                        } else {
                            alert("Erro ao inicializar o pedido. Você pode tentar novamente.");
                        }
                        
                        loaderStop();
                        break;
                    default:
                        alert("Unhandled status code: " + responseObject.Status);
                        loaderStop();
                        onPaymentErrorRedirect();
                        break;
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", status, error);
                console.log("Response Text:", xhr.responseText);
                // Handle AJAX error, such as displaying an error message to the user
            }
        });
    });
    
    // end
});

$(function () {

    /****************************************
     * CATALOG
     ***************************************/

    // $('.quantityProductSelect').change(function () {
    //
    //     $('button').prop("disabled", true); // On désactive tous les select
    //     const productId = $(this).data('product');
    //     const url = $(this).data('url');
    //     const qtty = $(this).val();
    //
    //     $.ajax({
    //         url: url,
    //         type: "POST",
    //         data: {
    //             "productId": productId,
    //             "quantity": qtty
    //         },
    //         success: function (response) {
    //             // On récupère l'HTML du du produit ajouté et on l'insère dans le récap du devis (=panier)
    //             var htmlToDisplay = response.trim();
    //             $("#devis-recap-item-" + productId).remove();
    //             $("#devis-recap").append(htmlToDisplay);
    //             $('#quantityProductSelect_' + productId).val($('#devis-recap-item-' + productId).data('qtty'));
    //             disableButtonsFromQuantity($('#quantityProductSelect_' + productId).val(), productId);
    //         },
    //         complete: function () {
    //             $('button').prop("disabled", false);
    //         }
    //     });
    // });


    $('#frequencyButton2').on('click', function () {
        $('.catalog-frequency-select').prop("disabled", false);
        initClassFrequencyButtons();
    });

    $('#frequencyButton1').on('click', function () {
        $('.catalog-frequency-select').prop("disabled", true);
        $('.devis__frequence__button').prop("disabled", true);
        $('.devis__frequence__button').addClass("round-btn--disable");

    });

    $('#addFrequencyButton').on('click', function () {
        $('#catalog_frequency_times_select').val(function (i, oldval) {
            return ++oldval;
        });
        initClassFrequencyButtons();
    });

    $('#removeFrequencyButton').on('click', function () {
        if ($('#catalog_frequency_times_select').val() > 1) {
            $('#catalog_frequency_times_select').val(function (i, oldval) {
                return --oldval;
            });
            initClassFrequencyButtons();
        }
    });

    $('#catalog_next_step_button').on('click', function () {
        $('.catalog_next_step_button').prop("disabled", true);

        const url = $(this).data('url');
        const ajaxUrl = $(this).data('ajax');

        var frequencyVal = $("input:radio[name ='groupOfDefaultRadios']:checked").val();
        var data = {frequency: frequencyVal};
        if (frequencyVal === 'regular') {
            data = {
                frequency: frequencyVal,
                frequency_times: $('#catalog_frequency_times_select').val(),
                frequency_interval: $('#catalog_frequency_interval_select').val()
            }
        }

        $.ajax({
            url: ajaxUrl,
            type: "POST",
            data: data,
            success: function (response) {
                $(location).attr('href', url);
            },
            error: function (response) {
            },
            complete: function () {
                $('.catalog_next_step_button').prop("disabled", true);
            }
        });
    });

    /**
     * Ajout un seul produit au clic sur le +
     */
    $('.addOneToCartButton').click(function () {
        var url = $(this).data('url');

        var productId = (this.id).replace('addOneToCartButton', '');
        $.ajax({
            type: "POST",
            url: url,
            success: function (response) {
                // On récupère l'HTML du du produit ajouté et on l'insère dans le récap du devis (=panier)
                var htmlToDisplay = response.trim();
                $("#devis-recap-item-" + productId).remove();
                $("#devis-recap").append(htmlToDisplay);
                // On met à jour la valeur du <select> de qtty du produit
                $('#quantityProductSelect_' + productId).val($('#devis-recap-item-' + productId).data('qtty'));
                disableButtonsFromQuantity($('#quantityProductSelect_' + productId).val(), productId);

            }
        })
    });

    /**
     * Enlève un seul produit au clic sur le -
     */
    $('.removeOneToCartButton').click(function () {
        var url = $(this).data('url');
        var productId = (this.id).replace('removeOneToCartButton', '');
        $.ajax({
            type: "POST",
            url: url,
            success: function (response) {
                $("#devis-recap-item-" + productId).remove();
                if (JSON.stringify(response) !== '{}') {
                    // On récupère l'HTML du du produit ajouté et on l'insère dans le récap du devis (=panier)
                    var htmlToDisplay = response.trim();
                    $("#devis-recap").append(htmlToDisplay);
                }
                // On met à jour la valeur du <select> de qtty du produit
                $('#quantityProductSelect_' + productId).val($('#devis-recap-item-' + productId).data('qtty'));
                disableButtonsFromQuantity($('#quantityProductSelect_' + productId).val(), productId);
            }
        })
    });

    /****************************************
     * CONTACT FORM
     ***************************************/

    $('input[name*=isMultisite]').change(function () {
        if (this.value == 1) {
            $('.address-field').prop("disabled", true);
        } else if (this.value == 0) {
            $('.address-field').prop("disabled", false);
        }
    });

    $('#contact_staff_select').change(function () {
        $('.contact_staff_input').val(this.value);
    });

    $('#contact_access_select').change(function () {
        $('.contact_access_input').val(this.value);
    });

    /**
     * Ajout du token du captcha dans le formulaire
     */
    var isContactDetailFormSubimitted = false;
    $('#contactDetailForm').submit(function (event) {
        if (!isContactDetailFormSubimitted) {
            isContactDetailFormSubimitted = true;
            event.preventDefault();
            const siteKey = $('#contactDetailFormSubmitButton').data('key');
            grecaptcha.ready(function () {
                grecaptcha.execute(siteKey, {action: 'homepage'}).then(function (token) {
                    $('#contactDetailForm').prepend('<input type="hidden" name="g-recaptcha-response" value="' + token + '">')
                    $('#contactDetailForm').submit();
                });
            });
        }
    });

    $('#paprec_catalogbundle_quote_request_public_postalCode').autocomplete({
        source: '' + $('#paprec_catalogbundle_quote_request_public_postalCode').data('url'),
        minLength: 1
    })

});


/*******************************************
 * Functions
 ******************************************/

/**
 * Désactive les buttons d'un produit sur la page catalog en fonction de la quantité et du productId
 * si la quantité est égale à 0, alors on ne peut pas "Add One" ou "Add to quote"
 * @param quantity
 * @param productId
 */
function disableButtonsFromQuantity(quantity, productId) {
    if (quantity < 1) {
        $('#addProductToQuoteButton_' + productId).prop('disabled', true);
        $('#removeOneToCartButton' + productId).prop('disabled', true);
        $('#removeOneToCartButton' + productId).addClass('round-btn--disable');
    } else {
        $('#addProductToQuoteButton_' + productId).prop('disabled', false);
        $('#removeOneToCartButton' + productId).prop('disabled', false);
        $('#removeOneToCartButton' + productId).removeClass('round-btn--disable');
    }
}


function initClassFrequencyButtons() {
    const freq = $('#catalog_frequency_times_select').val();
    console.dir(freq);
    if (freq < 2) {
        $('#removeFrequencyButton').addClass('round-btn--disable');
        $('#removeFrequencyButton').prop("disabled", true);
    } else {
        $('#removeFrequencyButton').removeClass('round-btn--disable');
        $('#removeFrequencyButton').prop("disabled", false);
    }
    $('#addFrequencyButton').removeClass('round-btn--disable');
    $('#addFrequencyButton').prop("disabled", false);

}
